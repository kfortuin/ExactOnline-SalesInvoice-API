<?php

namespace App\Http\Controllers;

use App\Models\SalesInvoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SalesInvoiceController extends Controller
{
    public function createSalesInvoice(Request $request): JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
        ]);

        // if 'lines' exists, validate each line with ValidSalesInvoiceLine
        if ($request->has('lines')) {
            $validator->after(function ($validator) use ($request) {
                $lines = $request->input('lines');
                foreach ($lines as $index => $line) {
                    $lineValidator = \Validator::make($line, [
                        'product_id' => 'required|string|exists:products,id',
                        'quantity' => 'required|numeric|min:1',
                    ]);

                    if ($lineValidator->fails()) {
                        $validator->errors()->add("lines.$index", $lineValidator->errors()->all());
                    }
                }
            });
        }

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $salesInvoice = new SalesInvoice();
        $salesInvoice->fill($request->all());

        $salesInvoice->save();

        try {

            Log::channel('exact-online')->info("Received invoice request for user {$salesInvoice->user->id}");
        } catch (\Exception $exception) {
            Log::channel('exact-online')->error($exception->getMessage());
        }

        return response()->json([], Response::HTTP_CREATED);
    }
}
