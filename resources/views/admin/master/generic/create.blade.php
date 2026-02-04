@extends('adminlte::page')

@section('title', 'เพิ่ม' . $title . 'ใหม่ | PrimeForecast')

@section('content_header')
    <h1>เพิ่ม{{ $title }}ใหม่</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route($routeName . '.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="{{ $nameField }}">{{ $title }} <span class="text-danger">*</span></label>
                    <input type="text" name="{{ $nameField }}" id="{{ $nameField }}" class="form-control @error($nameField) is-invalid @enderror" value="{{ old($nameField) }}" required>
                    @error($nameField)
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <a href="{{ route($routeName . '.index') }}" class="btn btn-secondary">ยกเลิก</a>
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
<style>
    .content-wrapper { background-color: #b3d6e4; }
</style>
@stop
