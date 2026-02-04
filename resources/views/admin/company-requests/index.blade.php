@extends('adminlte::page')

@section('title', 'จัดการคำขอเพิ่มบริษัท | PrimeForecast')

@section('content_header')
    <h1>จัดการคำขอเพิ่มบริษัท</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">คำขอเพิ่มบริษัททั้งหมด</h3>
        </div>
        <div class="card-body">
            <table id="dataTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>วันที่ขอ</th>
                        <th>ชื่อบริษัท</th>
                        <th>หมายเหตุ</th>
                        <th>ผู้ขอ</th>
                        <th>สถานะ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $request)
                    <tr>
                        <td>{{ $request->request_date }}</td>
                        <td><strong>{{ $request->company_name }}</strong></td>
                        <td>{{ $request->notes ?? '-' }}</td>
                        <td>{{ $request->user->nname ?? '' }} {{ $request->user->surename ?? '' }}</td>
                        <td>
                            @if($request->status == 'pending')
                                <span class="badge badge-warning">รอดำเนินการ</span>
                            @elseif($request->status == 'approved')
                                <span class="badge badge-success">อนุมัติแล้ว</span>
                            @else
                                <span class="badge badge-danger">ปฏิเสธ</span>
                            @endif
                        </td>
                        <td>
                            @if($request->status == 'pending')
                                <form action="{{ route('admin.company-requests.approve', $request->request_id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('ยืนยันการอนุมัติและเพิ่มบริษัทนี้?')">
                                        <i class="fas fa-check"></i> อนุมัติ
                                    </button>
                                </form>
                                <form action="{{ route('admin.company-requests.reject', $request->request_id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('ยืนยันการปฏิเสธคำขอนี้?')">
                                        <i class="fas fa-times"></i> ปฏิเสธ
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('admin.company-requests.destroy', $request->request_id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('ยืนยันการลบคำขอนี้?')">
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
        "language": { "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json" },
        "order": [[0, 'desc']]
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
