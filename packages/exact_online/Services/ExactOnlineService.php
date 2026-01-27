<?php

namespace ExactOnline\Services;

use App\Models\SalesInvoice;
use ExactOnline\Models\SalesInvoice as ExactOnlineSalesInvoice;
use ExactOnline\Models\SalesInvoiceLine as ExactOnlineSalesInvoiceLine;
use Illuminate\Support\Facades\Http;

class ExactOnlineService
{
    public static function sendInvoice(SalesInvoice $salesInvoice): bool
    {

        $exactSalesInvoiceLines = collect();

        foreach ($salesInvoice->salesInvoiceLines as $line) {
             $exactLine = (new ExactOnlineSalesInvoiceLine())->fromArray([
                 'Item' => $line->product->exact_online_id,
                 'InvoiceID' => $line->salesInvoice->exact_online_id,
                 'Quantity' => $line->quantity,
                 'GLAccount' => fake()->uuid(), // TODO: replace with value from config
             ]);
             $exactSalesInvoiceLines->push($exactLine);
        }

        $exactSalesInvoice = (new ExactOnlineSalesInvoice())->fromArray([
            'OrderedBy' => $salesInvoice->user->exact_online_id,
            'Journal' => '70', // Example journal code
            'InvoiceTo' => $salesInvoice->user->exact_online_id,
            'InvoiceDate' => $salesInvoice->invoice_date->format('Y-m-d'),
            'SalesInvoiceLines' => $exactSalesInvoiceLines->toArray(),
        ]);

        $division = config('app.exact_online_division');

        Http::fake();

        $response = Http::acceptJson()
            ->contentType('application/json')
            ->post("https://start.exactonline.com/api/v1/$division/salesinvoice/SalesInvoices", [
            'OrderedBy' => $exactSalesInvoice->getOrderedBy(),
            'Journal' => $exactSalesInvoice->getJournal(),
            'InvoiceTo' => $exactSalesInvoice->getInvoiceTo(),
            'InvoiceDate' => $exactSalesInvoice->getInvoiceDate(),
            'SalesInvoiceLines' => array_map(static function ($line) {
                return [
                    'Item' => $line->getItem(),
                    'InvoiceID' => $line->getInvoiceID(),
                    'Quantity' => $line->getQuantity(),
                    'GLAccount' => $line->getGLAccount(),
                ];
            }, $exactSalesInvoice->getSalesInvoiceLines()),
        ]);

        $response->getStatusCode();
    }
}
