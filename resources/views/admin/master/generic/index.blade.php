@extends('adminlte::page')

@section('title', 'จัดการ' . $title . ' | PrimeForecast')

@section('content_header')
    <h1>จัดการ{{ $title }}</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">รายการ{{ $title }}ทั้งหมด</h3>
            <div class="card-tools">
                <a href="{{ route($routeName . '.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> เพิ่ม{{ $title }}ใหม่
                </a>
            </div>
        </div>
        <div class="card-body">
            <table id="dataTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>{{ $title }}</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $item)
                    <tr>
                        <td>{{ $item->{$primaryKey} }}</td>
                        <td>{{ $item->{$nameField} }}</td>
                        <td>
                            <a href="{{ route($routeName . '.edit', $item->{$primaryKey}) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route($routeName . '.destroy', $item->{$primaryKey}) }}" method="POST" style="display:inline;" onsubmit="return confirm('ยืนยันการลบ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script>
$(function() {
    $('#dataTable').DataTable({
        "language": { "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json" }
    });
});
</script>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<style>
    .content-wrapper { background-color: #b3d6e4; }
</style>
@stop
