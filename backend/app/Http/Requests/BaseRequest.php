<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * By default, we allow all requests. Specific requests can override this.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Handle a failed validation attempt.
     * This method overrides the default behavior to use our custom error response helper.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        // Get all validation error messages.
        $errorsBag = $validator->errors();
        $errors = $errorsBag->toArray();

        // Create a custom error response using our helper function.
        // The response_error() function is from your helpers file.
        $response = response_error($errorsBag->first(), $errors, 422);

        // Throw an exception with our custom response.
        // This stops the request from proceeding further.
        throw new HttpResponseException($response);
    }
}
