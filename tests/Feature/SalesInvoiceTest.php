<?php

namespace Feature;

use App\Models\Product;
use App\Models\SalesInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use Symfony\Component\HttpFoundation\Response;

class SalesInvoiceTest extends TestCase
{
     use RefreshDatabase;

    // Seed the database with a user and some test products
    protected bool $seed = true;

    public function setUp(): void
    {
        parent::setUp();
    }

    // Test API endpoint and test if it returns 201 status code. Prevent actual creation of sales invoice in Exact Online by mocking the service.
    public function test_sales_invoices_are_created(): void
    {
        $user = User::first();
        $products = Product::inRandomOrder()->take(2)->get();

        $payload = [
            'user_id' => $user->id,
        ];

        foreach ($products as $product) {
            $payload['lines'][] = [
                'product_id' => $product->id,
                'quantity' => random_int(1, 5),
            ];
        }

        $response = $this->postJson('/api/sales-invoices', $payload);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseCount('sales_invoices', 1);
        $this->assertDatabaseCount('sales_invoice_lines', 2);
    }

    public function test_payload_is_validated(): void
    {
        $payload = [
            'user_id' => null,
            'lines' => [
                [
                    'product_id' => 0, // Invalid product ID
                    'quantity' => 'test', // Invalid quantity
                ],
                [
                    'product_id' => 'test', // Invalid product ID
                    'quantity' => 0, // Invalid quantity
                ],
            ],
        ];

        $response = $this->postJson('/api/sales-invoices', $payload);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors('user_id');
        $response->assertJsonValidationErrors('lines');
    }
}
