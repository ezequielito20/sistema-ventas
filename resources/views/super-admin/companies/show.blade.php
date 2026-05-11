@extends('layouts.app')

@section('title', 'Detalle de Cliente — Panel Super Admin')

@section('content')
    <livewire:super-admin.company-show :company-id="$companyId" />
@endsection
