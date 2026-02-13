@extends('adminlte::page')

@section('title', 'แก้ไขข้อมูลการขาย | PrimeForecast')

@section('content_header')
    <h1>แก้ไขข้อมูลการขาย</h1>
@stop

@section('content')
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('user.sales.update', $transaction->transac_id) }}" method="POST" id="salesForm" autocomplete="off">
                        @csrf
                        @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Product_detail">ชื่อโครงการ <span class="text-danger">*</span></label>
                            <input type="text" name="Product_detail" id="Product_detail" class="form-control @error('Product_detail') is-invalid @enderror" value="{{ old('Product_detail', $transaction->Product_detail) }}" required>
                            @error('Product_detail')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="company_id">หน่วยงาน / บริษัท <span class="text-danger">*</span></label>

                            <div class="input-group">
                                    <select name="company_id" id="company_id" class="form-control @error('company_id') is-invalid @enderror" required>
                                        <option value="">-- เลือกบริษัท/หน่วยงาน --</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->company_id }}" {{ old('company_id', $transaction->company_id) == $company->company_id ? 'selected' : '' }}>
                                                {{ $company->company }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#requestCompanyModal">
                                            <i class="fas fa-plus-circle"></i> ขอเพิ่ม
                                        </button>
                                    </div>
                            </div>
                            @error('company_id')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Product_id">กลุ่มสินค้า <span class="text-danger">*</span></label>
                            <select name="Product_id" id="Product_id" class="form-control @error('Product_id') is-invalid @enderror" required>
                                <option value="">-- เลือกกลุ่มสินค้า --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->product_id }}" {{ old('Product_id', $transaction->Product_id) == $product->product_id ? 'selected' : '' }}>
                                        {{ $product->product }}
                                    </option>
                                @endforeach
                            </select>
                            @error('Product_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="product_value">มูลค่าโครงการ (บาท) <span class="text-danger">*</span></label>
                            <input type="text" name="product_value" id="product_value" class="form-control @error('product_value') is-invalid @enderror" value="{{ old('product_value', number_format($transaction->product_value)) }}" required>
                            @error('product_value')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="team_id">ทีมขาย <span class="text-danger">*</span></label>
                            <select name="team_id" id="team_id" class="form-control @error('team_id') is-invalid @enderror" required>
                                <option value="">-- เลือกทีม --</option>
                                @foreach($teams as $team)
                                    <option value="{{ $team->team_id }}" {{ old('team_id', $transaction->team_id) == $team->team_id ? 'selected' : '' }}>
                                        {{ $team->team }}
                                    </option>
                                @endforeach
                            </select>
                            @error('team_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="priority_id">โอกาสการชนะ</label>
                            <select name="priority_id" id="priority_id" class="form-control @error('priority_id') is-invalid @enderror">
                                <option value="">-- เลือกโอกาส --</option>
                                @foreach($priorities as $priority)
                                    <option value="{{ $priority->priority_id }}" {{ old('priority_id', $transaction->priority_id) == $priority->priority_id ? 'selected' : '' }}>
                                        {{ $priority->priority }}
                                    </option>
                                @endforeach
                            </select>
                            @error('priority_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Source_budget_id">ที่มาของงบประมาณ <span class="text-danger">*</span></label>
                            <select name="Source_budget_id" id="Source_budget_id" class="form-control @error('Source_budget_id') is-invalid @enderror" required>
                                <option value="">-- เลือกที่มา --</option>
                                @foreach($sources as $source)
                                    <option value="{{ $source->Source_budget_id }}" {{ old('Source_budget_id', $transaction->Source_budget_id) == $source->Source_budget_id ? 'selected' : '' }}>
                                        {{ $source->Source_budge }}
                                    </option>
                                @endforeach
                            </select>
                            @error('Source_budget_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fiscalyear">ปีงบประมาณ <span class="text-danger">*</span></label>
                            <select name="fiscalyear" id="fiscalyear" class="form-control @error('fiscalyear') is-invalid @enderror" required>
                                @for($year = date('Y') - 2; $year <= date('Y') + 5; $year++)
                                    <option value="{{ $year }}" {{ old('fiscalyear', $transaction->fiscalyear) == $year ? 'selected' : '' }}>{{ $year + 543 }}</option>
                                @endfor
                            </select>
                            @error('fiscalyear')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="contact_start_date">วันที่เริ่มติดต่อ <span class="text-danger">*</span></label>
                            <input type="text" name="contact_start_date" id="contact_start_date" class="form-control flatpickr-thai @error('contact_start_date') is-invalid @enderror" value="{{ old('contact_start_date', $transaction->contact_start_date) }}" required readonly>
                            @error('contact_start_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="date_of_closing_of_sale">วันยื่น Bidding</label>
                            <input type="text" name="date_of_closing_of_sale" id="date_of_closing_of_sale" class="form-control flatpickr-thai @error('date_of_closing_of_sale') is-invalid @enderror" value="{{ old('date_of_closing_of_sale', $transaction->date_of_closing_of_sale) }}" readonly>
                            @error('date_of_closing_of_sale')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="sales_can_be_close">วันเซ็นสัญญา</label>
                            <input type="text" name="sales_can_be_close" id="sales_can_be_close" class="form-control flatpickr-thai @error('sales_can_be_close') is-invalid @enderror" value="{{ old('sales_can_be_close', $transaction->sales_can_be_close) }}" readonly>
                            @error('sales_can_be_close')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                        <div class="form-group">
                            <label>ขั้นตอนการขาย</label>
                            <div class="row">
                                @foreach($steps as $step)
                                    <div class="col-md-3">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input step-checkbox" id="step_{{ $step->level_id }}" name="step[{{ $step->level_id }}]" value="1" {{ isset($transactionSteps[$step->level_id]) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="step_{{ $step->level_id }}">{{ $step->level }}</label>
                                        </div>
                                        <input type="text" name="step_date[{{ $step->level_id }}]" class="form-control form-control-sm mt-1 step-date flatpickr-step" id="step_date_{{ $step->level_id }}" value="{{ isset($transactionSteps[$step->level_id]) ? \Carbon\Carbon::parse($transactionSteps[$step->level_id]->date)->format('Y-m-d') : '' }}" {{ isset($transactionSteps[$step->level_id]) ? '' : 'disabled' }} readonly>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                <div class="form-group">
                    <label for="remark">หมายเหตุ</label>
                    <textarea name="remark" id="remark" class="form-control" rows="3">{{ old('remark', $transaction->remark) }}</textarea>
                </div>

                <div class="card mt-3">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0"><i class="fas fa-user-tie"></i> ข้อมูลลูกค้า (ไม่บังคับ)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="contact_person">ชื่อผู้ติดต่อ</label>
                                    <input type="text" name="contact_person" id="contact_person" class="form-control" value="{{ old('contact_person', $transaction->contact_person) }}" placeholder="ชื่อ-นามสกุล">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="contact_phone">เบอร์โทรศัพท์</label>
                                    <input type="text" name="contact_phone" id="contact_phone" class="form-control" value="{{ old('contact_phone', $transaction->contact_phone) }}" placeholder="0xx-xxx-xxxx">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="contact_email">อีเมล</label>
                                    <input type="email" name="contact_email" id="contact_email" class="form-control" value="{{ old('contact_email', $transaction->contact_email) }}" placeholder="email@example.com">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="contact_note">อื่นๆ</label>
                            <textarea name="contact_note" id="contact_note" rows="2" class="form-control" placeholder="รายละเอียดเพิ่มเติมเกี่ยวกับลูกค้า">{{ old('contact_note', $transaction->contact_note) }}</textarea>
                        </div>
                    </div>
                </div>

                        <div class="card mt-3">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0"><i class="fas fa-user-tie"></i> ข้อมูลลูกค้า (ไม่บังคับ)</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <label for="contact_person">ชื่อผู้ติดต่อ</label>
                                        <input type="text" name="contact_person" id="contact_person" class="form-control" value="{{ old('contact_person', $transaction->contact_person) }}" placeholder="ชื่อ-นามสกุล">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="contact_phone">เบอร์โทรศัพท์</label>
                                        <input type="text" name="contact_phone" id="contact_phone" class="form-control" value="{{ old('contact_phone', $transaction->contact_phone) }}" placeholder="0xx-xxx-xxxx">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="contact_email">อีเมล</label>
                                        <input type="email" name="contact_email" id="contact_email" class="form-control" value="{{ old('contact_email', $transaction->contact_email) }}" placeholder="email@example.com">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 form-group">
                                        <label for="contact_note">อื่นๆ</label>
                                        <textarea name="contact_note" id="contact_note" rows="2" class="form-control" placeholder="รายละเอียดเพิ่มเติมเกี่ยวกับลูกค้า">{{ old('contact_note', $transaction->contact_note) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-right mt-4">
                            <a href="{{ route('user.dashboard.table') }}" class="btn btn-secondary">ยกเลิก</a>
                            <button type="submit" class="btn btn-primary">บันทึก</button>
                        </div>
            </form>
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
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
(function() {
    const f = document.getElementById('product_value');
    const fmt = function(v) {
        v = v.replace(/[^0-9.]/g, '');
        if (!v) return '';
        const parts = v.split('.');
        return (+parts[0]).toLocaleString('en-US') + (parts[1] ? '.' + parts[1].slice(0, 2) : '');
    };
    f.addEventListener('input', function() {
        const p = f.selectionStart;
        const l = f.value.length;
        f.value = fmt(f.value);
        f.setSelectionRange(p + (f.value.length - l), p + (f.value.length - l));
    });
    document.getElementById('salesForm').addEventListener('submit', function() {
        f.value = f.value.replace(/,/g, '');
    });
})();

    // Flatpickr Thai Date Picker
    function initThaiDatePicker(selector, currentValue) {
        const input = document.querySelector(selector);
        if (!input) return;
        
        let defaultDate = null;
        if (currentValue && currentValue !== '') {
            const date = new Date(currentValue);
            if (!isNaN(date.getTime())) {
                defaultDate = date;
            }
        }
        
        flatpickr(selector, {
            dateFormat: 'd/m/Y',
            defaultDate: defaultDate,
            locale: {
                firstDayOfWeek: 1,
                weekdays: {
                    shorthand: ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'],
                    longhand: ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์']
                },
                months: {
                    shorthand: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'],
                    longhand: ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม']
                }
            },
            onReady: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    const date = selectedDates[0];
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const year = date.getFullYear() + 543;
                    instance.input.value = `${day}/${month}/${year}`;
                }
            },
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    const date = selectedDates[0];
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const year = date.getFullYear() + 543;
                    instance.input.value = `${day}/${month}/${year}`;
                }
            }
        });
    }
    
    // Initialize Thai date pickers
    initThaiDatePicker('#contact_start_date', '{{ $transaction->contact_start_date }}');
    initThaiDatePicker('#date_of_closing_of_sale', '{{ $transaction->date_of_closing_of_sale }}');
    initThaiDatePicker('#sales_can_be_close', '{{ $transaction->sales_can_be_close }}');
    
    // Convert Thai date back to YYYY-MM-DD before form submission
    $('#salesForm').on('submit', function() {
        $('.flatpickr-thai, .flatpickr-step').each(function() {
            const thaiDate = $(this).val();
            if (thaiDate && thaiDate.includes('/')) {
                const parts = thaiDate.split('/');
                if (parts.length === 3) {
                    const day = parts[0];
                    const month = parts[1];
                    const buddhistYear = parseInt(parts[2]);
                    const christianYear = buddhistYear - 543;
                    $(this).val(`${christianYear}-${month}-${day}`);
                }
            }
        });
    });

    // Initialize Flatpickr for step dates
    document.querySelectorAll('.flatpickr-step').forEach(function(input) {
        const currentValue = input.value;
        let defaultDate = null;
        if (currentValue && currentValue !== '') {
            const date = new Date(currentValue);
            if (!isNaN(date.getTime())) {
                defaultDate = date;
            }
        }
        
        flatpickr(input, {
            dateFormat: 'd/m/Y',
            defaultDate: defaultDate,
            locale: {
                firstDayOfWeek: 1,
                weekdays: {
                    shorthand: ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'],
                    longhand: ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์']
                },
                months: {
                    shorthand: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'],
                    longhand: ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม']
                }
            },
            onReady: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    const date = selectedDates[0];
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const year = date.getFullYear() + 543;
                    instance.input.value = `${day}/${month}/${year}`;
                }
            },
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    const date = selectedDates[0];
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const year = date.getFullYear() + 543;
                    instance.input.value = `${day}/${month}/${year}`;
                }
            }
        });
    });

    // Enable/disable step date based on checkbox
    $('.step-checkbox').on('change', function() {
        const stepId = $(this).attr('id').replace('step_', '');
        const dateInput = $('#step_date_' + stepId);
        if ($(this).is(':checked')) {
            dateInput.prop('disabled', false);
        } else {
            dateInput.prop('disabled', true).val('');
        }
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
                $('#requestStatus').html('<div class="alert alert-success">' + response.message + '</div>');
                setTimeout(function() {
                    $('#requestCompanyModal').modal('hide');
                    $('#companyRequestForm')[0].reset();
                    $('#requestStatus').html('');
                }, 2000);
            } else {
                $('#requestStatus').html('<div class="alert alert-danger">เกิดข้อผิดพลาด: ' + response.message + '</div>');
            }
        },
        error: function(xhr) {
            var errorMsg = 'เกิดข้อผิดพลาดในการเชื่อมต่อ';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            $('#requestStatus').html('<div class="alert alert-danger">' + errorMsg + '</div>');
        },
        complete: function() {
            $('button[type="submit"]', '#companyRequestForm').prop('disabled', false);
        }
    });
});
</script>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .process-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .process-item input[type="checkbox"] { margin: 0; }
    .process-item input[type="date"] { height: 32px; font-size: 14px; }
    .content-wrapper {
        background-color: #b3d6e4;
    }
</style>
@stop
