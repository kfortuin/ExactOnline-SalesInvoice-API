<?php

namespace App\Services;

use App\Models\SalesInvoice;
use Illuminate\Support\Facades\Http;

class ExactOnlineService
{
    public static function sendInvoice(SalesInvoice $salesInvoice): bool
    {
        Http::fake();

        $response = Http::post('https://start.exactonline.com/api/v1/{division}/salesinvoice/SalesInvoices', [
            'OrderedBy' => $salesInvoice->user()->exact_online_id,
            'Journal' => '70', // Example journal code
            // TODO: lines
        ]);
    }
}
