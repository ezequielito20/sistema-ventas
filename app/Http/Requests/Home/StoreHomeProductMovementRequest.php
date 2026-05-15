<?php

namespace App\Http\Requests\Home;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreHomeProductMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) Auth::user()?->can('home.inventory.edit');
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:in,out'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
