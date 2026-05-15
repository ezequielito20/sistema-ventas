@extends('layouts.app')

@section('title', 'Cuentas Bancarias')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-100">Cuentas bancarias</h1>
        <p class="text-sm text-slate-400 mt-1">Cuentas manuales (no conectadas)</p>
    </div>

    <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl overflow-hidden">
        @php
            $accounts = \App\Models\Home\HomeBankAccount::where('company_id', auth()->user()->company_id)->get();
        @endphp

        @if($accounts->count() > 0)
            <div class="divide-y divide-slate-700/30">
                @foreach($accounts as $account)
                    <div class="flex items-center justify-between p-4">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-lg bg-blue-500/20 flex items-center justify-center">
                                <i class="fas fa-university text-blue-400"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-200">{{ $account->bank_name }}</p>
                                <p class="text-xs text-slate-500">{{ $account->masked_number ?? $account->account_type }} · {{ $account->currency_code }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-slate-100">${{ number_format($account->balance, 2) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-6 text-center">
                <p class="text-sm text-slate-500">No hay cuentas registradas.</p>
            </div>
        @endif
    </div>
</div>
@endsection
