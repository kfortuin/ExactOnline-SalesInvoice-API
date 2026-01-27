<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\SalesInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use ExactOnline\Services\ExactOnlineService;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ExactOnlineServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $user = User::first();

        if (!$user) {
            $user = User::factory()->create();
        }

        $products = Product::all();

        if (empty($products)) {
            $products = Product::factory()->count(2)->create();
        }

        $salesInvoice = new SalesInvoice();
        $salesInvoice->user_id = $user->id;
        $salesInvoice->invoice_number = SalesInvoice::generateInvoiceNumber();
        $salesInvoice->invoice_date = now()->format('Y-m-d');
        $salesInvoice->due_date = now()->addDays(30)->format('Y-m-d');
        $salesInvoice->save();

        foreach ($products as $product) {
            $salesInvoice->salesInvoiceLines()->create([
                'product_id' => $product->id,
                'quantity' => random_int(1, 5),
            ]);
        }

        $this->salesInvoice = $salesInvoice;
        $this->division = config('app.exact_online_division');
    }

    public function test_sales_invoice_has_been_created()
    {
        $this->assertEquals(SalesInvoice::class, $this->salesInvoice::class);
    }

    public function test_sales_invoice_can_be_posted()
    {
        Http::fake([
            "https://start.exactonline.com/api/v1/{$this->division}/salesinvoice/SalesInvoices"
            => Http::response([], Response::HTTP_CREATED),
        ]);

        $service = app(ExactOnlineService::class);
        $response = $service->sendInvoice($this->salesInvoice);

        Http::assertSent(function ($request) {
            return $request->url() === "https://start.exactonline.com/api/v1/{$this->division}/salesinvoice/SalesInvoices"
                && $request->method() === 'POST'
                && $request['SalesInvoiceLines'] !== [];
        });
        $this->assertEquals(Response::HTTP_CREATED, $response->status());
    }
}
