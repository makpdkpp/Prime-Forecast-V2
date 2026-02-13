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
                    <form method="GET" action="{{ route('admin.dashboard') }}" id="filterForm">
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
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm btn-block">
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
                    <h3 class="card-title">กราฟเปรียบเทียบ Target/Forecast/Win</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="targetForecastWinChart" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 4 -->
    <div class="row">
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
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">ยอดขาย Top 10 ของลูกค้า</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="topCustomerChart" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal: Win Projects by Person -->
    <div class="modal fade" id="winProjectsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white"><i class="fas fa-trophy"></i> โปรเจคที่ Win - <span id="winProjectsUserName"></span></h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div id="winProjectsLoading" class="text-center py-4" style="display:none;">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">กำลังโหลดข้อมูล...</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="winProjectsTable" style="display:none;">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>โปรเจค</th>
                                    <th>ลูกค้า</th>
                                    <th>กลุ่มสินค้า</th>
                                    <th class="text-right">มูลค่า (บาท)</th>
                                    <th>วันที่ Win</th>
                                </tr>
                            </thead>
                            <tbody id="winProjectsBody"></tbody>
                            <tfoot>
                                <tr class="font-weight-bold bg-light">
                                    <td colspan="4" class="text-right">รวมทั้งหมด</td>
                                    <td class="text-right" id="winProjectsTotal"></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div id="winProjectsEmpty" class="text-center text-muted py-4" style="display:none;">
                        <i class="fas fa-inbox fa-2x"></i>
                        <p class="mt-2">ไม่พบข้อมูลโปรเจค</p>
                    </div>
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
    const targetForecastWinData = @json($targetForecastWin);
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
        
        const teamColors = [
            'rgba(54, 162, 235, 0.8)',
            'rgba(255, 99, 132, 0.8)',
            'rgba(75, 192, 192, 0.8)',
            'rgba(255, 206, 86, 0.8)',
            'rgba(153, 102, 255, 0.8)',
            'rgba(255, 159, 64, 0.8)',
            'rgba(40, 167, 69, 0.8)',
            'rgba(220, 53, 69, 0.8)',
            'rgba(23, 162, 184, 0.8)',
            'rgba(108, 117, 125, 0.8)'
        ];
        const teamBorders = [
            'rgba(54, 162, 235, 1)',
            'rgba(255, 99, 132, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
            'rgba(40, 167, 69, 1)',
            'rgba(220, 53, 69, 1)',
            'rgba(23, 162, 184, 1)',
            'rgba(108, 117, 125, 1)'
        ];
        
        const labels = data.map(r => r.team);
        const values = data.map(r => Number(r.total_value));
        const bgColors = data.map((_, i) => teamColors[i % teamColors.length]);
        const bdColors = data.map((_, i) => teamBorders[i % teamBorders.length]);
        
        new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'ยอดขาย Win (บาท)',
                    data: values,
                    backgroundColor: bgColors,
                    borderColor: bdColors,
                    borderWidth: 1
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
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + Number(context.raw).toLocaleString('th-TH') + ' บาท';
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
        
        const personColors = [
            'rgba(75, 192, 192, 0.8)',
            'rgba(54, 162, 235, 0.8)',
            'rgba(255, 99, 132, 0.8)',
            'rgba(255, 206, 86, 0.8)',
            'rgba(153, 102, 255, 0.8)',
            'rgba(255, 159, 64, 0.8)',
            'rgba(40, 167, 69, 0.8)',
            'rgba(220, 53, 69, 0.8)',
            'rgba(23, 162, 184, 0.8)',
            'rgba(108, 117, 125, 0.8)'
        ];
        
        const labels = data.map(r => r.nname + ' ' + (r.surename || ''));
        const values = data.map(r => Number(r.total_value));
        const bgColors = data.map((_, i) => personColors[i % personColors.length]);
        
        const personChart = new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'ยอดขาย Win (บาท)',
                    data: values,
                    backgroundColor: bgColors
                }]
            },
            options: {
                responsive: true,
                onClick: function(evt, elements) {
                    if (elements.length === 0) return;
                    const idx = elements[0].index;
                    const person = data[idx];
                    if (!person || !person.user_id) return;
                    showWinProjects(person.user_id, person.nname + ' ' + (person.surename || ''));
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('th-TH');
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + Number(context.raw).toLocaleString('th-TH') + ' บาท';
                            }
                        }
                    }
                }
            }
        });
    }

    function showWinProjects(userId, userName) {
        const modal = $('#winProjectsModal');
        const loading = $('#winProjectsLoading');
        const table = $('#winProjectsTable');
        const tbody = $('#winProjectsBody');
        const total = $('#winProjectsTotal');
        const empty = $('#winProjectsEmpty');

        $('#winProjectsUserName').text(userName);
        loading.show();
        table.hide();
        empty.hide();
        tbody.empty();
        modal.modal('show');

        const params = new URLSearchParams(window.location.search);
        let url = '/admin/dashboard/win-projects/' + userId;
        if (params.toString()) {
            url += '?' + params.toString();
        }

        fetch(url)
            .then(res => res.json())
            .then(projects => {
                loading.hide();
                if (!projects.length) {
                    empty.show();
                    return;
                }
                let sumValue = 0;
                projects.forEach((p, i) => {
                    const val = Number(p.product_value) || 0;
                    sumValue += val;
                    tbody.append(`
                        <tr>
                            <td>${i + 1}</td>
                            <td>${p.Product_detail || '-'}</td>
                            <td>${p.company || '-'}</td>
                            <td>${p.product_group || '-'}</td>
                            <td class="text-right">${val.toLocaleString('th-TH', {minimumFractionDigits: 2})}</td>
                            <td>${p.win_date || '-'}</td>
                        </tr>
                    `);
                });
                total.text(sumValue.toLocaleString('th-TH', {minimumFractionDigits: 2}));
                table.show();
            })
            .catch(() => {
                loading.hide();
                empty.show();
            });
    }

    // Sale Status Chart (Grouped Bar by Month)
    function renderSaleStatusChart(data) {
        const ctx = document.getElementById('salestatusChart');
        if (!ctx) return;

        const stepConfig = {
            1: { label: 'นำเสนอ Solution', color: 'rgba(54, 162, 235, 0.8)', border: 'rgba(54, 162, 235, 1)' },
            2: { label: 'ตั้งงบประมาณ', color: 'rgba(255, 206, 86, 0.8)', border: 'rgba(255, 206, 86, 1)' },
            3: { label: 'ร่าง TOR', color: 'rgba(255, 159, 64, 0.8)', border: 'rgba(255, 159, 64, 1)' },
            4: { label: 'Bidding / เสนอราคา', color: 'rgba(153, 102, 255, 0.8)', border: 'rgba(153, 102, 255, 1)' },
            5: { label: 'WIN', color: 'rgba(40, 167, 69, 0.8)', border: 'rgba(40, 167, 69, 1)' },
            6: { label: 'LOST', color: 'rgba(220, 53, 69, 0.8)', border: 'rgba(220, 53, 69, 1)' }
        };

        const months = [...new Set(data.map(r => r.sale_month))].sort();

        const datasets = Object.keys(stepConfig).map(orderlv => {
            const cfg = stepConfig[orderlv];
            const values = months.map(m => {
                const row = data.find(r => r.sale_month === m && r.orderlv == orderlv);
                return row ? Number(row.count) : 0;
            });
            return {
                label: cfg.label,
                data: values,
                backgroundColor: cfg.color,
                borderColor: cfg.border,
                borderWidth: 1
            };
        });

        new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: months,
                datasets: datasets
            },
            options: {
                responsive: true,
                scales: {
                    x: { stacked: false },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            callback: function(value) {
                                return Number.isInteger(value) ? value : '';
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw + ' รายการ';
                            }
                        }
                    }
                }
            }
        });
    }

    // Status Value Chart (Grouped Bar by Month)
    function renderStatusValueChart(data) {
        const ctx = document.getElementById('statusValueChart');
        if (!ctx) return;

        const stepConfig = {
            1: { label: 'นำเสนอ Solution', color: 'rgba(54, 162, 235, 0.8)', border: 'rgba(54, 162, 235, 1)' },
            2: { label: 'ตั้งงบประมาณ', color: 'rgba(255, 206, 86, 0.8)', border: 'rgba(255, 206, 86, 1)' },
            3: { label: 'ร่าง TOR', color: 'rgba(255, 159, 64, 0.8)', border: 'rgba(255, 159, 64, 1)' },
            4: { label: 'Bidding / เสนอราคา', color: 'rgba(153, 102, 255, 0.8)', border: 'rgba(153, 102, 255, 1)' },
            5: { label: 'WIN', color: 'rgba(40, 167, 69, 0.8)', border: 'rgba(40, 167, 69, 1)' },
            6: { label: 'LOST', color: 'rgba(220, 53, 69, 0.8)', border: 'rgba(220, 53, 69, 1)' }
        };

        const months = [...new Set(data.map(r => r.sale_month))].sort();

        const datasets = Object.keys(stepConfig).map(orderlv => {
            const cfg = stepConfig[orderlv];
            const values = months.map(m => {
                const row = data.find(r => r.sale_month === m && r.orderlv == orderlv);
                return row ? Number(row.total_value) : 0;
            });
            return {
                label: cfg.label,
                data: values,
                backgroundColor: cfg.color,
                borderColor: cfg.border,
                borderWidth: 1
            };
        });

        new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: months,
                datasets: datasets
            },
            options: {
                responsive: true,
                scales: {
                    x: { stacked: false },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('th-TH');
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + Number(context.raw).toLocaleString('th-TH') + ' บาท';
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

    // Target/Forecast/Win Chart
    function renderTargetForecastWinChart(data) {
        const ctx = document.getElementById('targetForecastWinChart');
        if (!ctx) return;

        const labels = data.map(r => r.nname + ' ' + (r.surename || ''));

        new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Target',
                        data: data.map(r => Number(r.target_value)),
                        backgroundColor: 'rgba(153, 102, 255, 0.6)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Forecast',
                        data: data.map(r => Number(r.forecast_value)),
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Win',
                        data: data.map(r => Number(r.win_value)),
                        backgroundColor: 'rgba(40, 167, 69, 0.8)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    }
                ]
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
                },
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + Number(context.raw).toLocaleString('th-TH') + ' บาท';
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
    renderTargetForecastWinChart(targetForecastWinData);
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
