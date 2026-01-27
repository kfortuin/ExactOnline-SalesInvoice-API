<?php

namespace ExactOnline\Services;

use App\Models\SalesInvoice;
use ExactOnline\Models\SalesInvoice as ExactOnlineSalesInvoice;
use ExactOnline\Models\SalesInvoiceLine as ExactOnlineSalesInvoiceLine;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Promises\LazyPromise;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ExactOnlineService
{
    public static function sendInvoice(SalesInvoice $salesInvoice, bool $simulated = false): PromiseInterface|LazyPromise|\Illuminate\Http\Client\Response
    {
        $exactSalesInvoiceLines = collect();

        foreach ($salesInvoice->salesInvoiceLines as $line) {
             $exactLine = (new ExactOnlineSalesInvoiceLine())->fromArray([
                 'Item' => $line->product->exact_online_id,
                 'InvoiceID' => $line->salesInvoice->exact_online_id,
                 'Quantity' => $line->quantity,
                 'GLAccount' => config('app.exact_online_client_id'),
             ]);
             $exactSalesInvoiceLines->push($exactLine);
        }

        $exactSalesInvoice = (new ExactOnlineSalesInvoice())->fromArray([
            'OrderedBy' => $salesInvoice->user->exact_online_id,
            'Journal' => '70', // Example journal code
            'InvoiceTo' => $salesInvoice->user->exact_online_id,
            'InvoiceDate' => $salesInvoice->invoice_date->format('Y-m-d'),
            'SalesInvoiceLines' => $exactSalesInvoiceLines->toArray(),
            'YourRef' => $salesInvoice->invoice_number,
        ]);

        $division = config('app.exact_online_division');

        if ($simulated) {
            Http::fake([
                "https://start.exactonline.com/api/v1/$division/salesinvoice/SalesInvoices"
                => Http::response([], Response::HTTP_CREATED),
            ]);
        }

        $response = Http::acceptJson()
            ->contentType('application/json')
            ->post("https://start.exactonline.com/api/v1/$division/salesinvoice/SalesInvoices",
                [
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
                ]
            );

        Log::channel('exact_online')->info('SalesInvoice: created new SalesInvoice', [
            'status' => $response->getStatusCode(),
            'body' => $response->body(),
        ]);

        return $response;
    }
}
