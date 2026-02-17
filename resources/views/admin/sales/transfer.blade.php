@extends('adminlte::page')

@section('title', 'โอนข้อมูลการขาย | PrimeForecast')

@section('content_header')
    <h1>โอนข้อมูลการขาย</h1>
@stop

@section('content')
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ข้อมูลโครงการ</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">ชื่อโครงการ</th>
                            <td>{{ $transaction->Product_detail }}</td>
                        </tr>
                        <tr>
                            <th>บริษัท</th>
                            <td>{{ $transaction->company->company ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>มูลค่าโครงการ</th>
                            <td>{{ number_format($transaction->product_value, 2) }} บาท</td>
                        </tr>
                        <tr>
                            <th>ปีงบประมาณ</th>
                            <td>{{ $transaction->fiscalyear + 543 }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">เจ้าของปัจจุบัน</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">ชื่อผู้ใช้งาน</th>
                            <td>{{ $transaction->user->nname }} {{ $transaction->user->surename }}</td>
                        </tr>
                        <tr>
                            <th>ทีมขาย</th>
                            <td>{{ $transaction->team->team ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-exchange-alt"></i> โอนข้อมูลไปยัง</h3>
                </div>
                <form action="{{ route('admin.sales.transfer.process', $transaction->transac_id) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="to_user_id">เลือกผู้ใช้งานปลายทาง <span class="text-danger">*</span></label>
                            <select name="to_user_id" id="to_user_id" class="form-control @error('to_user_id') is-invalid @enderror" required>
                                <option value="">-- เลือกผู้ใช้งาน --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->user_id }}" data-team="{{ $user->team_id ?? '' }}" {{ old('to_user_id') == $user->user_id ? 'selected' : '' }}>
                                        {{ $user->nname }} {{ $user->surename }}
                                        @if($user->position)
                                            ({{ $user->position->position }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('to_user_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> เลือกผู้ใช้งานที่ต้องการโอนข้อมูลไป
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="new_team_display">ทีมใหม่</label>
                            <input type="text" id="new_team_display" class="form-control" readonly placeholder="เลือกผู้ใช้งานเพื่อดูทีม">
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> ทีมจะถูกอัพเดทตามทีมของผู้ใช้งานปลายทาง
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="transfer_reason">เหตุผลในการโอน</label>
                            <textarea name="transfer_reason" id="transfer_reason" class="form-control @error('transfer_reason') is-invalid @enderror" rows="4" placeholder="ระบุเหตุผลในการโอนข้อมูล (ถ้ามี)">{{ old('transfer_reason') }}</textarea>
                            @error('transfer_reason')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>คำเตือน:</strong> การโอนข้อมูลจะเปลี่ยนเจ้าของและทีมของโครงการนี้ และจะถูกบันทึกในประวัติการโอน
                        </div>
                    </div>

                    <div class="card-footer">
                        <a href="{{ route('admin.dashboard.table') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> ยกเลิก
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-exchange-alt"></i> ยืนยันการโอน
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(function() {
    const teams = @json($teams);
    
    $('#to_user_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const teamId = selectedOption.data('team');
        
        if (teamId) {
            const team = teams.find(t => t.team_id == teamId);
            $('#new_team_display').val(team ? team.team : '-');
        } else {
            $('#new_team_display').val('ไม่มีทีม');
        }
    });
});
</script>
@stop
