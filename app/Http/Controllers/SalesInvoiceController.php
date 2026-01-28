<?php

namespace App\Http\Controllers;

use App\Http\Requests\SalesInvoiceRequest;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceLine;
use Carbon\Carbon;
use ExactOnline\Services\ExactOnlineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SalesInvoiceController extends Controller
{
    public function createSalesInvoice(SalesInvoiceRequest $request): JsonResponse
    {
        $salesInvoice = new SalesInvoice();
        $salesInvoice->user_id = $request->input('user_id');
        $salesInvoice->invoice_number = SalesInvoice::generateInvoiceNumber();
        $salesInvoice->invoice_date = $request->has('invoice_date')
            ? $request->get('invoice_date')
            : Carbon::now()->format('Y-m-d')
        ;
        $salesInvoice->due_date = $request->has('due_date')
            ? $request->get('due_date')
            : Carbon::now()->addDays(30)->format('Y-m-d')
        ;

        $salesInvoice->save();

        foreach ($request->input('lines', []) as $lineData) {
            $salesInvoiceLine = new SalesInvoiceLine();
            $salesInvoiceLine->sales_invoice_id = $salesInvoice->id;
            $salesInvoiceLine->product_id = $lineData['product_id'];
            $salesInvoiceLine->quantity = $lineData['quantity'];
            $salesInvoiceLine->save();

            $salesInvoice->salesInvoiceLines()->save($salesInvoiceLine);
        }

        try {
            if (ExactOnlineService::sendInvoice(
                    $salesInvoice,
                    in_array(config('app.env'), ['local', 'testing', 'staging'], true)
                )->successful()
            ) {
                Log::channel('exact_online')
                    ->info("Processed SalesInvoice {$salesInvoice->id} for user {$salesInvoice->user->id}");
            } else {
                Log::channel('exact_online')
                    ->warning("Failed to process SalesInvoice {$salesInvoice->id} for user {$salesInvoice->user->id}");
                // Ideally, this is where I would send a Slack notification with the option to retry the POST
            }
        } catch (\Exception $exception) {
            Log::channel('exact_online')->error($exception->getMessage(), [
                'stack' => $exception->getTraceAsString(),
            ]);
            return response()->json([], Response::HTTP_BAD_REQUEST);
        }
        return response()->json([], Response::HTTP_CREATED);
    }
}
