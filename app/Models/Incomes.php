<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incomes extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'income_id',
        'number',
        'date',
        'last_change_date',
        'supplier_article',
        'tech_size',
        'barcode',
        'quantity',
        'total_price',
        'date_close',
        'warehouse_name',
        'nm_id',
    ];

    protected $casts = [
        'date' => 'date',
        'last_change_date' => 'datetime',
        'date_close' => 'date',
        'total_price' => 'decimal:2',
    ];
}
