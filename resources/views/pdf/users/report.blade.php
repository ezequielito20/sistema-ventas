@extends('pdf.layouts.document')

@section('pdf-document-title', 'Informe de usuarios')

@section('pdf-title', 'Informe de usuarios de la empresa')

@section('pdf-subtitle')
    Listado consolidado de usuarios con estado de verificación y roles asignados.
@endsection

@section('pdf-content')
    <table class="pdf-summary" cellspacing="0">
        <tr>
            <td>
                <strong>Resumen:</strong>
                {{ $users->count() }} usuarios registrados
                · {{ $verifiedCount }} verificados
                · {{ $pendingCount }} pendientes
                · {{ $withRolesCount }} con roles asignados
            </td>
        </tr>
    </table>

    <table class="pdf-table" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 6%;">#</th>
                <th style="width: 24%;">Usuario</th>
                <th style="width: 26%;">Correo</th>
                <th style="width: 16%;">Estado</th>
                <th style="width: 28%;">Roles</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td class="pdf-num">{{ $loop->iteration }}</td>
                    <td><strong>{{ $user->name }}</strong></td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @if ($user->email_verified_at)
                            <span class="pdf-badge">Verificado</span>
                        @else
                            <span class="pdf-badge pdf-badge--system">Pendiente</span>
                        @endif
                    </td>
                    <td>
                        {{ $user->roles->pluck('name')->implode(', ') ?: 'Sin rol asignado' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

@section('pdf-footer-module')
    Módulo: Usuarios · Informe estándar
@endsection
