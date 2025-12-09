<?php

namespace App\Models\SPModels;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\Inventory\Dtproduk;

class PenjualanDetail extends Model
{
    /**
     * The table associated with the model.
     * Diubah menjadi 'penjualan_details' agar sesuai dengan migrasi.
     * @var string
     */
    protected $table = 'penjualan_details';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = ['id'];

    /**
     * Get the main sale record that this detail belongs to.
     */
    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    /**
     * Get the product for this sale detail.
     */
    public function product()
    {
        // UBAH MODEL DI SINI
        return $this->belongsTo(Dtproduk::class, 'product_id'); // <-- GANTI DENGAN MODEL PRODUK ANDA YANG BENAR
    }
}
