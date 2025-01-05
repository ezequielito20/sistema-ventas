@extends('adminlte::page')

@section('title', 'Editar Producto')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark font-weight-bold">Editar Producto</h1>
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i>
            Volver
        </a>
    </div>
@stop

@section('content')
<form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" id="productForm">
    @csrf
    @method('PUT')
    <div class="row">
        {{-- Información Básica --}}
        <div class="col-12 col-md-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Información Básica</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="code">Código <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-barcode"></i>
                                        </span>
                                    </div>
                                    <input type="text" 
                                           class="form-control @error('code') is-invalid @enderror" 
                                           id="code" 
                                           name="code" 
                                           value="{{ old('code', $product->code) }}" 
                                           required>
                                </div>
                                @error('code')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category_id">Categoría <span class="text-danger">*</span></label>
                                <select class="form-control select2 @error('category_id') is-invalid @enderror" 
                                        id="category_id" 
                                        name="category_id" 
                                        required>
                                    <option value="">Seleccionar categoría</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" 
                                                {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Resto de campos similares al create pero con valores del producto --}}
                    {{-- ... (similar al create.blade.php pero con $product->campo) ... --}}
                </div>
            </div>
        </div>

        {{-- Imagen actual y cambio --}}
        <div class="col-12 col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Imagen del Producto</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <div class="text-center">
                            <img id="preview" 
                                 src="{{ $product->image ? asset($product->image) : asset('img/no-image.png') }}" 
                                 class="img-fluid mb-2" 
                                 style="max-height: 200px;">
                        </div>
                        <div class="custom-file">
                            <input type="file" 
                                   class="custom-file-input @error('image') is-invalid @enderror" 
                                   id="image" 
                                   name="image" 
                                   accept="image/*">
                            <label class="custom-file-label" for="image">Cambiar imagen</label>
                        </div>
                        @error('image')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-2"></i>
                Actualizar Producto
            </button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                <i class="fas fa-times mr-2"></i>
                Cancelar
            </a>
        </div>
    </div>
</form>
@stop

@section('css')
    {{-- Mismos estilos que en create --}}
@stop

@section('js')
    {{-- Mismo JavaScript que en create --}}
@stop
