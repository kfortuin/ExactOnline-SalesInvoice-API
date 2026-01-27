<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesInvoiceLine extends Model
{
    protected $table = 'sales_invoice_lines';

    protected $fillable = [
        'sales_invoice_id',
        'product_id',
        'quantity',
    ];
}
