@extends('adminlte::page')

@section('title', 'Sales Dashboard (Table) | PrimeForecast')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Sales Dashboard</h1>
        </div>
    </div>
@stop

@section('content')
    <!-- Filter Section -->
    <div class="row mb-2">
        <div class="col-md-12">
            <div class="card card-outline card-success collapsed-card">
                <div class="card-header py-2">
                    <h3 class="card-title"><i class="fas fa-filter"></i> กรองข้อมูล</h3>
                    <div class="card-tools">
                        @if(request('year') || request('quarter'))
                            <span class="badge badge-success mr-2">
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
                    <form method="GET" action="{{ route('user.dashboard.table') }}">
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
                                <button type="submit" class="btn btn-success btn-sm btn-block">
                                    <i class="fas fa-search"></i> กรอง
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('user.dashboard.table') }}" class="btn btn-secondary btn-sm btn-block">
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
            <h3 class="card-title">Forecast Data Table ({{ Auth::user()->nname ?: 'Sales' }})</h3>
        </div>
        <div class="card-body">
            <table id="salesTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ชื่อโครงการ</th>
                        <th>หน่วยงาน/บริษัท</th>
                        <th>มูลค่า (฿)</th>
                        <th>แหล่งงบประมาณ</th>
                        <th>ปีงบประมาณ</th>
                        <th>กลุ่มสินค้า</th>
                        <th>ทีม</th>
                        <th>โอกาสชนะ</th>
                        <th>วันที่เริ่ม</th>
                        <th>วันยื่น Bidding</th>
                        <th>วันเซ็นสัญญา</th>
                        <th>สถานะ</th>
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
                        <td>{{ $t->sourceBudget->Source_budge ?? '-' }}</td>
                        <td>{{ $t->fiscalyear }}</td>
                        <td>{{ $t->productGroup->product ?? '-' }}</td>
                        <td>{{ $t->team->team ?? '-' }}</td>
                        <td>{{ $t->priority->priority ?? '-' }}</td>
                        <td>{{ $t->contact_start_date }}</td>
                        <td>{{ $t->date_of_closing_of_sale }}</td>
                        <td>{{ $t->sales_can_be_close }}</td>
                        <td>{{ $t->latestStep->step->level ?? '-' }}</td>
                        <td>{{ $t->remark }}</td>
                        <td class="text-center">
                            <a href="{{ route('user.sales.edit', $t->transac_id) }}" class="btn btn-sm btn-info" title="แก้ไข">
                                <i class="fas fa-pencil-alt"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for requesting new company -->
    <div class="modal fade" id="requestCompanyModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">แบบฟอร์มขอเพิ่มหน่วยงาน/บริษัท</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="companyRequestForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="newCompanyName">ชื่อหน่วยงาน/บริษัทที่ต้องการเพิ่ม</label>
                            <input type="text" class="form-control" id="newCompanyName" name="company_name" required>
                        </div>
                        <div class="form-group">
                            <label for="companyNotes">รายละเอียดเพิ่มเติม (ถ้ามี)</label>
                            <textarea class="form-control" id="companyNotes" name="notes" rows="3"></textarea>
                        </div>
                        <div id="requestStatus" class="mt-3"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                        <button type="submit" class="btn btn-primary">ส่งคำขอ</button>
                    </div>
                </form>
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
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "language": { "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json" },
        "dom": 'lBfrtip',
        "buttons": [
            {
                text: '<i class="fas fa-plus-circle"></i> ขอเพิ่มหน่วยงาน',
                className: 'btn btn-primary',
                action: function (e, dt, node, config) {
                    $('#requestCompanyModal').modal('show');
                    $('#companyRequestForm')[0].reset();
                    $('#requestStatus').html('');
                }
            },
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
        ]
    });

    $('#companyRequestForm').on('submit', function(e) {
        e.preventDefault();

        var companyName = $('#newCompanyName').val();
        if (companyName.trim() === '') {
            alert('กรุณากรอกชื่อบริษัท');
            return;
        }

        $('#requestStatus').html('<div class="alert alert-info">กำลังส่งคำขอ...</div>');
        $('button[type="submit"]', this).prop('disabled', true);

        $.ajax({
            url: '{{ route("user.company.request") }}',
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#requestStatus').html('<div class="alert alert-success">ส่งคำขอเรียบร้อยแล้ว!</div>');
                    setTimeout(function() {
                        $('#requestCompanyModal').modal('hide');
                    }, 2000);
                } else {
                    $('#requestStatus').html('<div class="alert alert-danger">เกิดข้อผิดพลาด: ' + response.message + '</div>');
                }
            },
            error: function() {
                $('#requestStatus').html('<div class="alert alert-danger">เกิดข้อผิดพลาดในการเชื่อมต่อ</div>');
            },
            complete: function() {
                $('button[type="submit"]', '#companyRequestForm').prop('disabled', false);
            }
        });
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
</style>
@stop
