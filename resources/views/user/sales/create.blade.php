@extends('adminlte::page')

@section('title', 'เพิ่มรายละเอียดการขาย | PrimeForecast')

@section('content_header')
    <h1>เพิ่มรายละเอียดการขาย</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header text-center">
                    <h3 class="card-title">แบบฟอร์มเพิ่มรายละเอียดการขาย</h3>
                    <p class="lead mb-0">Sales: {{ Auth::user()->nname ?: 'N/A' }}</p>
                </div>
                <div class="card-body">
                    <form action="{{ route('user.sales.store') }}" method="POST" id="salesForm" autocomplete="off">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ Auth::id() }}">

                        <div class="row">
                            <div class="col-sm-12 form-group">
                                <label for="Product_detail">ชื่อโครงการ</label>
                                <input type="text" name="Product_detail" id="Product_detail" class="form-control" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="company_id">หน่วยงาน / บริษัท</label>
                                <div class="input-group">
                                    <select name="company_id" id="company_id" class="form-control" required>
                                        <option value="">-- เลือกบริษัท/หน่วยงาน --</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->company_id }}">{{ $company->company }}</option>
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#requestCompanyModal">
                                            <i class="fas fa-plus-circle"></i> ขอเพิ่ม
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="product_value">มูลค่า (บาท)</label>
                                <input type="text" name="product_value" id="product_value" class="form-control" placeholder="0" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="Source_budget_id">แหล่งที่มาของงบประมาณ</label>
                                <select name="Source_budget_id" id="Source_budget_id" class="form-control" required>
                                    <option value="">-- เลือกแหล่งที่มาของงบประมาณ --</option>
                                    @foreach($sources as $source)
                                        <option value="{{ $source->Source_budget_id }}">{{ $source->Source_budge }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="fiscalyear">ปีงบประมาณ</label>
                                <select name="fiscalyear" id="fiscalyear" class="form-control" required>
                                    <option value="">-- เลือกปีงบประมาณ --</option>
                                    @php
                                        $currentBuddhistYear = date('Y') + 543;
                                        for ($i = 0; $i < 5; $i++) {
                                            $year = $currentBuddhistYear + $i;
                                            echo "<option value=\"$year\">$year</option>";
                                        }
                                    @endphp
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label for="Product_id">กลุ่มสินค้า</label>
                                <select name="Product_id" id="Product_id" class="form-control" required>
                                    <option value="">-- เลือกสินค้า --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->product_id }}">{{ $product->product }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="team_id">ทีมขาย</label>
                                <select name="team_id" id="team_id" class="form-control" required>
                                    <option value="">-- เลือกทีม --</option>
                                    @foreach($teams as $team)
                                        <option value="{{ $team->team_id }}">{{ $team->team }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="priority_id">โอกาสชนะ</label>
                                <select name="priority_id" id="priority_id" class="form-control">
                                    <option value="">-- เลือกระดับ --</option>
                                    @foreach($priorities as $priority)
                                        <option value="{{ $priority->priority_id }}">{{ $priority->priority }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label for="contact_start_date">วันที่เริ่มโครงการ</label>
                                <input type="date" name="contact_start_date" id="contact_start_date" class="form-control" required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="date_of_closing_of_sale">วันที่คาดว่าจะ Bidding</label>
                                <input type="date" name="date_of_closing_of_sale" id="date_of_closing_of_sale" class="form-control">
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="sales_can_be_close">วันที่คาดจะเซ็นสัญญา</label>
                                <input type="date" name="sales_can_be_close" id="sales_can_be_close" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>สถานะ</label>
                            <div class="row">
                                @foreach($steps as $step)
                                    @php
                                        $key = strtolower($step->level);
                                        $field = $key . '_date';
                                    @endphp
                                    <div class="col-12 col-lg-6 mb-2">
                                        <div class="process-item d-flex align-items-center gap-2" style="background: #f8f9fa; padding: 8px 12px; border-radius: 6px; border: 1px solid #dee2e6;">
                                            <input type="hidden" name="step[{{ $step->level_id }}]" value="0">
                                            <div class="icheck-primary d-inline">
                                                <input type="checkbox" id="step_cb_{{ $step->level_id }}" name="step[{{ $step->level_id }}]" value="{{ $step->level_id }}" onchange="toggleDate('{{ $step->level_id }}')">
                                                <label for="step_cb_{{ $step->level_id }}" style="margin-bottom: 0; font-weight: normal !important;">{{ $step->level }}</label>
                                            </div>
                                            <input type="date" class="form-control form-control-sm ml-2" id="step_date_{{ $step->level_id }}" name="step_date[{{ $step->level_id }}]" style="width: auto;" disabled>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12 form-group">
                                <label for="remark">หมายเหตุ</label>
                                <textarea name="remark" id="remark" rows="3" class="form-control"></textarea>
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
                                        <input type="text" name="contact_person" id="contact_person" class="form-control" placeholder="ชื่อ-นามสกุล">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="contact_phone">เบอร์โทรศัพท์</label>
                                        <input type="text" name="contact_phone" id="contact_phone" class="form-control" placeholder="0xx-xxx-xxxx">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="contact_email">อีเมล</label>
                                        <input type="email" name="contact_email" id="contact_email" class="form-control" placeholder="email@example.com">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 form-group">
                                        <label for="contact_note">อื่นๆ</label>
                                        <textarea name="contact_note" id="contact_note" rows="2" class="form-control" placeholder="รายละเอียดเพิ่มเติมเกี่ยวกับลูกค้า"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-right mt-4">
                            <a href="{{ route('user.dashboard') }}" class="btn btn-secondary">กลับหน้าหลัก</a>
                            <button type="submit" class="btn btn-danger">บันทึกข้อมูล</button>
                        </div>
                    </form>
                </div>
            </div>
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
<script>
/* จัดรูปแบบตัวเลขใส่ comma */
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

function toggleDate(levelId) {
    const checkbox = document.getElementById('step_cb_' + levelId);
    const dateInput = document.getElementById('step_date_' + levelId);
    if (!dateInput) return;
    if (checkbox.checked) {
        dateInput.removeAttribute('disabled');
    } else {
        dateInput.setAttribute('disabled', 'disabled');
        dateInput.value = '';
    }
}

// Company Request Form AJAX
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
