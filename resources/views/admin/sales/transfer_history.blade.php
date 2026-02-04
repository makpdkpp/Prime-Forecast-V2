@extends('adminlte::page')

@section('title', 'ประวัติการโอนข้อมูล | PrimeForecast')

@section('content_header')
    <h1>ประวัติการโอนข้อมูลการขาย</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">ข้อมูลโครงการ</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th width="30%">ชื่อโครงการ</th>
                            <td>{{ $transaction->Product_detail }}</td>
                        </tr>
                        <tr>
                            <th>บริษัท</th>
                            <td>{{ $transaction->company->company ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th width="30%">มูลค่าโครงการ</th>
                            <td>{{ number_format($transaction->product_value, 2) }} บาท</td>
                        </tr>
                        <tr>
                            <th>เจ้าของปัจจุบัน</th>
                            <td>{{ $transaction->user->nname }} {{ $transaction->user->surename }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-history"></i> ประวัติการโอน</h3>
        </div>
        <div class="card-body">
            @if($transferHistory->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="15%">วันที่โอน</th>
                                <th width="20%">จากผู้ใช้งาน</th>
                                <th width="20%">ไปยังผู้ใช้งาน</th>
                                <th width="15%">ทีมเดิม → ทีมใหม่</th>
                                <th width="15%">ผู้ทำการโอน</th>
                                <th>เหตุผล</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transferHistory as $history)
                                <tr>
                                    <td>
                                        <small>
                                            {{ $history->transferred_at->format('d/m/Y') }}<br>
                                            <span class="text-muted">{{ $history->transferred_at->format('H:i') }} น.</span>
                                        </small>
                                    </td>
                                    <td>
                                        <i class="fas fa-user text-danger"></i>
                                        {{ $history->fromUser->nname ?? '-' }} {{ $history->fromUser->surename ?? '' }}
                                    </td>
                                    <td>
                                        <i class="fas fa-user text-success"></i>
                                        {{ $history->toUser->nname ?? '-' }} {{ $history->toUser->surename ?? '' }}
                                    </td>
                                    <td>
                                        <small>
                                            @if($history->old_team_id != $history->new_team_id)
                                                <span class="badge badge-secondary">{{ $history->oldTeam->team ?? '-' }}</span>
                                                <i class="fas fa-arrow-right"></i>
                                                <span class="badge badge-primary">{{ $history->newTeam->team ?? '-' }}</span>
                                            @else
                                                <span class="badge badge-info">{{ $history->oldTeam->team ?? '-' }}</span>
                                            @endif
                                        </small>
                                    </td>
                                    <td>
                                        <small>
                                            <i class="fas fa-user-shield text-primary"></i>
                                            {{ $history->transferredByUser->nname ?? '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($history->transfer_reason)
                                            <small>{{ $history->transfer_reason }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    ไม่มีประวัติการโอนข้อมูลสำหรับโครงการนี้
                </div>
            @endif
        </div>
        <div class="card-footer">
            <a href="{{ route('admin.dashboard.table') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> กลับ
            </a>
            <a href="{{ route('admin.sales.edit', $transaction->transac_id) }}" class="btn btn-info">
                <i class="fas fa-pencil-alt"></i> แก้ไขข้อมูล
            </a>
            <a href="{{ route('admin.sales.transfer', $transaction->transac_id) }}" class="btn btn-warning">
                <i class="fas fa-exchange-alt"></i> โอนข้อมูล
            </a>
        </div>
    </div>
@stop

@section('css')
<style>
    .table-responsive {
        overflow-x: auto;
    }
    .badge {
        font-size: 0.85em;
    }
</style>
@stop
