<?php

namespace App\Services;

use App\Http\Controllers\CustomerController;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Alta y edición de clientes — misma validación y formato que {@see CustomerController}.
 */
class CustomerPersistenceService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function validateAndCreate(array $data): Customer
    {
        $validated = Validator::make($data, $this->rulesForCreate(), $this->messages())->validate();

        return DB::transaction(function () use ($validated) {
            $customerData = [
                'name' => ucwords(strtolower($validated['name'])),
                'nit_number' => ! empty($validated['nit_number'] ?? null) ? $validated['nit_number'] : null,
                'phone' => ! empty($validated['phone'] ?? null) ? $validated['phone'] : null,
                'email' => ! empty($validated['email'] ?? null) ? strtolower($validated['email']) : null,
                'total_debt' => isset($validated['total_debt']) && $validated['total_debt'] !== '' ? $validated['total_debt'] : 0,
                'company_id' => Auth::user()->company_id,
            ];

            return Customer::create($customerData);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function validateAndUpdate(Customer $customer, array $data): Customer
    {
        $validated = Validator::make($data, $this->rulesForUpdate($customer->id), $this->messages())->validate();

        $customerData = [
            'name' => ucwords(strtolower($validated['name'])),
            'nit_number' => ! empty($validated['nit_number'] ?? null) ? $validated['nit_number'] : null,
            'phone' => ! empty($validated['phone'] ?? null) ? $validated['phone'] : null,
            'email' => ! empty($validated['email'] ?? null) ? strtolower($validated['email']) : null,
            'total_debt' => isset($validated['total_debt']) && $validated['total_debt'] !== '' ? $validated['total_debt'] : 0,
        ];

        $customer->update($customerData);

        return $customer->fresh();
    }

    /**
     * @return array<string, mixed>
     */
    public function rulesForCreate(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-]+$/u'],
            'nit_number' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('customers', 'nit_number'),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('customers', 'email'),
            ],
            'total_debt' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function rulesForUpdate(int $customerId): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-]+$/u'],
            'nit_number' => [
                'nullable',
                'string',
                'max:20',
                'unique:customers,nit_number,'.$customerId,
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'unique:customers,phone,'.$customerId,
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                'unique:customers,email,'.$customerId,
            ],
            'total_debt' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'name.required' => 'El nombre del cliente es obligatorio',
            'name.regex' => 'El nombre solo debe contener letras, espacios y guiones',
            'nit_number.unique' => 'Este NIT ya está registrado para otro cliente',
            'email.email' => 'El formato del correo electrónico no es válido',
            'email.unique' => 'Este correo electrónico ya está registrado para otro cliente',
            'phone.unique' => 'Este teléfono ya está registrado',
            'total_debt.min' => 'La deuda no puede ser un valor negativo',
            'total_debt.numeric' => 'La deuda debe ser un valor numérico',
        ];
    }
}
