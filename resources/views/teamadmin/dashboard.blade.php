@extends('adminlte::page')

@section('title', 'Admin Dashboard | PrimeForecast')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
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
                    <form method="GET" action="{{ route('teamadmin.dashboard') }}" id="filterForm">
                        <div class="row align-items-end">
                            <div class="col-md-2">
                                <label class="mb-1"><small>ปีงบประมาณ (พ.ศ.):</small></label>
                                <select name="year" class="form-control form-control-sm">
                                    <option value="">ทุกปี</option>
                                    @foreach($availableYears as $y)
                                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y + 543 }}</option>
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
                                <a href="{{ route('teamadmin.dashboard') }}" class="btn btn-secondary btn-sm btn-block">
                                    <i class="fas fa-redo"></i> รีเซ็ต
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Boxes -->
    <div class="row mb-3">
        <div class="col-lg-3 col-md-6">
            <div class="summary-box">
                <h4>มูลค่า Forecast ทั้งหมด</h4>
                <p>{{ number_format($estimateValue) }} บาท</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="summary-box">
                <h4>มูลค่าที่ WIN ทั้งหมด</h4>
                <p>{{ number_format($winValue) }} บาท</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="summary-box">
                <h4>จำนวนโครงการที่ WIN</h4>
                <p>{{ number_format($winCount) }} โครงการ</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="summary-box">
                <h4>จำนวนโครงการที่ LOST</h4>
                <p>{{ number_format($lostCount) }} โครงการ</p>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row">
        <div class="col-md-6">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">ยอดขายรวม(มูลค่า)</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="winstatusValueChart" height="180"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">ยอดขายรายทีม(บาท)</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="teamSumChart" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row">
        <div class="col-md-6">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">ยอดขายรายคน(บาท)</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="personSumChart" height="180"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">สถานะการขายในแต่ละขั้นตอน</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="salestatusChart" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 3 -->
    <div class="row">
        <div class="col-md-6">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">ประมาณการมูลค่าในแต่ละขั้นตอนการขาย</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="statusValueChart" height="180"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">TOP 10 ประเภทโซลูชั่น</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="topProductsChart" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 4 -->
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">ยอดขาย Top 10 ของลูกค้า</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="topCustomerChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function() {
    const cumulativeWinData = @json($cumulativeWin);
    const sumByTeamData = @json($sumByTeam);
    const sumByPersonData = @json($sumByPerson);
    const saleStatusData = @json($saleStatus);
    const saleStatusValueData = @json($saleStatusValue);
    const topProductsData = @json($topProducts);
    const topCustomersData = @json($topCustomers);

    // Win Status Value Chart (Cumulative Win by Month)
    function renderWinStatusValueChart(data) {
        const ctx = document.getElementById('winstatusValueChart');
        if (!ctx) return;
        
        const labels = data.map(r => r.sale_month);
        const values = data.map(r => Number(r.cumulative_win_value));
        
        new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'ยอด Win สะสม',
                    data: values,
                    backgroundColor: 'rgba(40, 167, 69, 0.8)'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('th-TH');
                            }
                        }
                    }
                }
            }
        });
    }

    // Team Sum Chart
    function renderTeamSumChart(data) {
        const ctx = document.getElementById('teamSumChart');
        if (!ctx) return;
        
        const labels = data.map(r => r.team);
        const values = data.map(r => Number(r.total_value));
        
        new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'ยอดขาย (บาท)',
                    data: values,
                    backgroundColor: 'rgba(54, 162, 235, 0.8)'
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('th-TH');
                            }
                        }
                    }
                }
            }
        });
    }

    // Person Sum Chart
    function renderPersonSumChart(data) {
        const ctx = document.getElementById('personSumChart');
        if (!ctx) return;
        
        const labels = data.map(r => r.nname + ' ' + (r.surename || ''));
        const values = data.map(r => Number(r.total_value));
        
        new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'ยอดขาย (บาท)',
                    data: values,
                    backgroundColor: 'rgba(75, 192, 192, 0.8)'
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('th-TH');
                            }
                        }
                    }
                }
            }
        });
    }

    // Sale Status Chart
    function renderSaleStatusChart(data) {
        const ctx = document.getElementById('salestatusChart');
        if (!ctx) return;
        
        const labels = data.map(r => r.level);
        const values = data.map(r => Number(r.count));
        
        new Chart(ctx.getContext('2d'), {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    }

    // Status Value Chart
    function renderStatusValueChart(data) {
        const ctx = document.getElementById('statusValueChart');
        if (!ctx) return;
        
        const labels = data.map(r => r.level);
        const values = data.map(r => Number(r.total_value));
        
        new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'มูลค่า (บาท)',
                    data: values,
                    backgroundColor: 'rgba(153, 102, 255, 0.8)'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('th-TH');
                            }
                        }
                    }
                }
            }
        });
    }

    // Top Products Chart
    function renderTopProductsChart(data) {
        const ctx = document.getElementById('topProductsChart');
        if (!ctx) return;
        
        const labels = data.map(r => r.product);
        const values = data.map(r => Number(r.total_value));
        
        new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'มูลค่า (บาท)',
                    data: values,
                    backgroundColor: 'rgba(255, 159, 64, 0.8)'
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('th-TH');
                            }
                        }
                    }
                }
            }
        });
    }

    // Top Customer Chart
    function renderTopCustomerChart(data) {
        const ctx = document.getElementById('topCustomerChart');
        if (!ctx) return;
        
        const labels = data.map(r => r.company);
        const values = data.map(r => Number(r.total_value));
        
        new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'มูลค่า (บาท)',
                    data: values,
                    backgroundColor: 'rgba(54, 162, 235, 0.8)'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('th-TH');
                            }
                        }
                    }
                }
            }
        });
    }

    // Initialize all charts
    renderWinStatusValueChart(cumulativeWinData);
    renderTeamSumChart(sumByTeamData);
    renderPersonSumChart(sumByPersonData);
    renderSaleStatusChart(saleStatusData);
    renderStatusValueChart(saleStatusValueData);
    renderTopProductsChart(topProductsData);
    renderTopCustomerChart(topCustomersData);
})();
</script>
@stop

@section('css')
<style>
    .content-wrapper {
        background-color: #b3d6e4;
    }
    .summary-box {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        text-align: left;
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        border-left: 5px solid #007bff;
        margin-bottom: 20px;
    }
    .summary-box h4 {
        font-size: 16px;
        margin-bottom: 10px;
        color: #555;
        font-weight: bold;
    }
    .summary-box p {
        font-size: 26px;
        font-weight: bold;
        margin: 0;
        color: #333;
    }
</style>
@stop
