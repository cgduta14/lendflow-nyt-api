<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class NytBestSellersListRequest extends FormRequest
{
    public function rules(): array {
        return [
            'author'    => 'nullable|string',
            'title'     => 'nullable|string',
            'offset'    => [
                'nullable',
                'numeric',
                function ($attribute, $value, $fail) {
                    if (is_numeric($value) && ($value % 20 !== 0)) {
                        $fail("{$attribute} must be divisible by 20.");
                    }
                },
            ],
            'isbn' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    foreach (explode(';', $value) as $isbn) {
                        $isbnLength = Str::length($isbn);
                        if (!$isbnLength) {
                            return $fail($attribute . 'must not end with a semicolon.');
                        }

                        if (10 !== $isbnLength && 13 !== $isbnLength) {
                            return $fail($attribute . 'must have 10 or 13 digits.');
                        }
                    }
                    return true;
                },
            ],
        ];
    }

    public function authorize(): bool {
        return true;
    }
}
