<?php

namespace App\Http\Controllers\MutasiGudang;

use App\Http\Controllers\Controller;
use App\Models\MutasiGudang\GudangOrder;
use App\Models\MutasiGudang\GudangOrderDetail;
use App\Models\MutasiGudang\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Inventory\Dtproduk;
use Carbon\Carbon;
use Exception;

class GudangOrderController extends Controller
{
    private function generateOrderNumber()
    {
        $today = Carbon::now()->format('dm y');
        $prefix = 'PG-' . str_replace(' ', '', $today);
        $todayDate = Carbon::now()->toDateString();
        $count = DB::table('th_gudangorder')->whereDate('Pur_Date', $todayDate)->count();
        $nextNumber = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        return $prefix . $nextNumber;
    }
    
    public function index()
    {
        $user = Auth::user();
        $isSuperAdmin = ($user->role_id == 1);
        $accessibleWarehouses = $user->warehouse_access ?? [];

        $query = GudangOrder::with('gudangPengirim', 'gudangPenerima');

        if ($isSuperAdmin) {
            $warehouses = Warehouse::all();
        } else {
            $warehouses = Warehouse::whereIn('WARE_Auto', $accessibleWarehouses)->get();
            $query->where(function ($q) use ($accessibleWarehouses) {
                $q->whereIn('pur_warehouse', $accessibleWarehouses)
                  ->orWhereIn('pur_destination', $accessibleWarehouses);
            });
        }
        
        $orders = $query->orderBy('Pur_Auto', 'desc')->paginate(15);
        return view('mutasigudang.gudangorder.index', compact('orders', 'warehouses'));
    }

    public function create()
    {
        $newOrder = GudangOrder::create([
            'pur_ordernumber' => $this->generateOrderNumber(),
            'Pur_Date' => Carbon::now(),
            'pur_status' => 'draft',
            'pur_emp' => Auth::user()->name,
            'pur_warehouse' => null,
            'pur_destination' => null,
        ]);
        return redirect()->route('gudangorder.edit', ['id' => $newOrder->Pur_Auto]);
    }

    public function edit($id)
    {
        $order = GudangOrder::with('details')->findOrFail($id);   
        $user = Auth::user();
        $isSuperAdmin = ($user->role_id == 1);
        $accessibleWarehouses = $user->warehouse_access ?? [];
        if ($isSuperAdmin) {
            $warehouses = Warehouse::all();
        } else {
            $warehouses = Warehouse::whereIn('WARE_Auto', $accessibleWarehouses)->get();
        }
        $allWarehouses = Warehouse::all();

        return view('mutasigudang.gudangorder.index', compact('order', 'warehouses', 'allWarehouses'));
    }

    public function show($id)
    {
        $order = GudangOrder::with('details')->findOrFail($id);
        $user = Auth::user();
        $isSuperAdmin = ($user->role_id == 1);
        $accessibleWarehouses = $user->warehouse_access ?? [];
        if ($isSuperAdmin) {
            $warehouses = Warehouse::all(); 
        } else {
            $warehouses = Warehouse::whereIn('WARE_Auto', $accessibleWarehouses)->get();
        }
        $allWarehouses = Warehouse::all();

        return view('mutasigudang.gudangorder.index', [
            'order'      => $order,
            'warehouses' => $warehouses,
            'allWarehouses' => $allWarehouses,
            'showMode'   => true
        ]);
    }

    public function updateHeader(Request $request, $id)
    {
        $order = GudangOrder::findOrFail($id);
        if ($order->pur_status !== 'draft') {
            return response()->json(['success' => false, 'message' => 'Hanya draft yang bisa diubah.'], 403);
        }

        $validatedData = $request->validate([
            'pur_ordernumber'   => 'required|string|max:255',
            'Pur_Date'          => 'required|date',
            'from_warehouse_id' => 'required|string|exists:m_warehouse,WARE_Auto',
            'to_warehouse_id'   => 'required|string|exists:m_warehouse,WARE_Auto',
            'Pur_Note'          => 'nullable|string',
        ]);

        $order->update([
            'pur_ordernumber' => $validatedData['pur_ordernumber'],
            'Pur_Date'        => $validatedData['Pur_Date'],
            'pur_warehouse'   => $validatedData['from_warehouse_id'],
            'pur_destination' => $validatedData['to_warehouse_id'],
            'Pur_Note'        => $validatedData['Pur_Note'],
        ]);

        return response()->json(['success' => true, 'message' => 'Header berhasil diperbarui.']);
    }

    public function storeDetail(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'Pur_Auto' => 'required|exists:th_gudangorder,Pur_Auto',
                'Pur_ProdCode' => 'required|string',
                'pur_prodname' => 'required|string',
                'Pur_UOM' => 'required|string|max:50',
                'Pur_Qty' => 'required|numeric|min:1',
                'Pur_GrossPrice' => 'required|numeric|min:0',
                'Pur_Discount' => 'nullable|numeric|min:0',
                'Pur_Taxes' => 'nullable|numeric|min:0',
                'Pur_NettPrice' => 'required|numeric',
            ]);
            
            $order = GudangOrder::findOrFail($validatedData['Pur_Auto']);
            $detail = GudangOrderDetail::create($validatedData);

            return response()->json(['success' => true, 'data' => $detail]);

        } catch (\Exception $e) {
            Log::error('Error storeDetail: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getProductsByWarehouse($warehouse_id)
    {
        if ($warehouse_id == 'all') {
             $products = Dtproduk::all();
        } else {
            $products = Dtproduk::where('WARE_Auto', $warehouse_id)->get();
        }
        return response()->json($products);
    }

    public function destroy($id)
    {
        $order = GudangOrder::findOrFail($id);
        if ($order->pur_status !== 'draft') {
            return response()->json(['success' => false, 'message' => 'Hanya DRAFT yang bisa dihapus.'], 403); 
        }
        DB::beginTransaction();
        try {
            $order->details()->delete();
            $order->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Draft berhasil dihapus.']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menghapus: ' . $e->getMessage()], 500); 
        }
    }

    public function destroyDetail($orderId, $detailId)
    {
        $detail = GudangOrderDetail::findOrFail($detailId);
        
        if ($detail->Pur_Auto != $orderId) { 
            return response()->json(['success' => false, 'message' => 'Detail tidak sesuai.'], 403);
        }
        $detail->delete();
        return response()->json(['success' => true, 'message' => 'Barang berhasil dihapus.']);
    }

    public function submit($id)
    {
        $order = GudangOrder::findOrFail($id);
        $order->pur_status = 'submitted';
        $order->save();
        return response()->json(['success' => true, 'message' => 'Order berhasil disubmit.']);
    }
}