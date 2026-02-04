@extends('adminlte::page')

@section('title', 'Dashboard (ตาราง) | PrimeForecast')

@section('content_header')
    <h1>Sales Dashboard (ตาราง)</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Filter Section -->
    <div class="row mb-2">
        <div class="col-md-12">
            <div class="card card-outline card-primary collapsed-card">
                <div class="card-header py-2">
                    <h3 class="card-title"><i class="fas fa-filter"></i> กรองข้อมูล</h3>
                    <div class="card-tools">
                        @if(request('year') || request('quarter'))
                            <span class="badge badge-info mr-2">
                                @if(request('year'))
                                    ปี {{ request('year') }}
                                @endif
                                @if(request('quarter'))
                                    @if(request('year')) / @endif
                                    Q{{ request('quarter') }}
                                @endif
                            </span>
                        @endif
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body py-2" style="display: none;">
                    <form method="GET" action="{{ route('teamadmin.dashboard.table') }}">
                        <div class="row align-items-end">
                            <div class="col-md-2">
                                <label class="mb-1"><small>ปีงบประมาณ (พ.ศ.):</small></label>
                                <select name="year" class="form-control form-control-sm">
                                    <option value="">ทุกปี</option>
                                    @foreach($availableYears as $y)
                                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="mb-1"><small>ไตรมาส:</small></label>
                                <select name="quarter" class="form-control form-control-sm">
                                    <option value="">ทุกไตรมาส</option>
                                    <option value="1" {{ request('quarter') == '1' ? 'selected' : '' }}>Q1</option>
                                    <option value="2" {{ request('quarter') == '2' ? 'selected' : '' }}>Q2</option>
                                    <option value="3" {{ request('quarter') == '3' ? 'selected' : '' }}>Q3</option>
                                    <option value="4" {{ request('quarter') == '4' ? 'selected' : '' }}>Q4</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-sm btn-block">
                                    <i class="fas fa-search"></i> กรอง
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('teamadmin.dashboard.table') }}" class="btn btn-secondary btn-sm btn-block">
                                    <i class="fas fa-redo"></i> รีเซ็ต
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Forecast Data Table (Team: {{ Auth::user()->nname ?? 'N/A' }})</h3>
        </div>
        <div class="card-body">
            @if(isset($transactions) && count($transactions) > 0)
                <p class="text-success">พบข้อมูล {{ count($transactions) }} รายการ</p>
            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> ไม่พบข้อมูล หรือคุณยังไม่ได้รับมอบหมายทีม
                </div>
            @endif
            <div class="table-responsive">
                <table id="salesTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ชื่อโครงการ</th>
                            <th>หน่วยงาน/บริษัท</th>
                            <th>มูลค่า (฿)</th>
                            <th>สถานะ</th>
                            <th>โอกาสชนะ</th>
                            <th>ปีงบประมาณ</th>
                            <th>วันที่เริ่ม</th>
                            <th>วันยื่น Bidding</th>
                            <th>วันเซ็นสัญญา</th>
                            <th>กลุ่มสินค้า</th>
                            <th>ชื่อผู้ใช้</th>
                            <th>ทีม</th>
                            <th>หมายเหตุ</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $t)
                        <tr>
                            <td>{{ $t->Product_detail }}</td>
                            <td>{{ $t->company->company ?? '-' }}</td>
                            <td>{{ number_format($t->product_value) }}</td>
                            <td>{{ $t->latestStep->step->level ?? '-' }}</td>
                            <td>{{ $t->priority->priority ?? '-' }}</td>
                            <td>{{ $t->fiscalyear }}</td>
                            <td>{{ $t->contact_start_date }}</td>
                            <td>{{ $t->date_of_closing_of_sale }}</td>
                            <td>{{ $t->sales_can_be_close }}</td>
                            <td>{{ $t->productGroup->product ?? '-' }}</td>
                            <td>{{ $t->user->nname ?? '' }} {{ $t->user->surename ?? '' }}</td>
                            <td>{{ $t->team->team ?? '-' }}</td>
                            <td>{{ $t->remark }}</td>
                            <td class="text-center">
                                <a href="{{ route('teamadmin.sales.edit', $t->transac_id) }}" class="btn btn-sm btn-info" title="แก้ไข">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>

<script>
$(function () {
    $("#salesTable").DataTable({
        "responsive": false,
        "lengthChange": true,
        "autoWidth": false,
        "language": { "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json" },
        "dom": 'lBfrtip',
        "buttons": [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Export to Excel',
                className: 'btn btn-success',
                titleAttr: 'Export to Excel',
                bom: true,
                exportOptions: {
                    columns: ':not(:last-child)'
                }
            },
            {
                extend: 'colvis',
                text: 'เลือกคอลัมน์',
                className: 'btn btn-info'
            }
        ],
        "order": [[0, 'desc']]
    });
});
</script>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<style>
    .content-wrapper {
        background-color: #b3d6e4;
    }
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
</style>
@stop
