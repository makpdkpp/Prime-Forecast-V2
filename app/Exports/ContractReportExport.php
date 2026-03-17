<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Facades\DB;

class ContractReportExport implements FromQuery, WithHeadings, WithTitle, ShouldAutoSize, WithColumnFormatting, WithMapping
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
        $query = DB::table('transactional as t')
            ->leftJoin('company_catalog as c', 't.company_id', '=', 'c.company_id')
            ->leftJoin('user as u', 't.user_id', '=', 'u.user_id')
            ->select([
                't.Product_detail as project_name',
                'c.company as company_name',
                't.product_value as value',
                't.sales_can_be_close as contract_date',
                DB::raw("CONCAT(u.nname, ' ', u.surename) as user_name")
            ])
            ->whereNotNull('t.sales_can_be_close');

        if ($this->userId) {
            $query->where('t.user_id', $this->userId);
        }

        if ($this->dateFrom) {
            $query->where('t.sales_can_be_close', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->where('t.sales_can_be_close', '<=', $this->dateTo);
        }

        return $query->orderBy('t.sales_can_be_close', 'desc');
    }

    public function headings(): array
    {
        return [
            'ชื่อโครงการ',
            'หน่วยงาน/บริษัท',
            'มูลค่า (฿)',
            'วันเซ็นสัญญา',
            'ชื่อผู้รับผิดชอบ'
        ];
    }

    public function title(): string
    {
        return 'รายงานวันเซ็นสัญญา';
    }

    public function map($row): array
    {
        return [
            $row->project_name ?? '-',
            $row->company_name ?? '-',
            $row->value ? number_format($row->value, 2) : '0.00',
            $row->contract_date ? \Carbon\Carbon::parse($row->contract_date)->format('d/m/Y') : '-',
            $row->user_name ?? '-'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }
}
