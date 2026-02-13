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
                                    ปี {{ request('year') + 543 }}
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
                    <form method="GET" action="{{ route('admin.dashboard.table') }}">
                        <div class="row align-items-end">
                            <div class="col-md-2">
                                <label class="mb-1"><small>ปีงบประมาณ (พ.ศ.):</small></label>
                                <select name="year" class="form-control form-control-sm">
                                    <option value="">ทุกปี</option>
                                    @foreach($availableYears as $y)
                                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                                            {{ $y + 543 }}
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
                                <a href="{{ route('admin.dashboard.table') }}" class="btn btn-secondary btn-sm btn-block">
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
            <h3 class="card-title">Forecast Data Table (All Users)</h3>
        </div>
        <div class="card-body">
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
                        <tr class="clickable-row" style="cursor: pointer;"
                            data-id="{{ $t->transac_id }}"
                            data-project="{{ $t->Product_detail }}"
                            data-company="{{ $t->company->company ?? '-' }}"
                            data-value="{{ number_format($t->product_value) }}"
                            data-status="{{ $t->latestStep->step->level ?? '-' }}"
                            data-priority="{{ $t->priority->priority ?? '-' }}"
                            data-year="{{ $t->fiscalyear + 543 }}"
                            data-start="{{ $t->contact_start_date }}"
                            data-bidding="{{ $t->date_of_closing_of_sale }}"
                            data-contract="{{ $t->sales_can_be_close }}"
                            data-product="{{ $t->productGroup->product ?? '-' }}"
                            data-user="{{ $t->user->nname ?? '' }} {{ $t->user->surename ?? '' }}"
                            data-team="{{ $t->team->team ?? '-' }}"
                            data-source="{{ $t->sourceBudget->Source_budge ?? '-' }}"
                            data-contact-person="{{ $t->contact_person ?? '-' }}"
                            data-contact-phone="{{ $t->contact_phone ?? '-' }}"
                            data-contact-email="{{ $t->contact_email ?? '-' }}"
                            data-contact-note="{{ $t->contact_note ?? '-' }}"
                            data-remark="{{ $t->remark }}">
                            <td>{{ $t->Product_detail }}</td>
                            <td>{{ $t->company->company ?? '-' }}</td>
                            <td>{{ number_format($t->product_value) }}</td>
                            <td>{{ $t->latestStep->step->level ?? '-' }}</td>
                            <td>{{ $t->priority->priority ?? '-' }}</td>
                            <td>{{ $t->fiscalyear + 543 }}</td>
                            <td>{{ thaiDate($t->contact_start_date) }}</td>
                            <td>{{ thaiDate($t->date_of_closing_of_sale) }}</td>
                            <td>{{ thaiDate($t->sales_can_be_close) }}</td>
                            <td>{{ $t->productGroup->product ?? '-' }}</td>
                            <td>{{ $t->user->nname ?? '' }} {{ $t->user->surename ?? '' }}</td>
                            <td>{{ $t->team->team ?? '-' }}</td>
                            <td>{{ $t->remark }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.sales.edit', $t->transac_id) }}" class="btn btn-sm btn-info" title="แก้ไข">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                <a href="{{ route('admin.sales.transfer', $t->transac_id) }}" class="btn btn-sm btn-warning" title="โอนข้อมูล">
                                    <i class="fas fa-exchange-alt"></i>
                                </a>
                                <form action="{{ route('admin.sales.delete', $t->transac_id) }}" method="POST" style="display: inline;" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลนี้?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="ลบ">
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
    </div>

    <!-- View Detail Modal -->
    <div class="modal fade" id="viewDetailModal" tabindex="-1" role="dialog" aria-labelledby="viewDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="viewDetailModalLabel"><i class="fas fa-info-circle"></i> รายละเอียดข้อมูลการขาย</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <h5 class="text-primary" id="modal-project"></h5>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-outline card-info mb-3">
                                <div class="card-header py-2"><strong><i class="fas fa-building"></i> ข้อมูลโครงการ</strong></div>
                                <div class="card-body py-2">
                                    <p class="mb-1"><strong>หน่วยงาน/บริษัท:</strong> <span id="modal-company"></span></p>
                                    <p class="mb-1"><strong>มูลค่า:</strong> <span id="modal-value" class="text-success font-weight-bold"></span> บาท</p>
                                    <p class="mb-1"><strong>กลุ่มสินค้า:</strong> <span id="modal-product"></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-outline card-warning mb-3">
                                <div class="card-header py-2"><strong><i class="fas fa-coins"></i> งบประมาณ</strong></div>
                                <div class="card-body py-2">
                                    <p class="mb-1"><strong>แหล่งงบประมาณ:</strong> <span id="modal-source"></span></p>
                                    <p class="mb-1"><strong>ปีงบประมาณ:</strong> <span id="modal-year"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-outline card-success mb-3">
                                <div class="card-header py-2"><strong><i class="fas fa-calendar-alt"></i> วันที่สำคัญ</strong></div>
                                <div class="card-body py-2">
                                    <p class="mb-1"><strong>วันที่เริ่ม:</strong> <span id="modal-start"></span></p>
                                    <p class="mb-1"><strong>วันยื่น Bidding:</strong> <span id="modal-bidding"></span></p>
                                    <p class="mb-1"><strong>วันเซ็นสัญญา:</strong> <span id="modal-contract"></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-outline card-danger mb-3">
                                <div class="card-header py-2"><strong><i class="fas fa-chart-line"></i> สถานะ</strong></div>
                                <div class="card-body py-2">
                                    <p class="mb-1"><strong>สถานะปัจจุบัน:</strong> <span id="modal-status" class="badge badge-info"></span></p>
                                    <p class="mb-1"><strong>โอกาสชนะ:</strong> <span id="modal-priority"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-outline card-secondary mb-3">
                                <div class="card-header py-2"><strong><i class="fas fa-users"></i> ผู้รับผิดชอบ</strong></div>
                                <div class="card-body py-2">
                                    <p class="mb-1"><strong>ชื่อผู้ใช้:</strong> <span id="modal-user"></span></p>
                                    <p class="mb-1"><strong>ทีม:</strong> <span id="modal-team"></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-outline card-primary mb-3">
                                <div class="card-header py-2"><strong><i class="fas fa-user-tie"></i> ข้อมูลลูกค้า</strong></div>
                                <div class="card-body py-2">
                                    <p class="mb-1"><strong>ชื่อผู้ติดต่อ:</strong> <span id="modal-contact-person"></span></p>
                                    <p class="mb-1"><strong>เบอร์โทร:</strong> <span id="modal-contact-phone"></span></p>
                                    <p class="mb-1"><strong>อีเมล:</strong> <span id="modal-contact-email"></span></p>
                                    <p class="mb-1"><strong>อื่นๆ:</strong> <span id="modal-contact-note"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-outline card-dark">
                                <div class="card-header py-2"><strong><i class="fas fa-sticky-note"></i> หมายเหตุ</strong></div>
                                <div class="card-body py-2">
                                    <p class="mb-0" id="modal-remark"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                </div>
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

    // Row click to show detail modal
    $('#salesTable tbody').on('click', 'tr.clickable-row', function(e) {
        // Don't trigger if clicking on action buttons
        if ($(e.target).closest('td:last-child').length) return;
        
        var row = $(this);
        $('#modal-project').text(row.data('project'));
        $('#modal-company').text(row.data('company'));
        $('#modal-value').text(row.data('value'));
        $('#modal-status').text(row.data('status'));
        $('#modal-priority').text(row.data('priority'));
        $('#modal-year').text(row.data('year'));
        $('#modal-start').text(row.data('start') ? formatThaiDate(row.data('start')) : '-');
        $('#modal-bidding').text(row.data('bidding') ? formatThaiDate(row.data('bidding')) : '-');
        $('#modal-contract').text(row.data('contract') ? formatThaiDate(row.data('contract')) : '-');
        $('#modal-product').text(row.data('product'));
        $('#modal-user').text(row.data('user'));
        $('#modal-team').text(row.data('team'));
        $('#modal-source').text(row.data('source'));
        $('#modal-contact-person').text(row.data('contact-person'));
        $('#modal-contact-phone').text(row.data('contact-phone'));
        $('#modal-contact-email').text(row.data('contact-email'));
        $('#modal-contact-note').text(row.data('contact-note'));
        $('#modal-remark').text(row.data('remark') || '-');
        
        $('#viewDetailModal').modal('show');
    });

    // Helper function to format date to Thai format
    function formatThaiDate(dateStr) {
        if (!dateStr || dateStr === '-') return '-';
        try {
            const date = new Date(dateStr);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear() + 543;
            return `${day}/${month}/${year}`;
        } catch (e) {
            return dateStr;
        }
    }
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
