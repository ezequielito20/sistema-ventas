@extends('pdf.layouts.document')

@section('pdf-document-title', 'Informe de permisos')

@section('pdf-title', 'Informe de permisos del sistema')

@section('pdf-subtitle')
    Catálogo consolidado de permisos con alcance, asignación por roles y usuarios impactados.
@endsection

@section('pdf-content')
    <table class="pdf-summary" cellspacing="0">
        <tr>
            <td>
                <strong>Resumen:</strong>
                {{ $permissions->count() }} permisos registrados
                · {{ $rolesCount }} roles con permisos
                · {{ $usersCount }} usuarios en el sistema
            </td>
        </tr>
    </table>

    <table class="pdf-table" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 6%;">#</th>
                <th style="width: 34%;">Permiso</th>
                <th style="width: 12%;">Guard</th>
                <th style="width: 12%;">Roles</th>
                <th style="width: 14%;">Usuarios</th>
                <th style="width: 22%;">Creación</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($permissions as $permission)
                <tr>
                    <td class="pdf-num">{{ $loop->iteration }}</td>
                    <td><strong>{{ $permission->name }}</strong></td>
                    <td>{{ $permission->guard_name }}</td>
                    <td class="pdf-num">{{ $permission->roles->count() }}</td>
                    <td class="pdf-num">{{ $permission->users_count ?? 0 }}</td>
                    <td>{{ $permission->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

@section('pdf-footer-module')
    Módulo: Permisos · Informe estándar
@endsection
