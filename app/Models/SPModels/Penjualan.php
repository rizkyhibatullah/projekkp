<?php

namespace App\Models\SPModels;

use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    /**
     * The table associated with the model.
     * Diubah menjadi 'penjualans' agar sesuai dengan migrasi yang baru.
     * @var string
     */
    protected $table = 'penjualans';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'approved_at' => 'datetime',
    ];

    /**
     * Get the pelanggan for the sale.
     */
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    /**
     * Get the customer order associated with the sale.
     */
    public function customerOrder()
    {
        return $this->belongsTo(CustomerOrder::class, 'customer_order_id');
    }

    /**
     * Get the details for the sale.
     */
    public function details()
    {
        return $this->hasMany(PenjualanDetail::class, 'penjualan_id');
    }
}
