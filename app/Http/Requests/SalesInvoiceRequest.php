<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class SalesInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'lines' => 'sometimes|array',
            'lines.*.product_id' => 'required|string|exists:products,id',
            'lines.*.quantity' => 'required|numeric|min:1',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errorMessages = $validator->errors()->getMessages();

        $formatted = [];
        // Reformat 'lines' errors for clarity
        foreach ($errorMessages as $key => $messages) {
            if (preg_match('/^lines\.(\d+)\.(.+)$/', $key, $matches)) {
                $index = (int) $matches[1];
                $field = $matches[2];
                $formatted['lines'][$index][$field] = $messages;
            } else {
                $formatted[$key] = $messages;
            }
        }

        throw new HttpResponseException(response()->json(['errors' => $formatted], Response::HTTP_UNPROCESSABLE_ENTITY));
    }
}
