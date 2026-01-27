<?php

namespace App\Http\Controllers;

use App\Http\Requests\SalesInvoiceRequest;
use App\Models\SalesInvoice;
use App\Services\ExactOnlineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SalesInvoiceController extends Controller
{
    public function createSalesInvoice(SalesInvoiceRequest $request): JsonResponse
    {
        $salesInvoice = new SalesInvoice();
        $salesInvoice->user_id = $request->input('user_id');
        $salesInvoice->invoice_number = SalesInvoice::generateInvoiceNumber();
        $salesInvoice->save();

        foreach ($request->input('lines', []) as $lineData) {
            $salesInvoice->salesInvoiceLines()->create([
                'product_id' => $lineData['product_id'],
                'quantity' => $lineData['quantity'],
            ]);
        }

        try {
            ExactOnlineService::sendInvoice($salesInvoice);
            Log::channel('exact-online')->info("Received invoice request for user {$salesInvoice->user->id}");
        } catch (\Exception $exception) {
            Log::channel('exact-online')->error($exception->getMessage());
        }

        return response()->json([], Response::HTTP_CREATED);
    }
}
