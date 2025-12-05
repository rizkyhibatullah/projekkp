<?php

namespace App\Http\Controllers\MutasiGudang;

use App\Http\Controllers\Controller;
use App\Models\MutasiGudang\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::all();
        return view('mutasigudang.warehouse.index', compact('warehouses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'WARE_Name' => 'required|max:50',
            'WARE_Address' => 'nullable|max:300',
            'WARE_Phone' => 'nullable|max:15',
            'WARE_Fax' => 'nullable|max:15',
            'WARE_Email' => 'nullable|email|max:50',
            'WARE_Web' => 'nullable|max:50',
            'ware_note1' => 'nullable|max:50',
            'ware_note2' => 'nullable|max:50',
        ]);

        $request->merge(['WARE_EntryDate' => now()]);
        Warehouse::create($request->all());

        return redirect()->route('warehouse.index')->with('success', 'Gudang berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $request->validate([
            'WARE_Name' => 'required|max:50',
            'WARE_Address' => 'nullable|max:300',
            'WARE_Phone' => 'nullable|max:15',
            'WARE_Fax' => 'nullable|max:15',
            'WARE_Email' => 'nullable|email|max:50',
            'WARE_Web' => 'nullable|max:50',
            'ware_note1' => 'nullable|max:50',
            'ware_note2' => 'nullable|max:50',
        ]);

        $warehouse->update($request->all());

        return redirect()->route('warehouse.index')->with('success', 'Gudang berhasil diperbarui.');
    }

    public function destroy($id)
    {
        try {
            $warehouse = Warehouse::findOrFail($id);
            if ($warehouse->products()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gudang tidak dapat dihapus karena memiliki produk terkait.'
                ], 500);
            }
            $warehouse->delete();

            return response()->json([
                'success' => true,
                'message' => 'Gudang berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data.'
            ], 500);
        }
    }

    public function json($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        return response()->json($warehouse);
    }

    public function getAll()
    {
        $warehouses = Warehouse::select('WARE_Auto', 'WARE_Name')->get();
        return response()->json($warehouses);
    }

}
