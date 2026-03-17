<?php

namespace App\Reports;

use Illuminate\Support\Facades\DB;
use PDF;

class BiddingReportPdf
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

    public function generate()
    {
        $data = $this->getData();
        
        $pdf = PDF::loadView('admin.reports.pdf.bidding', [
            'data' => $data,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'userId' => $this->userId
        ]);

        return $pdf->download('รายงานวันยื่น_Bidding_' . date('Y-m-d') . '.pdf');
    }

    private function getData()
    {
        $query = DB::table('transactional as t')
            ->leftJoin('company_catalog as c', 't.company_id', '=', 'c.company_id')
            ->leftJoin('user as u', 't.user_id', '=', 'u.user_id')
            ->select([
                't.Product_detail as project_name',
                'c.company as company_name',
                't.product_value as value',
                't.date_of_closing_of_sale as bidding_date',
                DB::raw("CONCAT(u.nname, ' ', u.surename) as user_name")
            ])
            ->whereNotNull('t.date_of_closing_of_sale');

        if ($this->userId) {
            $query->where('t.user_id', $this->userId);
        }

        if ($this->dateFrom) {
            $query->where('t.date_of_closing_of_sale', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->where('t.date_of_closing_of_sale', '<=', $this->dateTo);
        }

        return $query->orderBy('t.date_of_closing_of_sale', 'desc')->get();
    }
}
