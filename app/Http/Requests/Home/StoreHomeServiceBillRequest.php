<?php

namespace App\Http\Requests\Home;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreHomeServiceBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) Auth::user()?->can('home.finances.bills');
    }

    public function rules(): array
    {
        return [
            'home_service_id' => ['required', 'exists:home_services,id'],
            'period' => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
            'cutoff_date' => ['nullable', 'date'],
            'bill_image' => ['nullable', 'image', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
