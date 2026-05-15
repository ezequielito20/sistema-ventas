<?php

namespace App\Http\Requests\Home;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreHomeTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) Auth::user()?->can('home.finances.transactions');
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:income,expense'],
            'category' => ['required', 'string', 'in:alimentos,servicios,transporte,salud,entretenimiento,educacion,vivienda,ropa,otros'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:500'],
            'transaction_date' => ['required', 'date'],
            'home_bank_account_id' => ['nullable', 'exists:home_bank_accounts,id'],
            'receipt_image' => ['nullable', 'image', 'max:5120'],
        ];
    }
}
