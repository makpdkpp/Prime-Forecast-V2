<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>รายงานวันยื่น Bidding</title>
    <style>
        body {
            font-family: 'Sarabun', 'Tahoma', sans-serif;
            font-size: 14px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .filter-info {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>รายงานวันยื่น Bidding</h1>
        <p>Prime Forecast System</p>
    </div>

    @if($dateFrom || $dateTo || $userId)
    <div class="filter-info">
        <strong>เงื่อนไขการกรอง:</strong><br>
        @if($dateFrom) วันที่เริ่มต้น: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}<br>@endif
        @if($dateTo) วันที่สิ้นสุด: {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}<br>@endif
        @if($userId) ผู้ใช้: {{ \App\Models\User::find($userId)->nname ?? '' }} {{ \App\Models\User::find($userId)->surename ?? '' }}@endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="25%">ชื่อโครงการ</th>
                <th width="20%">หน่วยงาน/บริษัท</th>
                <th width="15%" class="text-right">มูลค่า (฿)</th>
                <th width="15%">วันยื่น Bidding</th>
                <th width="20%">ชื่อผู้รับผิดชอบ</th>
            </tr>
        </thead>
        <tbody>
            @php $totalValue = 0; @endphp
            @foreach($data as $index => $item)
                @php $totalValue += $item->value ?? 0; @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->project_name ?? '-' }}</td>
                    <td>{{ $item->company_name ?? '-' }}</td>
                    <td class="text-right">{{ number_format($item->value ?? 0, 2) }}</td>
                    <td>{{ $item->bidding_date ? \Carbon\Carbon::parse($item->bidding_date)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $item->user_name ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3">รวมทั้งหมด</th>
                <th class="text-right">{{ number_format($totalValue, 2) }}</th>
                <th colspan="2">{{ $data->count() }} รายการ</th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>วันที่สร้างรายงาน: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
