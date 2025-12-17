<?php

namespace App\Http\Controllers\ControllerSP;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Dtproduk;
use App\Models\SPModels\CustomerOrder;
use App\Models\SPModels\CustomerOrderDetail;
use App\Models\SPModels\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DaftarPesananController extends Controller
{
    /**
     * Display a listing of the resource along with data for the creation form.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $customerOrders = CustomerOrder::with(['pelanggan', 'details'])
            ->orderBy('tanggal_pesan', 'desc')
            ->get();

        $pelanggans = Pelanggan::where('status', 'Aktif')->orderBy('anggota')->get();

        $dataproduks = Dtproduk::with('warehouse')->orderBy('nama_produk')->get();



        return view('SistemPenjualan.Customerorder', compact(
            'customerOrders',
            'pelanggans',
            'dataproduks',

        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // PERBAIKAN: Hapus validasi items.*.disc dan items.*.pajak
        $validated = $request->validate([
            'pelanggan_id' => 'required|exists:daftarpelanggan,id',
            'po_pelanggan' => 'nullable|string|max:255',
            'tgl_kirim' => 'nullable|date',
            'bruto' => 'required|numeric|min:0',
            'disc' => 'nullable|numeric|min:0|max:100',
            'pajak' => 'nullable|numeric|min:0|max:100',
            'netto' => 'required|numeric|min:0',
            'tanggal_pesan' => 'required|date',
            'status' => 'nullable|in:Draft,Dikirim,Selesai,Batal',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:dataproduk_tabel,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.harga' => 'required|numeric|min:0',
            // 'items.*.disc' => 'nullable|numeric|min:0|max:100', // <-- HAPUS BARIS INI
            // 'items.*.pajak' => 'nullable|numeric|min:0',      // <-- HAPUS BARIS INI
            'items.*.catatan' => 'nullable|string|max:255',
        ]);

        // Generate a unique order number (no_order).
        $currentYear = date('Y');
        $currentMonth = date('m');
        $prefix = 'CO-' . $currentYear . $currentMonth . '-';

        $lastOrder = CustomerOrder::where('no_order', 'like', $prefix . '%')->latest('id')->first();
        $sequence = $lastOrder ? (int)substr($lastOrder->no_order, -5) + 1 : 1;
        $no_order = $prefix . str_pad($sequence, 5, '0', STR_PAD_LEFT);

        // Add generated and user data to the validated array.
        $validated['no_order'] = $no_order;
        $validated['pengguna'] = Auth::user()->name;

        DB::beginTransaction();
        try {
            // Buat header Customer Order
            $order = CustomerOrder::create($validated);

            // Ambil nilai diskon dan pajak global
            $globalDiscPercent = $validated['disc'] ?? 0;
            $globalPajakPercent = $validated['pajak'] ?? 0;

            // Simpan detail items
            foreach ($validated['items'] as $item) {
                $qty = (float)$item['qty'];
                $harga = (float)$item['harga'];

                $totalHarga = $qty * $harga;
                $discAmount = $totalHarga * ($globalDiscPercent / 100);
                $subtotalAfterDiscount = $totalHarga - $discAmount;
                $taxAmount = $subtotalAfterDiscount * ($globalPajakPercent / 100);

                $nominal = $subtotalAfterDiscount + $taxAmount;

                CustomerOrderDetail::create([
                    'customer_order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'satuan' => 'pcs',
                    'harga' => $item['harga'],
                    'disc' => $globalDiscPercent, // Simpan nilai global
                    'pajak' => $globalPajakPercent, // Simpan nilai global
                    'nominal' => $nominal,
                    'catatan' => $item['catatan'] ?? '',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pesanan pelanggan berhasil ditambahkan.',
                'data' => $order
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SPModels\CustomerOrder  $customer_order
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, CustomerOrder $customer_order)
    {
        // PERBAIKAN: Hapus validasi items.*.disc dan items.*.pajak
        $validated = $request->validate([
            'pelanggan_id' => 'required|exists:daftarpelanggan,id',
            'po_pelanggan' => 'nullable|string|max:255',
            'tgl_kirim' => 'nullable|date',
            'bruto' => 'required|numeric|min:0',
            'disc' => 'nullable|numeric|min:0|max:100',
            'pajak' => 'nullable|numeric|min:0|max:100',
            'netto' => 'required|numeric|min:0',
            'tanggal_pesan' => 'required|date',
            'status' => 'nullable|in:Draft,Dikirim,Selesai,Batal',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:dataproduk_tabel,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.harga' => 'required|numeric|min:0',
            // 'items.*.disc' => 'nullable|numeric|min:0|max:100', // <-- HAPUS BARIS INI
            // 'items.*.pajak' => 'nullable|numeric|min:0',      // <-- HAPUS BARIS INI
            'items.*.catatan' => 'nullable|string|max:255',
        ]);

        $validated['pengguna'] = Auth::user()->name;

        DB::beginTransaction();
        try {
            // Update header
            $customer_order->update($validated);

            // Hapus semua detail lama
            $customer_order->details()->delete();

            // Ambil nilai diskon dan pajak global
            $globalDiscPercent = $validated['disc'] ?? 0;
            $globalPajakPercent = $validated['pajak'] ?? 0;

            // Simpan ulang semua item
            foreach ($validated['items'] as $item) {
                $qty = (float)$item['qty'];
                $harga = (float)$item['harga'];

                $totalHarga = $qty * $harga;
                $discAmount = $totalHarga * ($globalDiscPercent / 100);
                $subtotalAfterDiscount = $totalHarga - $discAmount;
                $taxAmount = $subtotalAfterDiscount * ($globalPajakPercent / 100);

                $nominal = $subtotalAfterDiscount + $taxAmount;

                CustomerOrderDetail::create([
                    'customer_order_id' => $customer_order->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'satuan' => 'pcs',
                    'harga' => $item['harga'],
                    'disc' => $globalDiscPercent, // Simpan nilai global
                    'pajak' => $globalPajakPercent, // Simpan nilai global
                    'nominal' => $nominal,
                    'catatan' => $item['catatan'] ?? '',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pesanan pelanggan berhasil diperbarui.',
                'data' => $customer_order
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SPModels\CustomerOrder  $customer_order
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(CustomerOrder $customer_order) // Using Route-Model Binding
    {
        $no_order = $customer_order->no_order;
        $customer_order->delete(); // Deletes the order and its details via model events if configured.

        return response()->json([
            'success' => true,
            'message' => "Pesanan $no_order berhasil dihapus."
        ]);
    }

    public function getOrderDetails($id)
    {
        $customerOrder = CustomerOrder::with('details.product')->find($id);

        if (!$customerOrder) {
            return response()->json(['error' => 'Customer Order tidak ditemukan'], 404);
        }

        return response()->json(['details' => $customerOrder->details]);
    }
}
