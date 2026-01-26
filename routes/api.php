<?php

use Illuminate\Support\Facades\Route;

Route::group(
    [
        'namespace' => 'App\Http\Controllers',
//        'middleware' => ['auth'],
    ],
    static function () {
        Route::post('/sales-invoices', ['as' => 'api.sales-invoices', 'uses' => 'SalesInvoiceController@createSalesInvoice']);
    }
);
