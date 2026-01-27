<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesInvoice extends Model
{
    use HasUuids;

    protected $table = 'sales_invoices';

    protected $fillable = [
        'user_id',
        'exact_online_id',
        'invoice_number',
        'invoice_date',
        'due_date',
    ];

    protected $casts = [
        'invoice_date' => 'datetime',
        'due_date' => 'datetime',
    ];

    public function salesInvoiceLines(): HasMany
    {
        return $this->hasMany(SalesInvoiceLine::class, 'sales_invoice_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public static function generateInvoiceNumber(): string
    {
        $lastInvoice = self::orderBy('created_at', 'desc')->first();
        $lastNumber = $lastInvoice ? (int) str_replace('INV-', '', $lastInvoice->invoice_number) : 0;
        $newNumber = $lastNumber + 1;
        return 'INV-' . str_pad((string)$newNumber, 6, '0', STR_PAD_LEFT);
    }
}
