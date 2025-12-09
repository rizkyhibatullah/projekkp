<?php

namespace App\Http\Controllers\ControllerSP;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SPModels\Penjualan;
use App\Models\SPModels\PenjualanDetail;
use App\Models\SPModels\Pelanggan;
use App\Models\SPModels\CustomerOrder;
use App\Models\Product; // <-- Tambahkan ini untuk menggunakan model Product
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Exception; // <-- Tambahkan ini untuk menangani exception

class PenjualanController extends Controller
{
    /**
     * Menampilkan daftar jualan.
     */
    public function index()
    {
        $jualans = Penjualan::with(['pelanggan', 'customerOrder'])
            ->orderBy('created_at', 'desc')
            ->get();

        $pelanggans = Pelanggan::where('status', 'Aktif')->orderBy('anggota')->get();

        return view('SistemPenjualan.Penjualan', compact('jualans', 'pelanggans'));
    }

    /**
     * Menyimpan data jualan baru dan mengurangi stok produk.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pelanggan_id' => 'required|exists:daftarpelanggan,id',
            'customer_order_id' => 'required|exists:customer_orders,id',
            'tgl_kirim' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.qty' => 'required|numeric|min:0.01',
            'pengguna' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Gunakan transaksi database untuk memastikan semua operasi berhasil atau gagal bersamaan
        DB::beginTransaction();
        try {
            $customerOrder = CustomerOrder::with('details.product')->find($request->customer_order_id);
            if (!$customerOrder) {
                throw new Exception('Customer Order tidak ditemukan.');
            }
            $coDetailsMap = $customerOrder->details->keyBy('id');

            // Kalkulasi total
            $bruto = 0;
            $totalDisc = 0;
            $totalPajak = 0;
            foreach ($request->items as $coDetailId => $itemData) {
                if (!isset($coDetailsMap[$coDetailId])) continue;
                $detail = $coDetailsMap[$coDetailId];
                $hargaTotalItem = (float)$itemData['qty'] * (float)$detail->harga;
                $bruto += $hargaTotalItem;
                $totalDisc += $hargaTotalItem * ((float)$itemData['disc'] / 100);
                $totalPajak += (float)$itemData['pajak'];
            }
            $netto = $bruto - $totalDisc + $totalPajak;

            // Ambil nama pengguna dari request
            $pengguna = $request->pengguna ?? Auth::user()->name ?? 'System';

            // Buat header Penjualan
            $jualan = new Penjualan();
            $jualan->no_jualan = $this->generateJualanNumber();
            $jualan->customer_order_id = $request->customer_order_id;
            $jualan->pelanggan_id = $request->pelanggan_id;
            $jualan->tgl_kirim = $request->tgl_kirim;
            $jualan->jatuh_tempo = $request->jatuh_tempo;
            $jualan->po_pelanggan = $customerOrder->po_pelanggan;
            $jualan->bruto = $bruto;
            $jualan->total_disc = $totalDisc;
            $jualan->total_pajak = $totalPajak;
            $jualan->netto = $netto;
            $jualan->pengguna = $pengguna;
            $jualan->status = 'Draft'; // Status awal adalah Draft
            $jualan->save();

            // Simpan detail tanpa mengurangi stok
            foreach ($request->items as $coDetailId => $itemData) {
                if (isset($coDetailsMap[$coDetailId])) {
                    $detail = $coDetailsMap[$coDetailId];
                    $qtyJual = (float)$itemData['qty'];

                    // Hanya simpan detail tanpa mengurangi stok
                    PenjualanDetail::create([
                        'penjualan_id' => $jualan->id,
                        'product_id' => $detail->product_id,
                        'qty' => $qtyJual,
                        'satuan' => 'pcs',
                        'harga' => $detail->harga,
                        'disc' => $itemData['disc'],
                        'pajak' => $itemData['pajak'],
                        'nominal' => (float)$itemData['qty'] * (float)$detail->harga,
                        'catatan' => $itemData['catatan'],
                    ]);
                }
            }

            DB::commit(); // Jika semua berhasil, simpan perubahan ke database

            return response()->json(['message' => 'Data Penjualan berhasil disimpan dengan No: ' . $jualan->no_jualan]);

        } catch (Exception $e) {
            DB::rollBack(); // Jika ada error, batalkan semua operasi
            // Kirim pesan error yang jelas ke pengguna
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    // --- API METHODS ---

    public function getOutstandingOrders(Pelanggan $pelanggan)
    {
        $orders = CustomerOrder::where('pelanggan_id', $pelanggan->id)
            ->where('status', '!=', 'Selesai')
            ->get(['id', 'no_order', 'po_pelanggan']);

        return response()->json($orders);
    }

    public function getOrderDetails(CustomerOrder $customerOrder)
    {
        $details = $customerOrder->details()->with('product')->get();
        return response()->json($details);
    }

    /**
     * Fungsi helper untuk membuat nomor jualan.
     */
    private function generateJualanNumber()
    {
        $prefix = 'JUAL/' . date('Ym') . '/';
        $last = Penjualan::where('no_jualan', 'like', $prefix . '%')->latest('id')->first();
        $sequence = $last ? (int) substr($last->no_jualan, -4) + 1 : 1;
        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
 * Approve penjualan dan kurangi stok
 */
    public function approve($id)
    {
        // Gunakan transaksi database
        DB::beginTransaction();
        try {
            $jualan = Penjualan::with('details.product')->findOrFail($id);

            // Pastikan status masih Draft
            if ($jualan->status !== 'Draft') {
                return response()->json(['message' => 'Penjualan sudah di-approve atau dibatalkan'], 400);
            }

            // Periksa stok untuk setiap item
            foreach ($jualan->details as $detail) {
                $product = $detail->product;
                if (!$product) {
                    throw new Exception("Produk dengan ID {$detail->product_id} tidak ditemukan.");
                }

                if ($product->qty < $detail->qty) {
                    throw new Exception("Stok untuk produk '{$product->nama_produk}' tidak mencukupi. Sisa stok: {$product->qty}, Dibutuhkan: {$detail->qty}.");
                }
            }

            // Kurangi stok untuk setiap item
            foreach ($jualan->details as $detail) {
                $product = $detail->product;
                $product->qty -= $detail->qty;
                $product->save();
            }

            // Update status penjualan
            $jualan->status = 'Approved';
            $jualan->approved_by = Auth::user()->name;
            $jualan->approved_at = now();
            $jualan->save();

            DB::commit();

            return response()->json(['message' => 'Penjualan berhasil di-approve dan stok telah dikurangi']);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $jualan = Penjualan::with(['pelanggan', 'customerOrder', 'details.product'])->findOrFail($id);

        return view('SistemPenjualan.PenjualanDetail', compact('jualan'));
    }
}
