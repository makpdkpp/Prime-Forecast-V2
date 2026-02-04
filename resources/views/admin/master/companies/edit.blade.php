@extends('adminlte::page')

@section('title', 'แก้ไขข้อมูลบริษัท | PrimeForecast')

@section('content_header')
    <h1>แก้ไขข้อมูลบริษัท</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.companies.update', $company->company_id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="company">ชื่อบริษัท <span class="text-danger">*</span></label>
                    <input type="text" name="company" id="company" class="form-control @error('company') is-invalid @enderror" value="{{ old('company', $company->company) }}" required>
                    @error('company')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="Industry_id">อุตสาหกรรม <span class="text-danger">*</span></label>
                    <select name="Industry_id" id="Industry_id" class="form-control @error('Industry_id') is-invalid @enderror" required>
                        <option value="">-- เลือกอุตสาหกรรม --</option>
                        @foreach($industries as $industry)
                            <option value="{{ $industry->Industry_id }}" {{ old('Industry_id', $company->Industry_id) == $industry->Industry_id ? 'selected' : '' }}>
                                {{ $industry->Industry }}
                            </option>
                        @endforeach
                    </select>
                    @error('Industry_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary">ยกเลิก</a>
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
