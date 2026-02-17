@extends('adminlte::page')

@section('title', 'Sales Dashboard (Charts) | PrimeForecast')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Sales Dashboard (Charts)</h1>
        </div>
    </div>
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
                    <form method="GET" action="{{ route('user.dashboard') }}" id="filterForm">
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
                                <button type="submit" class="btn btn-success btn-sm btn-block">
                                    <i class="fas fa-search"></i> กรอง
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('user.dashboard') }}" class="btn btn-secondary btn-sm btn-block">
                                    <i class="fas fa-redo"></i> รีเซ็ต
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card card-success">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title">สถานะโครงการในแต่ละขั้นตอน</h3>
                    <button class="btn btn-tool btn-fullscreen ms-auto float-end" style="margin-left:auto;" title="ขยายเต็มจอ" type="button"><i class="fas fa-expand"></i></button>
                </div>
                <div class="card-body"><canvas id="stepChart"></canvas></div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-success">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title">กราฟเปรียบเทียบ Target/Forecast/Win</h3>
                    <button class="btn btn-tool btn-fullscreen ms-auto float-end" style="margin-left:auto;" title="ขยายเต็มจอ" type="button"><i class="fas fa-expand"></i></button>
                </div>
                <div class="card-body"><canvas id="winForecastChart"></canvas></div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card card-success">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title">กราฟเปรียบเทียบสัดส่วนของกลุ่มสินค้า</h3>
                    <button class="btn btn-tool btn-fullscreen ms-auto float-end" style="margin-left:auto;" title="ขยายเต็มจอ" type="button"><i class="fas fa-expand"></i></button>
                </div>
                <div class="card-body"><canvas id="sumValuePercentChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas></div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function() {
    const saleStepData = @json($saleStepData);
    const winForecastData = @json($winForecastData);
    const sumValuePercentData = @json($sumValuePercentData);

    function formatMonthYearBe(rawMonth) {
        if (!rawMonth) return '-';
        const parts = String(rawMonth).split('-');
        if (parts.length < 2) return rawMonth;

        const year = Number(parts[0]);
        const month = Number(parts[1]);
        if (!Number.isFinite(year) || !Number.isFinite(month) || month < 1 || month > 12) {
            return rawMonth;
        }

        return String(month).padStart(2, '0') + '-' + String(year + 543);
    }

    // Step Chart
    function drawStepChart(rows) {
        const chartNode = document.getElementById('stepChart');
        if (!chartNode || !Array.isArray(rows) || rows.length === 0) {
            if (chartNode) chartNode.parentNode.innerHTML = '<p class="text-center text-muted">ไม่มีข้อมูลแสดงกราฟขั้นตอน</p>';
            return;
        }

        const labels = rows.map(r => formatMonthYearBe(r.month));
        const datasets = [
            { label: 'Present', data: rows.map(r => +r.present_value), backgroundColor: 'rgba(128, 81, 255, 1)' },
            { label: 'Budget', data: rows.map(r => +r.budgeted_value), backgroundColor: 'rgba(255, 0, 144, 1)' },
            { label: 'TOR', data: rows.map(r => +r.tor_value), backgroundColor: 'rgba(230, 180, 40, 1)' },
            { label: 'Bidding', data: rows.map(r => +r.bidding_value), backgroundColor: 'rgba(230, 120, 40, 1)' },
            { label: 'Win', data: rows.map(r => +r.win_value), backgroundColor: 'rgba(34, 139, 34, 1)' },
            { label: 'Lost', data: rows.map(r => +r.lost_value), backgroundColor: 'rgba(178, 34, 34, 1)' }
        ];

        new Chart(chartNode.getContext('2d'), {
            type: 'bar',
            data: { labels, datasets },
            options: {
                responsive: true,
                plugins: {
                    title: { display: true, text: 'สถานะโครงการในแต่ละเดือน' },
                    legend: { position: 'top' }
                },
                scales: {
                    x: { stacked: false, title: { display: true, text: 'เดือน' } },
                    y: { stacked: false, beginAtZero: true, title: { display: true, text: 'มูลค่าโครงการ' } }
                }
            }
        });
    }

    // Win Forecast Chart
    function drawWinForecastChart(data) {
        const chartNode = document.getElementById('winForecastChart');
        if (!chartNode || !data) {
            if (chartNode) chartNode.parentNode.innerHTML = '<p class="text-center text-muted">ไม่มีข้อมูลแสดงกราฟ Forecast</p>';
            return;
        }

        const labels = ['Target', 'Forecast', 'Win'];
        const values = [+data.Target || 0, +data.Forecast || 0, +data.Win || 0];
        const colors = ['rgba(153,102,255,0.7)', 'rgba(54,162,235,0.7)', 'rgba(34, 139, 34, 0.7)'];

        new Chart(chartNode.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'หน่วย: จำนวนเงิน',
                    data: values,
                    backgroundColor: colors
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, title: { display: true, text: 'จำนวนเงิน' } } }
            }
        });
    }

    // Sum Value Percent Chart
    function drawSumValuePercentChart(rows) {
        const chartNode = document.getElementById('sumValuePercentChart');
        if (!chartNode || !Array.isArray(rows) || rows.length === 0) {
            if (chartNode) chartNode.parentNode.innerHTML = '<p class="text-center text-muted">ไม่มีข้อมูลแสดงกราฟสัดส่วน</p>';
            return;
        }

        const labels = rows.map(r => r.product);
        const values = rows.map(r => +r.sum_value);
        const total = values.reduce((acc, v) => acc + v, 0);
        
        const palette = ['rgba(54,162,235,0.8)', 'rgba(255,99,132,0.8)', 'rgba(255,206,86,0.8)', 'rgba(75,192,192,0.8)', 'rgba(153,102,255,0.8)', 'rgba(255,159,64,0.8)', 'rgba(0,128,128,0.8)'];
        const backgroundColors = values.map((_, i) => palette[i % palette.length]);
        const borderColors = backgroundColors.map(color => color.replace('0.8', '1'));

        new Chart(chartNode.getContext('2d'), {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{ 
                    data: values, 
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: { 
                        display: true, 
                        text: 'สัดส่วนกลุ่มสินค้า',
                        font: { size: 18, weight: 'bold' },
                        padding: { top: 10, bottom: 20 }
                    },
                    legend: { 
                        position: 'right',
                        labels: { boxWidth: 20, font: { size: 12 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const percent = total > 0 ? (value / total * 100).toFixed(2) : 0;
                                return `${label}: ${value.toLocaleString('th-TH')} (${percent}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // Initialize charts
    drawStepChart(saleStepData);
    drawWinForecastChart(winForecastData);
    drawSumValuePercentChart(sumValuePercentData);
})();
</script>
@stop

@section('css')
<style>
    .content-wrapper {
        background-color: #b3d6e4;
    }
</style>
@stop
