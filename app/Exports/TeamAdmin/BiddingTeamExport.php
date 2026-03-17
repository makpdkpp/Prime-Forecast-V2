<?php

namespace App\Exports\TeamAdmin;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Facades\DB;

class BiddingTeamExport implements FromQuery, WithHeadings, WithTitle, ShouldAutoSize, WithColumnFormatting, WithMapping
{
    protected $teamIds;
    protected $userId;
    protected $dateFrom;
    protected $dateTo;

    public function __construct(array $teamIds, $userId = null, $dateFrom = null, $dateTo = null)
    {
        $this->teamIds  = $teamIds;
        $this->userId   = $userId;
        $this->dateFrom = $dateFrom;
        $this->dateTo   = $dateTo;
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
                't.date_of_closing_of_sale as bidding_date',
                DB::raw("CONCAT(u.nname, ' ', u.surename) as user_name"),
            ])
            ->whereNotNull('t.date_of_closing_of_sale')
            ->whereIn('t.team_id', $this->teamIds);

        if ($this->userId) {
            $query->where('t.user_id', $this->userId);
        }
        if ($this->dateFrom) {
            $query->where('t.date_of_closing_of_sale', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->where('t.date_of_closing_of_sale', '<=', $this->dateTo);
        }

        return $query->orderBy('t.date_of_closing_of_sale', 'desc');
    }

    public function headings(): array
    {
        return ['ชื่อโครงการ', 'หน่วยงาน/บริษัท', 'มูลค่า (฿)', 'วันยื่น Bidding', 'ชื่อผู้รับผิดชอบ'];
    }

    public function title(): string
    {
        return 'รายงานวันยื่น Bidding';
    }

    public function map($row): array
    {
        return [
            $row->project_name ?? '-',
            $row->company_name ?? '-',
            $row->value ? number_format($row->value, 2) : '0.00',
            $row->bidding_date ? \Carbon\Carbon::parse($row->bidding_date)->format('d/m/Y') : '-',
            $row->user_name ?? '-',
        ];
    }

    public function columnFormats(): array
    {
        return ['C' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1];
    }
}
