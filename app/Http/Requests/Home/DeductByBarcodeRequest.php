<?php

namespace App\Http\Requests\Home;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DeductByBarcodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) Auth::user()?->can('home.scan_deduct');
    }

    public function rules(): array
    {
        return [
            'barcode' => ['required', 'string', 'max:50'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
