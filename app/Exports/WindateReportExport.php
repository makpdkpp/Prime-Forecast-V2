<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\DB;

class WindateReportExport implements FromQuery, WithHeadings, WithTitle, ShouldAutoSize, WithMapping
{
    protected $userId;
    protected $dateFrom;
    protected $dateTo;

    public function __construct($userId = null, $dateFrom = null, $dateTo = null)
    {
        $this->userId = $userId;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function query()
    {
        $winSub = DB::table('transactional_step as ts')
            ->join('step as s', 's.level_id', '=', 'ts.level_id')
            ->where('s.level', 5)
            ->select('ts.transac_id', DB::raw('MAX(ts.transacstep_id) as max_step_id'))
            ->groupBy('ts.transac_id');

        $query = DB::table('transactional as t')
            ->joinSub($winSub, 'win_latest', 'win_latest.transac_id', '=', 't.transac_id')
            ->join('transactional_step as ts_win', 'ts_win.transacstep_id', '=', 'win_latest.max_step_id')
            ->leftJoin('company_catalog as c', 't.company_id', '=', 'c.company_id')
            ->leftJoin('user as u', 't.user_id', '=', 'u.user_id')
            ->select([
                't.Product_detail as project_name',
                'c.company as company_name',
                't.product_value as value',
                'ts_win.date as win_date',
                DB::raw("CONCAT(u.nname, ' ', u.surename) as user_name")
            ])
            ->whereNull('t.deleted_at');

        if ($this->userId) {
            $query->where('t.user_id', $this->userId);
        }

        if ($this->dateFrom) {
            $query->where('ts_win.date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->where('ts_win.date', '<=', $this->dateTo);
        }

        return $query->orderBy('ts_win.date', 'desc');
    }

    public function headings(): array
    {
        return [
            'ชื่อโครงการ',
            'หน่วยงาน/บริษัท',
            'มูลค่า (฿)',
            'Windate',
            'ชื่อผู้รับผิดชอบ'
        ];
    }

    public function title(): string
    {
        return 'รายงาน Windate';
    }

    public function map($row): array
    {
        return [
            $row->project_name ?? '-',
            $row->company_name ?? '-',
            $row->value ? number_format($row->value, 2) : '0.00',
            $row->win_date ? \Carbon\Carbon::parse($row->win_date)->format('d/m/Y') : '-',
            $row->user_name ?? '-'
        ];
    }
}
