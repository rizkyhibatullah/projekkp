<?php

namespace App\Models\MutasiGudang;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Inventory\Dtproduk;

class Warehouse extends Model
{
    use HasFactory;

    protected $table = 'm_warehouse';
    protected $primaryKey = 'WARE_Auto';
    public $timestamps = false;

    protected $fillable = [
        'WARE_Name',
        'WARE_Address',
        'WARE_Phone',
        'WARE_Email',
        'WARE_Web',
        'ware_note1',
        'ware_note2',
        'WARE_EntryDate',
    ];

    public function products()
    {   
        return $this->hasMany(Dtproduk::class, 'WARE_Auto', 'WARE_Auto'); 
    }
}
