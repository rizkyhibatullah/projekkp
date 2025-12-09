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
        // Eager load relationships for efficiency.
        $customerOrders = CustomerOrder::with(['pelanggan', 'details'])
            ->orderBy('tanggal_pesan', 'desc')
            ->get();

        // Get all active customers for the dropdown list.
        $pelanggans = Pelanggan::where('status', 'Aktif')->orderBy('anggota')->get();

        // Get all products for the dropdown list.
        $dataproduks = Dtproduk::orderBy('nama_produk')->get();

        // Return the main view with all necessary data.
        return view('SistemPenjualan.Customerorder', compact('customerOrders', 'pelanggans', 'dataproduks'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    
{
    // Validasi input termasuk detail item
    $validated = $request->validate([
        'pelanggan_id' => 'required|exists:daftarpelanggan,id',
        'po_pelanggan' => 'nullable|string|max:255',
        'tgl_kirim' => 'nullable|date',
        'bruto' => 'required|numeric|min:0',
        'disc' => 'nullable|numeric|min:0|max:100',
        'pajak' => 'nullable|numeric|min:0',
        'netto' => 'required|numeric|min:0',
        'tanggal_pesan' => 'required|date',
        'status' => 'nullable|in:Draft,Dikirim,Selesai,Batal',
        'items' => 'required|array|min:1', // Tambahkan validasi untuk items
        'items.*.product_id' => 'required|exists:dataproduk_tabel,id', // Validasi untuk setiap item
        'items.*.qty' => 'required|numeric|min:0.01',
        'items.*.harga' => 'required|numeric|min:0',
        'items.*.disc' => 'nullable|numeric|min:0|max:100',
        'items.*.pajak' => 'nullable|numeric|min:0',
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

    // Gunakan transaksi database untuk memastikan semua operasi berhasil atau gagal bersamaan
    DB::beginTransaction();
    try {
        // Buat header Customer Order
        $order = CustomerOrder::create($validated);

        // Simpan detail items
        foreach ($validated['items'] as $item) {
            // Hitung nominal
            $qty = (float)$item['qty'];
            $harga = (float)$item['harga'];
            $discPercent = (float)($item['disc'] ?? 0);
            $pajak = (float)($item['pajak'] ?? 0);

            $total = $qty * $harga;
            $discAmount = $total * ($discPercent / 100);
            $nominal = $total - $discAmount + $pajak;

            CustomerOrderDetail::create([
                'customer_order_id' => $order->id,
                'product_id' => $item['product_id'],
                'qty' => $item['qty'],
                'satuan' => 'pcs', // Default ke 'pcs' karena tidak ada field satuan di Dtproduk
                'harga' => $item['harga'],
                'disc' => $item['disc'] ?? 0,
                'pajak' => $item['pajak'] ?? 0,
                'nominal' => $nominal, // Tambahkan field nominal
                'catatan' => $item['catatan'] ?? '',
            ]);
        }

        DB::commit(); // Jika semua berhasil, simpan perubahan ke database

        // Return a success response with the created data.
        return response()->json([
            'success' => true,
            'message' => 'Pesanan pelanggan berhasil ditambahkan.',
            'data' => $order
        ]);
    } catch (\Exception $e) {
        DB::rollBack(); // Jika ada error, batalkan semua operasi

        // Return error response
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
    public function update(Request $request, CustomerOrder $customer_order) // Using Route-Model Binding
    {
        // Validate the incoming request data.
        $validated = $request->validate([
            'pelanggan_id' => 'required|exists:daftarpelanggan,id',
            'po_pelanggan' => 'nullable|string|max:255',
            'tgl_kirim' => 'nullable|date',
            'bruto' => 'required|numeric|min:0',
            'disc' => 'nullable|numeric|min:0|max:100',
            'pajak' => 'nullable|numeric|min:0',
            'netto' => 'required|numeric|min:0',
            'tanggal_pesan' => 'required|date',
            'status' => 'nullable|in:Draft,Dikirim,Selesai,Batal',
        ]);

        $validated['pengguna'] = Auth::user()->name;

        // Update the model instance.
        $customer_order->update($validated);

        // Return a success response with the updated data.
        return response()->json([
            'success' => true,
            'message' => 'Pesanan pelanggan berhasil diperbarui.',
            'data' => $customer_order
        ]);
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
