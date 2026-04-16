@extends('pdf.layouts.document')

@section('pdf-document-title', 'Informe de roles')

@section('pdf-title', 'Informe de roles y permisos')

@section('pdf-subtitle')
    Listado consolidado de roles de su empresa con conteos de usuarios y permisos asociados.
@endsection

@section('pdf-content')
    @php
        $totalPerms = $roles->sum(fn ($r) => $r->permissions->count());
    @endphp
    <table class="pdf-summary" cellspacing="0">
        <tr>
            <td>
                <strong>Resumen:</strong>
                {{ $roles->count() }} {{ $roles->count() === 1 ? 'rol registrado' : 'roles registrados' }}
                · {{ $totalPerms }} permisos listados en total entre todos los roles
            </td>
        </tr>
    </table>

    <table class="pdf-table" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 6%;">#</th>
                <th style="width: 22%;">Rol</th>
                <th style="width: 14%;">Tipo</th>
                <th style="width: 18%;">Alta</th>
                <th style="width: 14%;">Usuarios</th>
                <th style="width: 14%;">Permisos</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($roles as $role)
                @php
                    $isSystem = $role->isSystemRole();
                @endphp
                <tr>
                    <td class="pdf-num">{{ $loop->iteration }}</td>
                    <td><strong>{{ $role->name }}</strong></td>
                    <td>
                        @if ($isSystem)
                            <span class="pdf-badge pdf-badge--system">Sistema</span>
                        @else
                            <span class="pdf-badge">Empresa</span>
                        @endif
                    </td>
                    <td>{{ $role->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td>
                    <td class="pdf-num">{{ $role->users->count() }}</td>
                    <td class="pdf-num">{{ $role->permissions->count() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

@section('pdf-footer-module')
    Módulo: Roles · Informe estándar
@endsection
