@extends('adminlte::page')

@section('title', 'Sales Dashboard (ตาราง) | PrimeForecast')

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
            <div class="card card-outline card-success collapsed-card">
                <div class="card-header py-2">
                    <h3 class="card-title"><i class="fas fa-filter"></i> กรองข้อมูล</h3>
                    <div class="card-tools">
                        @if(request('year') || request('quarter'))
                            <span class="badge badge-success mr-2">
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
                    <form method="GET" action="{{ route('user.dashboard.table') }}" id="tableFilterForm">
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
                                <button type="submit" class="btn btn-success btn-sm btn-block" id="tableFilterBtn">
                                    <span id="tableFilterBtnText"><i class="fas fa-search"></i> กรอง</span>
                                    <span id="tableFilterBtnSpinner" class="spinner-border spinner-border-sm ml-1" style="display:none;"></span>
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
                            <th>ทีม</th>
                            <th>หมายเหตุ</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via DataTables AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- View Detail Modal -->
    <div class="modal fade" id="viewDetailModal" tabindex="-1" role="dialog" aria-labelledby="viewDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="viewDetailModalLabel"><i class="fas fa-info-circle"></i> รายละเอียดข้อมูลการขาย</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <h5 class="text-success" id="modal-project"></h5>
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
                                    <p class="mb-1"><strong>ทีม:</strong> <span id="modal-team"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-outline card-primary mb-3">
                                <div class="card-header py-2"><strong><i class="fas fa-user-tie"></i> ข้อมูลผู้ติดต่อ</strong></div>
                                <div class="card-body py-2">
                                    <p class="mb-1"><strong>ชื่อผู้ติดต่อ:</strong> <span id="modal-contact-person"></span></p>
                                    <p class="mb-1"><strong>เบอร์โทร:</strong> <span id="modal-contact-phone"></span></p>
                                    <p class="mb-1"><strong>อีเมล:</strong> <span id="modal-contact-email"></span></p>
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
                    <button type="button" id="modal-edit-btn" class="btn btn-info" data-id=""><i class="fas fa-pencil-alt"></i> แก้ไข</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-pencil-alt"></i> แก้ไขข้อมูลการขาย</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form id="editForm" autocomplete="off">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <div class="modal-body" style="overflow-y:auto; max-height:70vh;">
                        <div id="editFormAlert" class="alert alert-danger" style="display:none;"></div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>ชื่อโครงการ <span class="text-danger">*</span></label>
                                    <input type="text" name="Product_detail" id="ef_Product_detail" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>หน่วยงาน / บริษัท <span class="text-danger">*</span></label>
                                    <select name="company_id" id="ef_company_id" class="form-control" required>
                                        <option value="">-- เลือกบริษัท/หน่วยงาน --</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>กลุ่มสินค้า <span class="text-danger">*</span></label>
                                    <select name="Product_id" id="ef_Product_id" class="form-control" required>
                                        <option value="">-- เลือกกลุ่มสินค้า --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>มูลค่าโครงการ (บาท) <span class="text-danger">*</span></label>
                                    <input type="text" name="product_value" id="ef_product_value" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>ทีมขาย <span class="text-danger">*</span></label>
                                    <select name="team_id" id="ef_team_id" class="form-control" required>
                                        <option value="">-- เลือกทีม --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>โอกาสการชนะ</label>
                                    <select name="priority_id" id="ef_priority_id" class="form-control">
                                        <option value="">-- เลือกโอกาส --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>ที่มาของงบประมาณ <span class="text-danger">*</span></label>
                                    <select name="Source_budget_id" id="ef_Source_budget_id" class="form-control" required>
                                        <option value="">-- เลือกที่มา --</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>ปีงบประมาณ <span class="text-danger">*</span></label>
                                    <select name="fiscalyear" id="ef_fiscalyear" class="form-control" required></select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>วันที่เริ่มติดต่อ <span class="text-danger">*</span></label>
                                    <input type="text" name="contact_start_date" id="ef_contact_start_date" class="form-control" placeholder="dd/mm/yyyy" readonly required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>วันยื่น Bidding</label>
                                    <input type="text" name="date_of_closing_of_sale" id="ef_date_of_closing_of_sale" class="form-control" placeholder="dd/mm/yyyy" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>วันเซ็นสัญญา</label>
                                    <input type="text" name="sales_can_be_close" id="ef_sales_can_be_close" class="form-control" placeholder="dd/mm/yyyy" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>ขั้นตอนการขาย</label>
                            <div class="row" id="ef_steps_container"></div>
                        </div>

                        <div class="form-group">
                            <label>หมายเหตุ</label>
                            <textarea name="remark" id="ef_remark" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="card mt-2">
                            <div class="card-header bg-light py-2">
                                <strong><i class="fas fa-user-tie"></i> ข้อมูลลูกค้า (ไม่บังคับ)</strong>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>ชื่อผู้ติดต่อ</label>
                                            <input type="text" name="contact_person" id="ef_contact_person" class="form-control" placeholder="ชื่อ-นามสกุล">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>เบอร์โทรศัพท์</label>
                                            <input type="text" name="contact_phone" id="ef_contact_phone" class="form-control" placeholder="0xx-xxx-xxxx">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>อีเมล</label>
                                            <input type="email" name="contact_email" id="ef_contact_email" class="form-control" placeholder="email@example.com">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <label>อื่นๆ</label>
                                    <textarea name="contact_note" id="ef_contact_note" rows="2" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary" id="editFormSaveBtn">
                            <span id="editFormSaveTxt"><i class="fas fa-save"></i> บันทึก</span>
                            <span id="editFormSaveSpinner" class="spinner-border spinner-border-sm ml-1" style="display:none;"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
    const table = $("#salesTable").DataTable({
        "processing": true,
        "serverSide": true,
        "responsive": false,
        "lengthChange": true,
        "autoWidth": false,
        "language": { "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json" },
        "dom": 'lBfrtip',
        "ajax": {
            "url": "{{ route('user.dashboard.table.data') }}",
            "data": function(d) {
                d.year    = $('select[name="year"]').val()    || '{{ request("year") }}';
                d.quarter = $('select[name="quarter"]').val() || '{{ request("quarter") }}';
            }
        },
        "columns": [
            { data: 'project' },
            { data: 'company' },
            { data: 'value',    render: function(v){ return Number(v || 0).toLocaleString('th-TH'); } },
            { data: 'status' },
            { data: 'priority' },
            { data: 'year' },
            { data: 'start',    render: function(v){ return formatThaiDate(v); } },
            { data: 'bidding',  render: function(v){ return formatThaiDate(v); } },
            { data: 'contract', render: function(v){ return formatThaiDate(v); } },
            { data: 'product' },
            { data: 'team' },
            { data: 'remark' },
            { data: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        "buttons": [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Export to Excel',
                className: 'btn btn-success',
                titleAttr: 'Export to Excel',
                bom: true,
                exportOptions: { columns: ':not(:last-child)' }
            },
            {
                extend: 'colvis',
                text: 'เลือกคอลัมน์',
                className: 'btn btn-info'
            }
        ],
        "order": [[6, 'desc']]
    });

    // Row click → View Detail Modal
    $('#salesTable tbody').on('click', 'tr', function(e) {
        if ($(e.target).closest('td:last-child').length) return;

        const row = table.row(this).data();
        if (!row) return;

        $('#modal-project').text(row.project || '-');
        $('#modal-company').text(row.company || '-');
        $('#modal-value').text(Number(row.value || 0).toLocaleString('th-TH'));
        $('#modal-status').text(row.status || '-');
        $('#modal-priority').text(row.priority || '-');
        $('#modal-year').text(row.year || '-');
        $('#modal-start').text(formatThaiDate(row.start));
        $('#modal-bidding').text(formatThaiDate(row.bidding));
        $('#modal-contract').text(formatThaiDate(row.contract));
        $('#modal-product').text(row.product || '-');
        $('#modal-team').text(row.team || '-');
        $('#modal-source').text(row.source || '-');
        $('#modal-contact-person').text(row.contact_person || '-');
        $('#modal-contact-phone').text(row.contact_phone || '-');
        $('#modal-contact-email').text(row.contact_email || '-');
        $('#modal-remark').text(row.remark || '-');
        $('#modal-edit-btn').data('id', row.id);

        $('#viewDetailModal').modal('show');
    });

    // Click แก้ไข in View Detail Modal → open Edit Modal
    $('#modal-edit-btn').on('click', function() {
        const id = $(this).data('id');
        if (!id) return;
        $('#viewDetailModal').modal('hide');
        openEditModal(id);
    });

    // ---- Edit Modal Logic ----
    let efFlatpickrs = {};
    const fpLocale = {
        firstDayOfWeek: 1,
        weekdays: {
            shorthand: ['อา','จ','อ','พ','พฤ','ศ','ส'],
            longhand:  ['อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัสบดี','ศุกร์','เสาร์']
        },
        months: {
            shorthand: ['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'],
            longhand:  ['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม']
        }
    };

    function initEfFlatpickr(selector, defaultDate) {
        if (efFlatpickrs[selector]) { efFlatpickrs[selector].destroy(); }
        efFlatpickrs[selector] = flatpickr(selector, {
            dateFormat: 'Y-m-d',
            defaultDate: defaultDate || null,
            locale: fpLocale,
            onChange: function(sel, str, instance) {
                if (sel.length > 0) {
                    const d = sel[0];
                    const day   = String(d.getDate()).padStart(2,'0');
                    const month = String(d.getMonth()+1).padStart(2,'0');
                    instance.input.setAttribute('data-iso', str);
                    instance.input.value = day+'/'+month+'/'+(d.getFullYear()+543);
                }
            },
            onReady: function(sel, str, instance) {
                if (sel.length > 0) {
                    const d = sel[0];
                    const day   = String(d.getDate()).padStart(2,'0');
                    const month = String(d.getMonth()+1).padStart(2,'0');
                    instance.input.setAttribute('data-iso', str);
                    instance.input.value = day+'/'+month+'/'+(d.getFullYear()+543);
                }
            }
        });
    }

    function populateSelect(selectId, items, valueKey, labelKey, selectedValue) {
        const sel = document.getElementById(selectId);
        const firstOpt = sel.options[0].outerHTML;
        sel.innerHTML = firstOpt;
        items.forEach(function(item) {
            const opt = document.createElement('option');
            opt.value = item[valueKey];
            opt.text  = item[labelKey];
            if (String(item[valueKey]) === String(selectedValue)) opt.selected = true;
            sel.appendChild(opt);
        });
    }

    function openEditModal(id) {
        $('#editFormAlert').hide();
        $('#editForm')[0].reset();
        $('#ef_steps_container').html('<div class="col-12 text-center py-2"><i class="fas fa-spinner fa-spin"></i> กำลังโหลด...</div>');
        $('#editModal').modal('show');
        $('#editForm').data('id', id);

        $.getJSON('/user/sales/' + id + '/edit-data', function(data) {
            const t = data.transaction;

            // Text fields
            $('#ef_Product_detail').val(t.Product_detail || '');
            $('#ef_product_value').val(Number(t.product_value || 0).toLocaleString('en-US'));
            $('#ef_remark').val(t.remark || '');
            $('#ef_contact_person').val(t.contact_person || '');
            $('#ef_contact_phone').val(t.contact_phone || '');
            $('#ef_contact_email').val(t.contact_email || '');
            $('#ef_contact_note').val(t.contact_note || '');

            // Dropdowns
            populateSelect('ef_company_id',       data.companies,  'company_id',       'company',       t.company_id);
            populateSelect('ef_Product_id',        data.products,   'product_id',       'product',       t.Product_id);
            populateSelect('ef_team_id',           data.teams,      'team_id',          'team',          t.team_id);
            populateSelect('ef_priority_id',       data.priorities, 'priority_id',      'priority',      t.priority_id);
            populateSelect('ef_Source_budget_id',  data.sources,    'Source_budget_id', 'Source_budge',  t.Source_budget_id);

            // Year dropdown
            const yearSel = document.getElementById('ef_fiscalyear');
            yearSel.innerHTML = '';
            const curYear = new Date().getFullYear();
            for (let y = curYear - 2; y <= curYear + 5; y++) {
                const opt = document.createElement('option');
                opt.value = y; opt.text = y + 543;
                if (y === parseInt(t.fiscalyear)) opt.selected = true;
                yearSel.appendChild(opt);
            }

            // Date pickers
            initEfFlatpickr('#ef_contact_start_date',      t.contact_start_date || null);
            initEfFlatpickr('#ef_date_of_closing_of_sale', t.date_of_closing_of_sale || null);
            initEfFlatpickr('#ef_sales_can_be_close',      t.sales_can_be_close || null);

            // Steps
            let stepsHtml = '';
            data.steps.forEach(function(step) {
                const ts    = data.transactionSteps[step.level_id];
                const chk   = ts ? 'checked' : '';
                const dis   = ts ? '' : 'disabled';
                const dateV = ts ? (ts.date || '') : '';
                stepsHtml += `
                <div class="col-md-3 mb-2">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input ef-step-chk" id="ef_step_${step.level_id}" name="step[${step.level_id}]" value="1" ${chk}>
                        <label class="custom-control-label" for="ef_step_${step.level_id}">${step.level}</label>
                    </div>
                    <input type="text" name="step_date[${step.level_id}]" class="form-control form-control-sm mt-1 ef-step-date" id="ef_step_date_${step.level_id}" data-iso="${dateV}" ${dis} readonly>
                </div>`;
            });
            $('#ef_steps_container').html(stepsHtml);

            // Init flatpickr for each step date
            data.steps.forEach(function(step) {
                const ts = data.transactionSteps[step.level_id];
                initEfFlatpickr('#ef_step_date_' + step.level_id, ts ? ts.date : null);
            });

            // Step checkbox toggle
            $('#ef_steps_container').off('change', '.ef-step-chk').on('change', '.ef-step-chk', function() {
                const lid = $(this).attr('id').replace('ef_step_', '');
                const dateInp = $('#ef_step_date_' + lid);
                if ($(this).is(':checked')) {
                    dateInp.prop('disabled', false);
                } else {
                    dateInp.prop('disabled', true).val('');
                    if (efFlatpickrs['#ef_step_date_' + lid]) {
                        efFlatpickrs['#ef_step_date_' + lid].clear();
                    }
                }
            });
        }).fail(function() {
            $('#editFormAlert').text('ไม่สามารถโหลดข้อมูลได้').show();
        });
    }

    // product_value formatter
    $('#ef_product_value').on('input', function() {
        let v = this.value.replace(/[^0-9.]/g, '');
        if (v) {
            const parts = v.split('.');
            v = (+parts[0]).toLocaleString('en-US') + (parts[1] !== undefined ? '.' + parts[1].slice(0,2) : '');
        }
        this.value = v;
    });

    // Edit Form submit via AJAX
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        if (!id) return;

        $('#editFormAlert').hide();
        $('#editFormSaveBtn').prop('disabled', true);
        $('#editFormSaveTxt').text('กำลังบันทึก...');
        $('#editFormSaveSpinner').show();

        // Collect form data, convert dates to ISO
        const formData = {};
        $(this).serializeArray().forEach(function(field) {
            // For date fields, use data-iso attribute (ISO format) instead of display value
            const el = document.getElementsByName(field.name)[0];
            if (el && el.hasAttribute('data-iso') && el.getAttribute('data-iso')) {
                formData[field.name] = el.getAttribute('data-iso');
            } else {
                formData[field.name] = field.value;
            }
        });
        // Remove comma from product_value
        if (formData['product_value']) {
            formData['product_value'] = formData['product_value'].replace(/,/g, '');
        }

        $.ajax({
            url: '/user/sales/' + id + '/ajax',
            method: 'POST',
            data: Object.assign(formData, { _method: 'PUT', _token: '{{ csrf_token() }}' }),
            success: function(res) {
                if (res.success) {
                    $('#editModal').modal('hide');
                    table.ajax.reload(null, false); // false = stay on current page
                } else {
                    $('#editFormAlert').text(res.message || 'เกิดข้อผิดพลาด').show();
                }
            },
            error: function(xhr) {
                let msg = 'เกิดข้อผิดพลาด';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        msg = Object.values(xhr.responseJSON.errors).flat().join(' | ');
                    } else if (xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                }
                $('#editFormAlert').text(msg).show();
            },
            complete: function() {
                $('#editFormSaveBtn').prop('disabled', false);
                $('#editFormSaveTxt').html('<i class="fas fa-save"></i> บันทึก');
                $('#editFormSaveSpinner').hide();
            }
        });
    });

    function formatThaiDate(dateStr) {
        if (!dateStr || dateStr === '-') return '-';
        try {
            const date = new Date(dateStr);
            const day   = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year  = date.getFullYear() + 543;
            return `${day}/${month}/${year}`;
        } catch (e) {
            return dateStr;
        }
    }

    $('#tableFilterForm').on('submit', function () {
        $('#tableFilterBtn').prop('disabled', true);
        $('#tableFilterBtnText').text('กำลังกรอง...');
        $('#tableFilterBtnSpinner').show();
    });
});
</script>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
