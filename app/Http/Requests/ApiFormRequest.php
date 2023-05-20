<?php

namespace App\Http\Requests;

use App\Enums\EStatusApi;
use App\Http\Response\ResponseError;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApiFormRequest extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        $first = $validator->errors()->first();

        $response = response()->json((new ResponseError(422, $first))->toArray());

        throw new HttpResponseException($response);
    }
}
