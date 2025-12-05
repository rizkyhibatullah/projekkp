<?php

namespace App\Http\Controllers\MutasiGudang;

use App\Http\Controllers\Controller;
use App\Models\MutasiGudang\TransferHeader;
use App\Models\MutasiGudang\TransferDetail;
use App\Models\MutasiGudang\GudangOrder;
use App\Models\MutasiGudang\Warehouse;
use App\Models\Inventory\Dtproduk; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class TransferGudangController extends Controller
{
    
    public function index()
    {
        $user = Auth::user();
        $isSuperAdmin = ($user->role_id == 1);
        $accessibleWarehouses = $user->warehouse_access ?? [];
        
        $query = TransferHeader::with('gudangPengirim', 'gudangPenerima');
        $query->where('trx_posting', 'F');
        if (!$isSuperAdmin) {
            $query->whereIn('Trx_WareCode', $accessibleWarehouses);
        }

        $transfers = $query->orderBy('Trx_Auto', 'desc')->paginate(15); 
        return view('mutasigudang.transfergudang.index', compact('transfers'));
    }

    public function create()
    {
        $user = Auth::user();
        DB::beginTransaction();
        try {
            $transactionDate = now();
            $newTrxNumber = $this->generateTrxNumber($transactionDate);
            $transfer = TransferHeader::create([
                'trx_number'  => $newTrxNumber,
                'Trx_Date'    => $transactionDate,
                'trx_posting' => 'F', 
                'user_id'     => Auth::id(), 
                'Trx_WareCode' => null, 
                'Trx_RcvNo'    => null,
            ]);
            DB::commit();
            return redirect()->route('transfergudang.edit', $transfer->Trx_Auto);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal membuat draft Transfer: ' . $e->getMessage());
            return redirect()->route('transfergudang.index')->with('error', 'Gagal membuat draft baru: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $user = Auth::user();
        $isSuperAdmin = ($user->role_id == 1);
        $accessibleWarehouses = $user->warehouse_access ?? [];

        $transfer = TransferHeader::with('details.produk')->findOrFail($id);
        $warehouses = Warehouse::all();
        $permintaanQuery = GudangOrder::with('gudangPengirim', 'gudangPenerima')
            ->where('pur_status', 'submitted')
            ->where(function($q) use ($transfer) {
                $q->whereNotExists(function ($sub) {
                    $sub->select(DB::raw(1))
                          ->from('th_slsgt')
                          ->whereRaw('th_slsgt.ref_pur_auto = th_gudangorder.Pur_Auto');
                })

                ->orWhere(function($subQ) use ($transfer) {
                    if ($transfer->ref_pur_auto) {
                        $subQ->where('Pur_Auto', $transfer->ref_pur_auto);
                    }
                });
            });

        if (!$isSuperAdmin) {
            $permintaanQuery->whereIn('pur_warehouse', $accessibleWarehouses);
        }

        $permintaanGudang = $permintaanQuery->orderBy('Pur_Auto', 'desc')->get();
        
        return view('mutasigudang.transfergudang.index', compact('transfer', 'warehouses', 'permintaanGudang'));
    }
        
    public function show($id)
    {
        return $this->edit($id); 
    }

    public function updateHeader(Request $request, $id)
    {
        $transfer = TransferHeader::findOrFail($id);
        if ($transfer->trx_posting !== 'F') {
            return response()->json(['success' => false, 'message' => 'Hanya draft yang bisa diubah.'], 403);
        }
        
        $validatedData = $request->validate([
            'Trx_Date'      => 'required|date',
            'Trx_WareCode'  => 'required|integer|exists:m_warehouse,WARE_Auto',
            'Trx_RcvNo'     => 'required|integer|exists:m_warehouse,WARE_Auto',
            'Trx_Note'      => 'nullable|string',
        ]);
        
        $transfer->update($validatedData);
        return response()->json(['success' => true, 'message' => 'Header berhasil diperbarui.']);
    }

    public function storeDetail(Request $request)
    {
        $validated = $request->validate([
            'Trx_Auto' => 'required|exists:th_slsgt,Trx_Auto',
            'Trx_ProdCode' => 'required|string|max:50',
            'trx_prodname' => 'required|string|max:255',
            'trx_uom' => 'required|string',
            'Trx_QtyTrx' => 'required|numeric|min:1',
            'trx_cogs' => 'required|numeric|min:0',
            'trx_discount' => 'nullable|numeric|min:0',
            'trx_taxes' => 'nullable|numeric|min:0',
            'trx_nettprice' => 'required|numeric', 
        ]);
        $transferHeader = TransferHeader::find($validated['Trx_Auto']);
        if (!$transferHeader || $transferHeader->trx_posting !== 'F') {
            return response()->json(['message' => 'Header transfer tidak ditemukan atau sudah diposting.'], 404);
        }
        $validated['trx_number'] = $transferHeader->trx_number; 
        $detail = TransferDetail::create($validated);
        $this->recalculateTransferTotal($transferHeader->Trx_Auto); 
        return response()->json(['success' => true, 'message' => 'Barang berhasil disimpan.', 'data' => $detail]);
    }

    public function destroyDetail($id, $detailId)
    {
        $detail = TransferDetail::findOrFail($detailId);
        if ($detail->Trx_Auto != $id) {
            return response()->json(['success' => false, 'message' => 'Detail tidak sesuai.'], 403);
        }
        $detail->delete();
        $this->recalculateTransferTotal($id); 
        return response()->json(['success' => true, 'message' => 'Barang berhasil dihapus.']);
    }

    public function destroy($id)
    {
        $transfer = TransferHeader::findOrFail($id);
        if ($transfer->trx_posting !== 'F') {
            return response()->json(['success' => false, 'message' => 'Hanya draft yang bisa dihapus.'], 403);
        }
        DB::beginTransaction();
        try {
            $transfer->details()->delete();
            $transfer->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Draft transfer berhasil dihapus.']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menghapus draft: ' . $e->getMessage()], 500);
        }
    }
    
    public function submit($id)
    {
        $transfer = TransferHeader::with('details')->findOrFail($id);
        if ($transfer->details->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Tidak ada barang. Gagal posting.'], 422);
        }
        
        DB::beginTransaction();
        try {
            $sourceWarehouseId = $transfer->Trx_WareCode;
            if (!$sourceWarehouseId) {
                throw new Exception('Gudang asal tidak boleh kosong.');
            }
            if (!$transfer->Trx_RcvNo) {
                throw new Exception('Gudang tujuan tidak boleh kosong.');
            }

            foreach ($transfer->details as $detail) {
                $prodCode = $detail->Trx_ProdCode;
                $qty      = $detail->Trx_QtyTrx;

                $stock = Dtproduk::where('kode_produk', $prodCode)
                                ->where('WARE_Auto', $sourceWarehouseId)
                                ->lockForUpdate()
                                ->first();

                if (!$stock) {
                    throw new Exception("Produk {$prodCode} tidak ditemukan di gudang (ID: {$sourceWarehouseId}).");
                }
                
                if ($stock->qty < $qty) {
                    $gudang = $transfer->gudangPengirim->WARE_Name ?? "ID: {$sourceWarehouseId}";
                    throw new Exception("Stok tidak cukup untuk produk {$prodCode} di {$gudang} (tersedia: {$stock->qty}, dibutuhkan: {$qty}).");
                }

                $stock->qty -= $qty;
                $stock->save(); 
            }

            $transfer->update(['trx_posting' => 'T']); 
            DB::commit();
            $message = 'Transfer berhasil di-posting. Stok di gudang asal telah dikurangi dan barang sekarang dalam perjalanan (menggantung).';
            return response()->json(['success' => true, 'message' => $message]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal update stok saat posting transfer: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal posting: ' . $e->getMessage()], 500);
        }
    }

    
    public function showInTransit()
    {
        try {
            $user = Auth::user();
            $isSuperAdmin = ($user->role_id == 1);
            $accessibleWarehouses = $user->warehouse_access ?? [];

            $query = TransferHeader::with(['details', 'gudangPengirim', 'gudangPenerima'])
                ->where('trx_posting', 'T')
                ->whereDoesntHave('penerimaan');

            if (!$isSuperAdmin && !empty($accessibleWarehouses)) {
                $query->where(function ($q) use ($accessibleWarehouses) {
                    $q->whereIn('Trx_WareCode', $accessibleWarehouses)
                    ->orWhereIn('Trx_RcvNo', $accessibleWarehouses);
                });
            }

            $inTransitTransfers = $query->orderBy('Trx_Date', 'desc')
                                        ->orderBy('Trx_Auto', 'desc')
                                        ->get();

            return view('mutasigudang.in_transit.index', compact('inTransitTransfers'));

        } catch (\Exception $e) {
            Log::error('Error in showInTransit: ' . $e->getMessage());
            return redirect()->route('transfergudang.index')
                            ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }


    public function fetchPermintaanDetails($permintaanId)
    {
        try {
            $permintaan = GudangOrder::with('details')->findOrFail($permintaanId); 
            return response()->json([
                'success' => true,
                'data' => [
                    'pur_warehouse_id' => $permintaan->pur_warehouse, 
                    'pur_destination_id' => $permintaan->pur_destination,
                    'details' => $permintaan->details->map(function ($detail) {
                        return [
                            'Pur_ProdCode' => $detail->Pur_ProdCode, 
                            'pur_prodname' => $detail->pur_prodname, 
                            'Pur_UOM'      => $detail->Pur_UOM,      
                            'Pur_Qty'      => $detail->Pur_Qty,      
                            'Pur_GrossPrice' => $detail->Pur_GrossPrice, 
                            'Pur_Discount' => $detail->Pur_Discount, 
                            'Pur_Taxes'    => $detail->Pur_Taxes,    
                            'Pur_NettPrice' => $detail->Pur_NettPrice, 
                        ];
                    })
                ]
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Permintaan Gudang tidak ditemukan.'], 404);
        }
    }

    public function syncDetailsFromPermintaan(Request $request, $id)
    {
        $request->validate(['permintaan_id' => 'required|exists:th_gudangorder,Pur_Auto']);
        $transfer = TransferHeader::findOrFail($id);
        $permintaan = GudangOrder::with('details')->find($request->permintaan_id);

        if ($transfer->trx_posting !== 'F') {
            return response()->json(['success' => false, 'message' => 'Hanya draft yang bisa diubah.'], 403);
        }
        
        DB::beginTransaction();
        try {
            $transfer->details()->delete();
            $transfer->update([
                'Trx_WareCode' => $permintaan->pur_warehouse,
                'Trx_RcvNo'    => $permintaan->pur_destination,
                'Trx_Note'     => 'Transfer berdasarkan Permintaan No: ' . $permintaan->pur_ordernumber,
                'ref_pur_auto' => $permintaan->Pur_Auto, 
            ]);
            $totalBruto = 0; $totalDiscount = 0; $totalTaxes = 0; $totalNetto = 0;
            foreach ($permintaan->details as $detail) {
                TransferDetail::create([
                    'Trx_Auto' => $transfer->Trx_Auto,
                    'trx_number' => $transfer->trx_number,
                    'Trx_ProdCode' => $detail->Pur_ProdCode, 
                    'trx_prodname' => $detail->pur_prodname, 
                    'trx_uom'      => $detail->Pur_UOM,      
                    'Trx_QtyTrx'   => $detail->Pur_Qty,      
                    'trx_cogs'     => $detail->Pur_GrossPrice, 
                    'trx_discount' => $detail->Pur_Discount, 
                    'trx_taxes'    => $detail->Pur_Taxes,    
                    'trx_nettprice' => $detail->Pur_NettPrice, 
                ]);
                $totalBruto += ($detail->Pur_Qty * $detail->Pur_GrossPrice); 
                $totalDiscount += $detail->Pur_Discount; 
                $totalTaxes += $detail->Pur_Taxes;       
                $totalNetto += $detail->Pur_NettPrice;  
            }
            $transfer->update([
                'bruto_from_permintaan' => $totalBruto,
                'diskon_from_permintaan' => $totalDiscount,
                'pajak_from_permintaan' => $totalTaxes,
                'netto_from_permintaan' => $totalNetto,
            ]);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data berhasil disinkronkan dari permintaan.']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal sync detail transfer: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal sinkronisasi data: ' . $e->getMessage()], 500);
        }
    }
    
    protected function recalculateTransferTotal($transferId)
    {
        $transfer = TransferHeader::with('details')->findOrFail($transferId);
        $totalBruto = $transfer->details->sum(function($detail) {
            return $detail->Trx_QtyTrx * $detail->trx_cogs;
        });
        $totalDiscount = $transfer->details->sum('trx_discount');
        $totalTaxes = $transfer->details->sum('trx_taxes');
        $totalNetto = $transfer->details->sum('trx_nettprice');
        $transfer->update([
            'bruto_from_permintaan' => $totalBruto,
            'diskon_from_permintaan' => $totalDiscount,
            'pajak_from_permintaan' => $totalTaxes,
            'netto_from_permintaan' => $totalNetto,
        ]);
    }
    
    private function generateTrxNumber(Carbon $transactionDate)
    {
        $prefix = 'GT-'; 
        $date = $transactionDate->format('dmy'); 
        $lastTrx = TransferHeader::where('trx_number', 'like', $prefix . $date . '%')
                                 ->latest('Trx_Auto') 
                                 ->first();
        if (!$lastTrx) {
            return $prefix . $date . '001';
        }
        $lastNumber = (int)substr($lastTrx->trx_number, -3);
        return $prefix . $date . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    }
}