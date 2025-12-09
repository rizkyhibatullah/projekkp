<?php

namespace App\Models\SPModels; // Corrected namespace

use App\Models\Inventory\Dtproduk;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product; // Assuming Product model exists in App\Models

class CustomerOrderDetail extends Model
{
    /**
     * The table associated with the model.
     * Table name matches the migration file.
     * @var string
     */
    protected $table = 'customer_order_details';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = ['id'];

    /**
     * Get the customer order that owns the detail.
     */
    public function customerOrder()
    {
        return $this->belongsTo(CustomerOrder::class, 'customer_order_id');
    }

    /**
     * Get the product associated with the detail.
     */
    public function product()
    {
        return $this->belongsTo(Dtproduk::class, 'product_id');
    }
}
