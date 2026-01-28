# Exact Online - Sales Invoice creation

## Setup
This application is built in Laravel 12, with PHP 8.3 and runs off a sqlite database.
- Run `composer install` to install the dependencies.
- Create a new sqlite database file by running `touch database/database.sqlite`.
- Add the `.env` file by copying `.env.example` to `.env`.
  - Supply the `.env` file with the following linesL
    ```
    # Exact Online
    EXACT_ONLINE_DIVISION=1234567
    EXACT_ONLINE_CLIENT_ID="79014b33-7fed-4549-a103-159bd475ce34" # random GUID
      ```
- Generate the `APP_KEY` by running `php artisan key:generate`.  
- Run ``php artisan migrate:fresh --seed`` to create the necessary tables and fill them with a test user and some test Products.

**Exact Online** is currently mocked. No external API calls are made and no authentication is required or provided. Laravel's HTTP Client is used to simulate the API calls.  
The ExactOnline SalesInvoice model is based on Exact Online's POST documentation: https://start.exactonline.nl/docs/HlpRestAPIResourcesDetails.aspx?name=SalesInvoiceSalesInvoices

## Routes
The only route available is the `/api/sales-invoice` POST route.
### Request
The request body must be a JSON object with the following structure:
```json
{
    "user_id": 1,
    "lines": [
        {
            "product_id": "1234",
            "quantity": 2
        }
    ]
}
```
The `user_id` field is required and must correspond to an existing user in the database.  
If you ran the migrations, you should be able to use `1` as the user ID.  
For the products ('lines') you can copy one or more of the product IDs that are shown in the table that is rendered after running the migrations and seeders. Each line must contain a valid `product_id` and a `quantity` greater than zero.  

Postman can be used to send the POST request. Make sure to set the correct JSON headers.

## Testing
You can run the tests with `php artisan test`.  
There are tests for the Sales Invoice creation endpoint, covering both successful and failure scenarios, as well as tests for the Exact Online service that simulates the API calls.  
Transactions are used in the tests to ensure database changes are rolled back after each test. Using Postman _will_ store a SalesInvoice and its lines in the database.

## Logging
Successful and failed attempts to POST to Exact Online are logged in the `exact_online` channel. The logfile can be found in `/storage/logs/exact_online-YEAR-MONTH-DAY.log`.
