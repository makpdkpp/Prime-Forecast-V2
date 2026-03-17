@extends('adminlte::page')

@section('title', 'Windate Report | PrimeForecast')

@section('content_header')
    <h1>รายงาน Windate</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">ฟลเตอรขอมล</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form id="reportFilterForm">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="user_id">ชอผใช</label>
                                <select name="user_id" id="user_id" class="form-control">
                                    <option value="">ทกคน</option>
                                    @foreach($availableUsers as $user)
                                        <option value="{{ $user->user_id }}">
                                            {{ trim(($user->nname ?? '') . ' ' . ($user->surename ?? '')) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="date_from">วนทเรมตน</label>
                                <input type="text" name="date_from" id="date_from" class="form-control flatpickr" placeholder="dd/mm/yyyy">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="date_to">วนทสนสด</label>
                                <input type="text" name="date_to" id="date_to" class="form-control flatpickr" placeholder="dd/mm/yyyy">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label><br>
                                <button type="button" id="filterBtn" class="btn btn-primary">
                                    <i class="fas fa-search"></i> กรองขอมล
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">ผลลพธรายงาน</h3>
                <div class="card-tools">
                    <button type="button" id="exportExcel" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="reportTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ชอโครงการ</th>
                                <th>หนวยงาน/บรษท</th>
                                <th>มลคา ()</th>
                                <th>Windate</th>
                                <th>ชอผรบผดชอบ</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script>
$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
    flatpickr('.flatpickr', {
        dateFormat: 'd/m/Y',
        locale: {
            firstDayOfWeek: 1,
            weekdays: { shorthand: ['อา','จ','อ','พ','พฤ','ศ','ส'], longhand: ['อาทตย','จนทร','องคาร','พธ','พฤหสบด','ศกร','เสาร'] },
            months: { shorthand: ['ม.ค.','ก.พ.','ม.ค.','เม.ย.','พ.ค.','ม.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'], longhand: ['มกราคม','กมภาพนธ','มนาคม','เมษายน','พฤษภาคม','มถนายน','กรกฎาคม','สงหาคม','กนยายน','ตลาคม','พฤศจกายน','ธนวาคม'] }
        },
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length > 0) {
                const d = selectedDates[0];
                const day = String(d.getDate()).padStart(2,'0');
                const month = String(d.getMonth()+1).padStart(2,'0');
                instance.input.value = day+'/'+month+'/'+( d.getFullYear()+543);
                $(instance.input).data('isoValue', d.getFullYear()+'-'+month+'-'+day);
            }
        }
    });
    const table = $('#reportTable').DataTable({
        processing: true, serverSide: false, responsive: false, autoWidth: false,
        language: { processing:'กำลงดำเนนการ...', search:'คนหา:', lengthMenu:'แสดง _MENU_ รายการ', info:'แสดง _START_ ถง _END_ จาก _TOTAL_ รายการ', infoEmpty:'แสดง 0 ถง 0 จาก 0 รายการ', infoFiltered:'(กรองจาก _MAX_ รายการทงหมด)', zeroRecords:'ไมพบขอมล', emptyTable:'ไมมขอมลในตาราง', paginate:{first:'หนาแรก',previous:'กอนหนา',next:'ถดไป',last:'หนาสดทาย'} },
        data: [],
        columns: [ {data:'project_name'},{data:'company_name'},{data:'value'},{data:'win_date'},{data:'user_name'} ]
    });
    function loadData() {
        $.post('{{ route("teamadmin.reports.windate.data") }}', {
            user_id: $('#user_id').val(),
            date_from: $('input[name="date_from"]').data('isoValue') || '',
            date_to:   $('input[name="date_to"]').data('isoValue') || ''
        }, function(r){ table.clear().rows.add(r.data).draw(); });
    }
    $('#filterBtn').on('click', loadData);
    $('#exportExcel').on('click', function() {
        const params = {export_type:'excel'};
        const uv = $('#user_id').val(); if(uv) params.user_id=uv;
        const df = $('input[name="date_from"]').data('isoValue'); if(df) params.date_from=df;
        const dt = $('input[name="date_to"]').data('isoValue');   if(dt) params.date_to=dt;
        const form = $('<form>',{method:'POST',action:'{{ route("teamadmin.reports.windate.data") }}'});
        form.append($('<input>',{type:'hidden',name:'_token',value:'{{ csrf_token() }}'}));
        Object.keys(params).forEach(k=>form.append($('<input>',{type:'hidden',name:k,value:params[k]})));
        $('body').append(form); form.submit(); form.remove();
    });
});
</script>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
@stop
