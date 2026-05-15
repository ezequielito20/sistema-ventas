<?php

namespace App\Http\Requests\Home;

use App\Support\Home\HomeProductCategories;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreHomeProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) Auth::user()?->can('home.inventory.create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:' . implode(',', HomeProductCategories::ALL)],
            'quantity' => ['required', 'integer', 'min:0'],
            'min_quantity' => ['required', 'integer', 'min:0'],
            'max_quantity' => ['nullable', 'integer', 'min:0', 'gte:min_quantity'],
            'unit' => ['required', 'string', 'in:unidad,kg,g,ml,l,paquete,caja,bolsa,rollo,par'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'barcode' => [
                'nullable', 'string', 'max:50',
                Rule::unique('home_products', 'barcode')
                    ->where('company_id', Auth::user()->company_id)
                    ->ignore($this->route('product')),
            ],
            'image' => ['nullable', 'image', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'barcode.unique' => 'Este código de barras ya está registrado para otro producto.',
            'max_quantity.gte' => 'La cantidad máxima debe ser mayor o igual a la cantidad mínima.',
        ];
    }
}
