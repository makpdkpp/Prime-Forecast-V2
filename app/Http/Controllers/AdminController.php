<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Transactional;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        // Get filter parameters
        $year = $request->get('year');
        $quarter = $request->get('quarter');
        
        // Get available years from transactional data
        $availableYears = Cache::remember('dashboard:admin:availableYears', 120, function () {
            return DB::table('transactional')
                ->select('fiscalyear')
                ->distinct()
                ->orderBy('fiscalyear', 'desc')
                ->pluck('fiscalyear');
        });
        
        // Build base query with filters
        $query = DB::table('transactional');
        
        if ($year) {
            $query->where('fiscalyear', $year);
        }
        
        $this->applyQuarterFilterToQuery($query, $year, $quarter);
        
        // Get summary statistics
        $estimateValue = (clone $query)->sum('product_value');
        
        // Win value and count (latest step is WIN - level = 5)
        $winParams = [];
        $winWhere = "";
        if ($year) {
            $winWhere .= " AND t.fiscalyear = ?";
            $winParams[] = $year;
        }
        $this->appendQuarterSqlFilter($winWhere, $winParams, $year, $quarter, 't');
        $winData = DB::select("
            SELECT 
                COALESCE(SUM(t.product_value), 0) as win_value,
                COUNT(*) as win_count
            FROM transactional t
            JOIN (
                SELECT ts.transac_id
                FROM transactional_step ts
                JOIN step s ON s.level_id = ts.level_id
                WHERE s.level = 5
                AND (ts.transacstep_id, ts.transac_id) IN (
                    SELECT MAX(ts2.transacstep_id), ts2.transac_id
                    FROM transactional_step ts2
                    GROUP BY ts2.transac_id
                )
            ) wintrans ON wintrans.transac_id = t.transac_id
            WHERE 1=1 {$winWhere}
        ", $winParams);
        
        // Lost count (latest step is LOST - level = 6)
        $lostParams = [];
        $lostWhere = "";
        if ($year) {
            $lostWhere .= " AND t.fiscalyear = ?";
            $lostParams[] = $year;
        }
        $this->appendQuarterSqlFilter($lostWhere, $lostParams, $year, $quarter, 't');
        $lostCount = DB::select("
            SELECT COUNT(*) as lost_count
            FROM transactional t
            JOIN (
                SELECT ts.transac_id
                FROM transactional_step ts
                JOIN step s ON s.level_id = ts.level_id
                WHERE s.level = 6
                AND (ts.transacstep_id, ts.transac_id) IN (
                    SELECT MAX(ts2.transacstep_id), ts2.transac_id
                    FROM transactional_step ts2
                    GROUP BY ts2.transac_id
                )
            ) losttrans ON losttrans.transac_id = t.transac_id
            WHERE 1=1 {$lostWhere}
        ", $lostParams);
        
        $winValue = $winData[0]->win_value ?? 0;
        $winCount = $winData[0]->win_count ?? 0;
        $lostCount = $lostCount[0]->lost_count ?? 0;
        
        // Cumulative win by month (only transactions whose latest step is WIN - level = 5)
        $cumulativeWinParams = [];
        $cumulativeWinWhere = "";
        if ($year) {
            $cumulativeWinWhere .= " AND t.fiscalyear = ?";
            $cumulativeWinParams[] = $year;
        }
        $this->appendQuarterSqlFilter($cumulativeWinWhere, $cumulativeWinParams, $year, $quarter, 't');
        $cumulativeWin = Cache::remember('dashboard:admin:cumulativeWin:' . ($year ?? 'all') . ':' . ($quarter ?? 'all'), 120, function () use ($cumulativeWinWhere, $cumulativeWinParams) {
            return DB::select("
                SELECT 
                    monthly.sale_month,
                    SUM(monthly.monthly_value) OVER (ORDER BY monthly.sale_month) as cumulative_win_value
                FROM (
                    SELECT 
                        DATE_FORMAT(ts_win.date, '%Y-%m') as sale_month,
                        SUM(t.product_value) as monthly_value
                    FROM transactional t
                    JOIN (
                        SELECT ts.transac_id, ts.date
                        FROM transactional_step ts
                        JOIN step s ON s.level_id = ts.level_id
                        WHERE s.level = 5
                        AND (ts.transacstep_id, ts.transac_id) IN (
                            SELECT MAX(ts2.transacstep_id), ts2.transac_id
                            FROM transactional_step ts2
                            GROUP BY ts2.transac_id
                        )
                    ) ts_win ON ts_win.transac_id = t.transac_id
                    WHERE 1=1 {$cumulativeWinWhere}
                    GROUP BY DATE_FORMAT(ts_win.date, '%Y-%m')
                ) monthly
                ORDER BY monthly.sale_month
            ", $cumulativeWinParams);
        });
        
        // Sum by team (only transactions whose latest step is WIN - level = 5)
        $sumByTeamParams = [];
        $sumByTeamWhere = "";
        if ($year) {
            $sumByTeamWhere .= " AND t.fiscalyear = ?";
            $sumByTeamParams[] = $year;
        }
        $this->appendQuarterSqlFilter($sumByTeamWhere, $sumByTeamParams, $year, $quarter, 't');
        $sumByTeam = Cache::remember('dashboard:admin:sumByTeam:' . ($year ?? 'all') . ':' . ($quarter ?? 'all'), 120, function () use ($sumByTeamWhere, $sumByTeamParams) {
            return DB::select("
                SELECT 
                    tc.team_id,
                    tc.team,
                    SUM(t.product_value) as total_value
                FROM transactional t
                JOIN team_catalog tc ON t.team_id = tc.team_id
                JOIN (
                    SELECT ts.transac_id
                    FROM transactional_step ts
                    JOIN step s ON s.level_id = ts.level_id
                    WHERE s.level = 5
                    AND (ts.transacstep_id, ts.transac_id) IN (
                        SELECT MAX(ts2.transacstep_id), ts2.transac_id
                        FROM transactional_step ts2
                        GROUP BY ts2.transac_id
                    )
                ) wintrans ON wintrans.transac_id = t.transac_id
                WHERE 1=1 {$sumByTeamWhere}
                GROUP BY tc.team_id, tc.team
                ORDER BY total_value DESC
            ", $sumByTeamParams);
        });
        
        // Sum by person (only transactions whose latest step is WIN - level = 5)
        $sumByPersonParams = [];
        $sumByPersonWhere = "";
        if ($year) {
            $sumByPersonWhere .= " AND t.fiscalyear = ?";
            $sumByPersonParams[] = $year;
        }
        $this->appendQuarterSqlFilter($sumByPersonWhere, $sumByPersonParams, $year, $quarter, 't');
        $sumByPerson = Cache::remember('dashboard:admin:sumByPerson:' . ($year ?? 'all') . ':' . ($quarter ?? 'all'), 120, function () use ($sumByPersonWhere, $sumByPersonParams) {
            return DB::select("
                SELECT 
                    u.user_id,
                    u.nname,
                    u.surename,
                    SUM(t.product_value) as total_value
                FROM transactional t
                JOIN user u ON t.user_id = u.user_id
                JOIN (
                    SELECT ts.transac_id
                    FROM transactional_step ts
                    JOIN step s ON s.level_id = ts.level_id
                    WHERE s.level = 5
                    AND (ts.transacstep_id, ts.transac_id) IN (
                        SELECT MAX(ts2.transacstep_id), ts2.transac_id
                        FROM transactional_step ts2
                        GROUP BY ts2.transac_id
                    )
                ) wintrans ON wintrans.transac_id = t.transac_id
                WHERE 1=1 {$sumByPersonWhere}
                GROUP BY u.user_id, u.nname, u.surename
                ORDER BY total_value DESC
                LIMIT 10
            ", $sumByPersonParams);
        });
        
        // Sale status count by month and step level
        $saleStatusParams = [];
        $saleStatusWhere = "";
        if ($year) {
            $saleStatusWhere .= " AND t.fiscalyear = ?";
            $saleStatusParams[] = $year;
        }
        $this->appendQuarterSqlFilter($saleStatusWhere, $saleStatusParams, $year, $quarter, 't');
        $saleStatus = Cache::remember('dashboard:admin:saleStatus:' . ($year ?? 'all') . ':' . ($quarter ?? 'all'), 120, function () use ($saleStatusParams, $saleStatusWhere) {
            return DB::select("
                SELECT 
                    DATE_FORMAT(ts.date, '%Y-%m') as sale_month,
                    s.level_id,
                    s.orderlv,
                    s.level,
                    COUNT(*) as count
                FROM transactional t
                JOIN transactional_step ts ON t.transac_id = ts.transac_id
                JOIN step s ON s.level_id = ts.level_id
                WHERE (ts.transacstep_id, ts.transac_id) IN (
                    SELECT MAX(ts2.transacstep_id), ts2.transac_id
                    FROM transactional_step ts2
                    GROUP BY ts2.transac_id
                )
                AND s.orderlv BETWEEN 1 AND 6
                {$saleStatusWhere}
                GROUP BY DATE_FORMAT(ts.date, '%Y-%m'), s.level_id, s.orderlv, s.level
                ORDER BY sale_month, s.orderlv
            ", $saleStatusParams);
        });
        
        // Sale status value by month and step level
        $saleStatusValueParams = [];
        $saleStatusValueWhere = "";
        if ($year) {
            $saleStatusValueWhere .= " AND t.fiscalyear = ?";
            $saleStatusValueParams[] = $year;
        }
        $this->appendQuarterSqlFilter($saleStatusValueWhere, $saleStatusValueParams, $year, $quarter, 't');
        $saleStatusValue = Cache::remember('dashboard:admin:saleStatusValue:' . ($year ?? 'all') . ':' . ($quarter ?? 'all'), 120, function () use ($saleStatusValueParams, $saleStatusValueWhere) {
            return DB::select("
                SELECT 
                    DATE_FORMAT(ts.date, '%Y-%m') as sale_month,
                    s.level_id,
                    s.orderlv,
                    s.level,
                    SUM(t.product_value) as total_value
                FROM transactional t
                JOIN transactional_step ts ON t.transac_id = ts.transac_id
                JOIN step s ON s.level_id = ts.level_id
                WHERE (ts.transacstep_id, ts.transac_id) IN (
                    SELECT MAX(ts2.transacstep_id), ts2.transac_id
                    FROM transactional_step ts2
                    GROUP BY ts2.transac_id
                )
                AND s.orderlv BETWEEN 1 AND 6
                {$saleStatusValueWhere}
                GROUP BY DATE_FORMAT(ts.date, '%Y-%m'), s.level_id, s.orderlv, s.level
                ORDER BY sale_month, s.orderlv
            ", $saleStatusValueParams);
        });
        
        // Top 10 products (only WIN - level = 5)
        $topProductsParams = [];
        $topProductsWhere = "";
        if ($year) {
            $topProductsWhere .= " AND t.fiscalyear = ?";
            $topProductsParams[] = $year;
        }
        $this->appendQuarterSqlFilter($topProductsWhere, $topProductsParams, $year, $quarter, 't');
        $topProducts = Cache::remember('dashboard:admin:topProducts:' . ($year ?? 'all') . ':' . ($quarter ?? 'all'), 120, function () use ($topProductsWhere, $topProductsParams) {
            return DB::select("
                SELECT 
                    pg.product_id,
                    pg.product,
                    SUM(t.product_value) as total_value
                FROM transactional t
                JOIN product_group pg ON t.Product_id = pg.product_id
                JOIN (
                    SELECT ts.transac_id
                    FROM transactional_step ts
                    JOIN step s ON s.level_id = ts.level_id
                    WHERE s.level = 5
                    AND (ts.transacstep_id, ts.transac_id) IN (
                        SELECT MAX(ts2.transacstep_id), ts2.transac_id
                        FROM transactional_step ts2
                        GROUP BY ts2.transac_id
                    )
                ) wintrans ON wintrans.transac_id = t.transac_id
                WHERE 1=1 {$topProductsWhere}
                GROUP BY pg.Product_id, pg.product
                ORDER BY total_value DESC
                LIMIT 10
            ", $topProductsParams);
        });
        
        // Top 10 customers (only WIN - level = 5)
        $topCustomersParams = [];
        $topCustomersWhere = "";
        if ($year) {
            $topCustomersWhere .= " AND t.fiscalyear = ?";
            $topCustomersParams[] = $year;
        }
        $this->appendQuarterSqlFilter($topCustomersWhere, $topCustomersParams, $year, $quarter, 't');
        $topCustomers = Cache::remember('dashboard:admin:topCustomers:' . ($year ?? 'all') . ':' . ($quarter ?? 'all'), 120, function () use ($topCustomersWhere, $topCustomersParams) {
            return DB::select("
                SELECT 
                    cc.company_id,
                    cc.company,
                    SUM(t.product_value) as total_value
                FROM transactional t
                JOIN company_catalog cc ON t.company_id = cc.company_id
                JOIN (
                    SELECT ts.transac_id
                    FROM transactional_step ts
                    JOIN step s ON s.level_id = ts.level_id
                    WHERE s.level = 5
                    AND (ts.transacstep_id, ts.transac_id) IN (
                        SELECT MAX(ts2.transacstep_id), ts2.transac_id
                        FROM transactional_step ts2
                        GROUP BY ts2.transac_id
                    )
                ) wintrans ON wintrans.transac_id = t.transac_id
                WHERE 1=1 {$topCustomersWhere}
                GROUP BY cc.company_id, cc.company
                ORDER BY total_value DESC
                LIMIT 10
            ", $topCustomersParams);
        });
        
        // Target/Forecast/Win comparison per user
        $tfwParams = [];
        $tfwWhere = "";
        $tfwTargetWhere = "";
        if ($year) {
            $tfwWhere .= " AND t.fiscalyear = ?";
            $tfwTargetWhere .= " AND uft.fiscal_year = ?";
            $tfwParams[] = $year;
        }
        $tfwQuarterParams = [];
        $this->appendQuarterSqlFilter($tfwWhere, $tfwQuarterParams, $year, $quarter, 't');
        // Build params for forecast subquery
        $forecastParams = [];
        if ($year) {
            $forecastParams[] = $year;
        }
        $forecastParams = array_merge($forecastParams, $tfwQuarterParams);
        // Build params for win subquery
        $winParams = $forecastParams;
        // Build params for target subquery
        $targetParams = [];
        if ($year) $targetParams[] = $year;

        $targetForecastWin = Cache::remember('dashboard:admin:targetForecastWin:' . ($year ?? 'all') . ':' . ($quarter ?? 'all'), 120, function () use ($tfwTargetWhere, $tfwWhere, $targetParams, $forecastParams, $winParams) {
            return DB::select("
                SELECT 
                    u.user_id,
                    u.nname,
                    u.surename,
                    COALESCE(tgt.target_value, 0) as target_value,
                    COALESCE(fc.forecast_value, 0) as forecast_value,
                    COALESCE(wn.win_value, 0) as win_value
                FROM user u
                LEFT JOIN (
                    SELECT uft.user_id, SUM(uft.target_value) as target_value
                    FROM user_forecast_target uft
                    WHERE 1=1 {$tfwTargetWhere}
                    GROUP BY uft.user_id
                ) tgt ON tgt.user_id = u.user_id
                LEFT JOIN (
                    SELECT t.user_id, SUM(t.product_value) as forecast_value
                    FROM transactional t
                    WHERE 1=1 {$tfwWhere}
                    GROUP BY t.user_id
                ) fc ON fc.user_id = u.user_id
                LEFT JOIN (
                    SELECT t.user_id, SUM(t.product_value) as win_value
                    FROM transactional t
                    JOIN (
                        SELECT ts.transac_id
                        FROM transactional_step ts
                        JOIN step s ON s.level_id = ts.level_id
                        WHERE s.level = 5
                        AND (ts.transacstep_id, ts.transac_id) IN (
                            SELECT MAX(ts2.transacstep_id), ts2.transac_id
                            FROM transactional_step ts2
                            GROUP BY ts2.transac_id
                        )
                    ) wintrans ON wintrans.transac_id = t.transac_id
                    WHERE 1=1 {$tfwWhere}
                    GROUP BY t.user_id
                ) wn ON wn.user_id = u.user_id
                WHERE (tgt.target_value IS NOT NULL OR fc.forecast_value IS NOT NULL OR wn.win_value IS NOT NULL)
                ORDER BY COALESCE(fc.forecast_value, 0) DESC
            ", array_merge($targetParams, $forecastParams, $winParams));
        });

        return view('admin.dashboard', compact(
            'estimateValue',
            'winValue',
            'winCount',
            'lostCount',
            'cumulativeWin',
            'sumByTeam',
            'sumByPerson',
            'saleStatus',
            'saleStatusValue',
            'topProducts',
            'topCustomers',
            'targetForecastWin',
            'availableYears',
            'year',
            'quarter'
        ));
    }

    public function chartDetail(Request $request)
    {
        $type = $request->get('type');
        $value = $request->get('value');
        $value2 = $request->get('value2');
        $year = $request->get('year');
        $quarter = $request->get('quarter');

        $params = [];
        $where = "";
        if ($year) {
            $where .= " AND t.fiscalyear = ?";
            $params[] = $year;
        }
        $this->appendQuarterSqlFilter($where, $params, $year, $quarter, 't');

        // WIN subquery — used by most chart types
        $winJoin = "
            JOIN (
                SELECT ts.transac_id
                FROM transactional_step ts
                JOIN step s ON s.level_id = ts.level_id
                WHERE s.level = 5
                AND (ts.transacstep_id, ts.transac_id) IN (
                    SELECT MAX(ts2.transacstep_id), ts2.transac_id
                    FROM transactional_step ts2
                    GROUP BY ts2.transac_id
                )
            ) wintrans ON wintrans.transac_id = t.transac_id
        ";

        // Step subquery — for step-based charts
        $stepJoin = "
            JOIN transactional_step ts ON t.transac_id = ts.transac_id
            JOIN step s ON s.level_id = ts.level_id
            AND (ts.transacstep_id, ts.transac_id) IN (
                SELECT MAX(ts2.transacstep_id), ts2.transac_id
                FROM transactional_step ts2
                GROUP BY ts2.transac_id
            )
        ";

        $extraJoin = "";
        $extraWhere = "";
        $extraParams = [];

        switch ($type) {
            case 'month': // ยอดขายรวม — win by month
                $extraJoin = $winJoin . "
                    JOIN transactional_step ts_w ON ts_w.transac_id = t.transac_id
                    JOIN step s_w ON s_w.level_id = ts_w.level_id AND s_w.level = 5";
                $extraWhere = " AND DATE_FORMAT(ts_w.date, '%Y-%m') = ?";
                $extraParams[] = $value;
                break;
            case 'team': // ยอดขายรายทีม — win by team
                $extraJoin = $winJoin;
                $extraWhere = " AND t.team_id = ?";
                $extraParams[] = $value;
                break;
            case 'step': // สถานะการขาย — by step orderlv + optional month
                $extraJoin = $stepJoin;
                $extraWhere = " AND s.level_id = ?";
                $extraParams[] = $value;
                if ($value2) {
                    $extraWhere .= " AND DATE_FORMAT(ts.date, '%Y-%m') = ?";
                    $extraParams[] = $value2;
                }
                break;
            case 'user_forecast': // Target/Forecast/Win — all transactions by user
                $extraWhere = " AND t.user_id = ?";
                $extraParams[] = $value;
                break;
            case 'user_win': // Target/Forecast/Win — win only by user
                $extraJoin = $winJoin;
                $extraWhere = " AND t.user_id = ?";
                $extraParams[] = $value;
                break;
            case 'product': // TOP 10 product — win by product
                $extraJoin = $winJoin;
                $extraWhere = " AND t.Product_id = ?";
                $extraParams[] = $value;
                break;
            case 'company': // TOP 10 customer — win by company
                $extraJoin = $winJoin;
                $extraWhere = " AND t.company_id = ?";
                $extraParams[] = $value;
                break;
            default:
                return response()->json([]);
        }

        $allParams = array_merge($params, $extraParams);

        $projects = DB::select("
            SELECT 
                t.transac_id,
                t.Product_detail,
                t.product_value,
                c.company,
                pg.product as product_group,
                u.nname,
                u.surename,
                tc.team,
                COALESCE(latest_s.level, '-') as step_name,
                t.contact_start_date
            FROM transactional t
            {$extraJoin}
            LEFT JOIN company_catalog c ON t.company_id = c.company_id
            LEFT JOIN product_group pg ON t.Product_id = pg.product_id
            LEFT JOIN user u ON t.user_id = u.user_id
            LEFT JOIN team_catalog tc ON t.team_id = tc.team_id
            LEFT JOIN (
                SELECT ts3.transac_id, s3.level
                FROM transactional_step ts3
                JOIN step s3 ON s3.level_id = ts3.level_id
                WHERE (ts3.transacstep_id, ts3.transac_id) IN (
                    SELECT MAX(ts4.transacstep_id), ts4.transac_id
                    FROM transactional_step ts4
                    GROUP BY ts4.transac_id
                )
            ) latest_s ON latest_s.transac_id = t.transac_id
            WHERE 1=1 {$where} {$extraWhere}
            ORDER BY t.product_value DESC
            LIMIT 100
        ", $allParams);

        return response()->json($projects);
    }

    public function winProjectsByUser(Request $request, $userId)
    {
        $year = $request->get('year');
        $quarter = $request->get('quarter');

        $params = [$userId];
        $where = "";
        if ($year) {
            $where .= " AND t.fiscalyear = ?";
            $params[] = $year;
        }
        $this->appendQuarterSqlFilter($where, $params, $year, $quarter, 't');

        $projects = DB::select("
            SELECT 
                t.transac_id,
                t.Product_detail,
                t.product_value,
                c.company,
                pg.product as product_group,
                DATE_FORMAT(ts_win.date, '%Y-%m-%d') as win_date
            FROM transactional t
            JOIN (
                SELECT ts.transac_id, ts.date
                FROM transactional_step ts
                JOIN step s ON s.level_id = ts.level_id
                WHERE s.level = 5
                AND (ts.transacstep_id, ts.transac_id) IN (
                    SELECT MAX(ts2.transacstep_id), ts2.transac_id
                    FROM transactional_step ts2
                    GROUP BY ts2.transac_id
                )
            ) ts_win ON ts_win.transac_id = t.transac_id
            LEFT JOIN company_catalog c ON t.company_id = c.company_id
            LEFT JOIN product_group pg ON t.Product_id = pg.product_id
            WHERE t.user_id = ? {$where}
            ORDER BY ts_win.date DESC
        ", $params);

        return response()->json($projects);
    }

    public function dashboardTable(Request $request)
    {
        // Get filter parameters
        $year = $request->get('year');
        $quarter = $request->get('quarter');
        $userId = $request->get('user_id');
        
        // Get available years
        $availableYears = Cache::remember('dashboard:admin:table:availableYears', 120, function () {
            return DB::table('transactional')
                ->select('fiscalyear')
                ->distinct()
                ->orderBy('fiscalyear', 'desc')
                ->pluck('fiscalyear');
        });

        $availableUsers = Cache::remember('dashboard:admin:table:availableUsers', 120, function () {
            return DB::table('user')
                ->select('user_id', 'nname', 'surename')
                ->where('role_id', 3)
                ->orderBy('nname')
                ->orderBy('surename')
                ->get();
        });
        
        return view('admin.dashboard_table', compact('availableYears', 'availableUsers', 'year', 'quarter', 'userId'));
    }

    public function dashboardTableData(Request $request)
    {
        $year = $request->get('year');
        $quarter = $request->get('quarter');
        $userId = $request->get('user_id');

        $draw = (int) $request->input('draw', 1);
        $start = max((int) $request->input('start', 0), 0);
        $length = (int) $request->input('length', 25);
        if ($length <= 0 || $length > 200) {
            $length = 25;
        }

        $base = DB::table('transactional as t')
            ->leftJoin('company_catalog as c', 't.company_id', '=', 'c.company_id')
            ->leftJoin('product_group as pg', 't.Product_id', '=', 'pg.product_id')
            ->leftJoin('team_catalog as tc', 't.team_id', '=', 'tc.team_id')
            ->leftJoin('priority_level as pl', 't.priority_id', '=', 'pl.priority_id')
            ->leftJoin('user as u', 't.user_id', '=', 'u.user_id')
            ->leftJoin('source_of_the_budget as sb', 't.Source_budget_id', '=', 'sb.Source_budget_id')
            ->leftJoin('step as s', 't.Step_id', '=', 's.level_id');

        if ($year) {
            $base->where('t.fiscalyear', $year);
        }
        $this->applyQuarterFilterToQuery($base, $year, $quarter, 't.contact_start_date');
        if ($userId) {
            $base->where('t.user_id', $userId);
        }

        $total = (clone $base)->count('t.transac_id');

        $searchValue = trim((string) data_get($request->input('search'), 'value', ''));
        if ($searchValue !== '') {
            $base->where(function ($q) use ($searchValue) {
                $like = '%' . $searchValue . '%';
                $q->where('t.Product_detail', 'like', $like)
                    ->orWhere('c.company', 'like', $like)
                    ->orWhere('pg.product', 'like', $like)
                    ->orWhere('s.level', 'like', $like)
                    ->orWhere('pl.priority', 'like', $like)
                    ->orWhere('tc.team', 'like', $like)
                    ->orWhere('u.nname', 'like', $like)
                    ->orWhere('u.surename', 'like', $like)
                    ->orWhere('t.remark', 'like', $like);
            });
        }

        $filtered = (clone $base)->count('t.transac_id');

        $orderMap = [
            0 => 't.Product_detail',
            1 => 'c.company',
            2 => 't.product_value',
            3 => 's.level',
            4 => 'pl.priority',
            5 => 't.fiscalyear',
            6 => 't.contact_start_date',
            7 => 't.date_of_closing_of_sale',
            8 => 't.sales_can_be_close',
            9 => 'pg.product',
            10 => 'u.nname',
            11 => 'tc.team',
            12 => 't.remark',
        ];

        $orderCol = (int) data_get($request->input('order'), '0.column', 6);
        $orderDir = strtolower((string) data_get($request->input('order'), '0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $orderBy = $orderMap[$orderCol] ?? 't.updated_at';

        $rows = $base
            ->select([
                't.transac_id',
                't.Product_detail',
                't.product_value',
                't.fiscalyear',
                't.contact_start_date',
                't.date_of_closing_of_sale',
                't.sales_can_be_close',
                't.remark',
                'c.company',
                'pg.product as product_name',
                'tc.team',
                'pl.priority',
                'sb.Source_budge as source_budget',
                'u.nname',
                'u.surename',
                's.level as step_level',
            ])
            ->orderBy($orderBy, $orderDir)
            ->orderBy('t.transac_id', 'desc')
            ->offset($start)
            ->limit($length)
            ->get();

        $data = $rows->map(function ($r) {
            $id = (int) $r->transac_id;
            return [
                'id' => $id,
                'project' => $r->Product_detail,
                'company' => $r->company ?? '-',
                'value' => (float) $r->product_value,
                'status' => $r->step_level ?? '-',
                'priority' => $r->priority ?? '-',
                'year' => $r->fiscalyear ? ((int) $r->fiscalyear + 543) : '-',
                'start' => $r->contact_start_date,
                'bidding' => $r->date_of_closing_of_sale,
                'contract' => $r->sales_can_be_close,
                'product' => $r->product_name ?? '-',
                'user' => trim(($r->nname ?? '') . ' ' . ($r->surename ?? '')),
                'team' => $r->team ?? '-',
                'source' => $r->source_budget ?? '-',
                'contact_person' => '-',
                'contact_phone' => '-',
                'contact_email' => '-',
                'contact_note' => '-',
                'remark' => $r->remark ?? '-',
                'action' => '<a href="' . route('admin.sales.edit', $id) . '" class="btn btn-sm btn-info" title="แก้ไข"><i class="fas fa-pencil-alt"></i></a> '
                    . '<a href="' . route('admin.sales.transfer', $id) . '" class="btn btn-sm btn-warning" title="โอนข้อมูล"><i class="fas fa-exchange-alt"></i></a> '
                    . '<button type="button" class="btn btn-sm btn-danger js-delete-sale" title="ลบ" data-delete-url="' . route('admin.sales.delete', $id) . '"><i class="fas fa-trash"></i></button>',
            ];
        })->values();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data,
        ]);
    }

    public function editSales($id)
    {
        $transaction = Transactional::findOrFail($id);
        
        // Cache master data for 1 hour (3600 seconds)
        $companies = \Cache::remember('companies_list', 3600, function() {
            return DB::table('company_catalog')->orderBy('company')->get();
        });
        
        $products = \Cache::remember('products_list', 3600, function() {
            return DB::table('product_group')->orderBy('product')->get();
        });
        
        $priorities = \Cache::remember('priorities_list', 3600, function() {
            return DB::table('priority_level')->orderBy('priority')->get();
        });
        
        $sources = \Cache::remember('sources_list', 3600, function() {
            return DB::table('source_of_the_budget')->orderBy('Source_budge')->get();
        });
        
        $steps = \Cache::remember('steps_list', 3600, function() {
            return DB::table('step')->orderBy('level_id')->get();
        });
        
        $teams = \Cache::remember('teams_list', 3600, function() {
            return DB::table('team_catalog')->orderBy('team')->get();
        });
        
        $users = \Cache::remember('active_users_list', 600, function() {
            return User::where('role_id', 3)->orderBy('nname')->get();
        });
        
        // Get transaction steps (not cached as it's transaction-specific)
        $transactionSteps = DB::table('transactional_step')
            ->where('transac_id', $id)
            ->get()
            ->keyBy('level_id');
        
        return view('admin.sales.edit', compact(
            'transaction',
            'companies',
            'products',
            'priorities',
            'sources',
            'steps',
            'teams',
            'users',
            'transactionSteps'
        ));
    }

    public function updateSales(Request $request, $id)
    {
        $transaction = Transactional::findOrFail($id);
        
        // Validation
        $request->validate([
            'Product_detail' => 'required|max:255',
            'company_id' => 'required|integer',
            'product_value' => 'required',
            'Source_budget_id' => 'required|integer',
            'fiscalyear' => 'required|integer',
            'Product_id' => 'required|integer',
            'team_id' => 'required|integer',
            'user_id' => 'required|integer',
            'priority_id' => 'nullable|integer',
            'contact_start_date' => 'required|date',
            'date_of_closing_of_sale' => 'nullable|date',
            'sales_can_be_close' => 'nullable|date',
        ]);

        try {
            // Remove comma from product_value
            $productValue = str_replace(',', '', $request->product_value);

            // Get the highest step level selected
            $stepId = null;
            if ($request->has('step') && is_array($request->step)) {
                $selectedSteps = array_keys(array_filter($request->step));
                if (!empty($selectedSteps)) {
                    $stepId = max($selectedSteps);
                }
            }

            // Update transactional record
            $transaction->update([
                'user_id' => $request->user_id,
                'company_id' => $request->company_id,
                'Product_id' => $request->Product_id,
                'team_id' => $request->team_id,
                'priority_id' => $request->priority_id,
                'Source_budget_id' => $request->Source_budget_id,
                'Product_detail' => $request->Product_detail,
                'product_value' => $productValue,
                'fiscalyear' => $request->fiscalyear,
                'contact_start_date' => $request->contact_start_date,
                'date_of_closing_of_sale' => $request->date_of_closing_of_sale,
                'sales_can_be_close' => $request->sales_can_be_close,
                'remark' => $request->remark ?? '',
                'contact_person' => $request->contact_person,
                'contact_phone' => $request->contact_phone,
                'contact_email' => $request->contact_email,
                'contact_note' => $request->contact_note,
                'Step_id' => $stepId ?? $transaction->Step_id,
            ]);

            // Delete existing steps and recreate
            DB::table('transactional_step')->where('transac_id', $id)->delete();

            // Save transactional steps if any
            if ($request->has('step') && is_array($request->step)) {
                foreach ($request->step as $levelId => $value) {
                    if ($value && isset($request->step_date[$levelId]) && $request->step_date[$levelId]) {
                        DB::table('transactional_step')->insert([
                            'transac_id' => $id,
                            'level_id' => $levelId,
                            'date' => $request->step_date[$levelId],
                        ]);
                    }
                }
            }

            // Redirect based on user role
            $user = auth()->user();
            if ($user->role_id == 2) {
                // Team Admin
                return redirect()->route('teamadmin.dashboard.table')->with('success', 'อัพเดทข้อมูลเรียบร้อยแล้ว');
            } else {
                // Admin
                return redirect()->route('admin.dashboard.table')->with('success', 'อัพเดทข้อมูลเรียบร้อยแล้ว');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function deleteSales($id)
    {
        try {
            $transaction = Transactional::findOrFail($id);
            
            // Delete related steps first
            DB::table('transactional_step')->where('transac_id', $id)->delete();
            
            // Delete transaction
            $transaction->delete();

            return redirect()->route('admin.dashboard.table')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function transferSales($id)
    {
        $transaction = Transactional::with(['user', 'company', 'team'])->findOrFail($id);
        
        $users = User::where('role_id', 3)
            ->where('user_id', '!=', $transaction->user_id)
            ->where('is_active', 1)
            ->orderBy('nname')
            ->get();
        
        $teams = DB::table('team_catalog')->orderBy('team')->get();
        
        return view('admin.sales.transfer', compact('transaction', 'users', 'teams'));
    }

    public function processTransfer(Request $request, $id)
    {
        $request->validate([
            'to_user_id' => 'required|integer|exists:user,user_id',
            'transfer_reason' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();
            
            $transaction = Transactional::findOrFail($id);
            $currentUserId = $transaction->user_id;
            $currentTeamId = $transaction->team_id;
            
            if ($currentUserId == $request->to_user_id) {
                return redirect()->back()->with('error', 'ไม่สามารถโอนข้อมูลให้ผู้ใช้งานคนเดิมได้');
            }
            
            $toUser = User::findOrFail($request->to_user_id);
            $newTeamId = $toUser->team_id ?? $currentTeamId;
            
            $transaction->user_id = $request->to_user_id;
            $transaction->team_id = $newTeamId;
            $transaction->save();
            
            \App\Models\TransactionalTransferHistory::create([
                'transac_id' => $id,
                'from_user_id' => $currentUserId,
                'to_user_id' => $request->to_user_id,
                'transferred_by' => auth()->user()->user_id,
                'transfer_reason' => $request->transfer_reason,
                'old_team_id' => $currentTeamId,
                'new_team_id' => $newTeamId,
                'transferred_at' => now(),
            ]);
            
            DB::commit();
            
            return redirect()->route('admin.dashboard.table')->with('success', 'โอนข้อมูลเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function getTransferHistory($id)
    {
        $transaction = Transactional::with(['user', 'company'])->findOrFail($id);
        
        $transferHistory = \App\Models\TransactionalTransferHistory::with([
            'fromUser', 
            'toUser', 
            'transferredByUser',
            'oldTeam',
            'newTeam'
        ])
        ->where('transac_id', $id)
        ->orderBy('transferred_at', 'desc')
        ->get();
        
        return view('admin.sales.transfer_history', compact('transaction', 'transferHistory'));
    }

    public function profile()
    {
        $user = auth()->user();
        $roles = \App\Models\RoleCatalog::orderBy('role')->get();
        $positions = \App\Models\Position::orderBy('position')->get();
        $twoFactorEnabled = $user->two_factor_enabled;
        
        return view('admin.profile', compact('user', 'roles', 'positions', 'twoFactorEnabled'));
    }

    public function updateProfile(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'nname' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            $user->nname = $request->nname;
            $user->surename = $request->surname;

            if ($request->hasFile('avatar')) {
                $avatar = $request->file('avatar');
                
                // Additional security checks
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!in_array($avatar->getMimeType(), $allowedMimeTypes)) {
                    return redirect()->back()->with('error', 'ไฟล์ต้องเป็นรูปภาพ (JPEG, PNG, JPG) เท่านั้น');
                }
                
                // Check file size (max 2MB)
                if ($avatar->getSize() > 2048 * 1024) {
                    return redirect()->back()->with('error', 'ไฟล์มีขนาดใหญ่เกินไป (สูงสุด 2MB)');
                }
                
                // Generate secure filename
                $extension = $avatar->getClientOriginalExtension();
                $fileName = 'user_' . $user->user_id . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
                
                $uploadPath = public_path('uploads' . DIRECTORY_SEPARATOR . 'avatars');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                $avatar->move($uploadPath, $fileName);
                $user->avatar_path = 'uploads/avatars/' . $fileName;
            }

            $user->save();

            return redirect()->route('admin.profile')->with('success', 'อัปเดตโปรไฟล์เรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function toggleTwoFactor(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();
        
        $user->two_factor_enabled = !$user->two_factor_enabled;
        $user->save();
        
        $status = $user->two_factor_enabled ? 'เปิด' : 'ปิด';
        
        return response()->json([
            'success' => true,
            'enabled' => $user->two_factor_enabled,
            'message' => $status . 'การใช้งาน 2FA เรียบร้อยแล้ว'
        ]);
    }
    
    private function quarterDateRange($year, $quarter)
    {
        $y = (int) $year;
        $q = (int) $quarter;
        if ($y <= 0 || $q < 1 || $q > 4) {
            return null;
        }

        $startMonth = (($q - 1) * 3) + 1;
        $start = sprintf('%04d-%02d-01', $y, $startMonth);

        $endMonth = $startMonth + 3;
        $endYear = $y;
        if ($endMonth > 12) {
            $endMonth -= 12;
            $endYear++;
        }
        $end = sprintf('%04d-%02d-01', $endYear, $endMonth);

        return [$start, $end];
    }

    private function applyQuarterFilterToQuery($query, $year, $quarter, $column = 'contact_start_date')
    {
        if (!$quarter) {
            return;
        }

        $range = $this->quarterDateRange($year, $quarter);
        if ($range) {
            $query->where($column, '>=', $range[0])
                ->where($column, '<', $range[1]);
            return;
        }

        $query->whereRaw("QUARTER({$column}) = ?", [$quarter]);
    }

    private function appendQuarterSqlFilter(&$where, &$params, $year, $quarter, $alias = 't')
    {
        if (!$quarter) {
            return;
        }

        $range = $this->quarterDateRange($year, $quarter);
        if ($range) {
            $where .= " AND {$alias}.contact_start_date >= ? AND {$alias}.contact_start_date < ?";
            $params[] = $range[0];
            $params[] = $range[1];
            return;
        }

        $where .= " AND QUARTER({$alias}.contact_start_date) = ?";
        $params[] = $quarter;
    }

    /**
     * Build WHERE clause for year and quarter filters
     */
    private function buildFilterWhere($year, $quarter, $alias = 't')
    {
        $where = [];
        $params = [];

        if ($year) {
            $where[] = "{$alias}.fiscalyear = ?";
            $params[] = $year;
        }

        $quarterSql = '';
        $quarterParams = [];
        $this->appendQuarterSqlFilter($quarterSql, $quarterParams, $year, $quarter, $alias);
        if ($quarterSql !== '') {
            $where[] = ltrim(str_replace('AND', '', $quarterSql));
            $params = array_merge($params, $quarterParams);
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        return ['where' => $whereClause, 'params' => $params];
    }
}
