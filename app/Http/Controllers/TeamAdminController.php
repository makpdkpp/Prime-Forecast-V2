<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TeamAdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        
        // Get filter parameters
        $year = $request->get('year');
        $quarter = $request->get('quarter');
        
        // Get available years from transactional data
        $availableYears = Cache::remember('dashboard:teamadmin:availableYears', 120, function () {
            return DB::table('transactional')
                ->select('fiscalyear')
                ->distinct()
                ->orderBy('fiscalyear', 'desc')
                ->pluck('fiscalyear');
        });
        
        // Get user's teams
        $userTeams = DB::table('transactional_team')
            ->where('user_id', $user->user_id)
            ->pluck('team_id')
            ->toArray();
        
        if (empty($userTeams)) {
            return view('teamadmin.dashboard', compact('availableYears', 'year', 'quarter'))
                ->with('error', 'คุณยังไม่ได้รับมอบหมายทีม');
        }
        
        // Build base query with filters
        $query = DB::table('transactional')->whereIn('team_id', $userTeams);
        
        if ($year) {
            $query->where('fiscalyear', $year);
        }

        $this->applyQuarterFilterToQuery($query, $year, $quarter);
        
        // Summary statistics for user's teams
        $estimateValue = (clone $query)->sum('product_value');
        
        $winQuery = (clone $query)->where('win', 1);
        $winData = $winQuery->selectRaw('SUM(product_value) as total, COUNT(*) as count')->first();
        
        $winValue = $winData->total ?? 0;
        $winCount = $winData->count ?? 0;
        
        $lostCount = (clone $query)->where('lost', 1)->count();
        
        // Cumulative win by month
        $teamKey = md5(implode(',', $userTeams));
        $cumulativeWin = Cache::remember('dashboard:teamadmin:cumulativeWin:' . $user->user_id . ':' . $teamKey . ':' . ($year ?? 'all') . ':' . ($quarter ?? 'all'), 120, function () use ($userTeams, $year, $quarter) {
            $query = DB::table('transactional as t')
                ->join('transactional_step as ts', 't.transac_id', '=', 'ts.transac_id')
                ->join('step as s', 's.level_id', '=', 'ts.level_id')
                ->whereIn('t.team_id', $userTeams)
                ->where('s.level', 5);

            if ($year) {
                $query->where('t.fiscalyear', $year);
            }
            $this->applyQuarterFilterToQuery($query, $year, $quarter, 't.contact_start_date');

            return $query
                ->selectRaw('DATE_FORMAT(ts.date, "%Y-%m") as sale_month, SUM(t.product_value) as cumulative_win_value')
                ->groupBy('sale_month')
                ->orderBy('sale_month')
                ->get();
        });
        
        // Sum by team
        $sumByTeam = DB::table('transactional as t')
            ->join('team_catalog as tc', 't.team_id', '=', 'tc.team_id')
            ->whereIn('t.team_id', $userTeams)
            ->selectRaw('tc.team_id, tc.team, SUM(t.product_value) as total_value')
            ->groupBy('tc.team_id', 'tc.team')
            ->orderBy('total_value', 'desc')
            ->get();
        
        // Sales by person in teams
        $sumByPerson = DB::table('transactional as t')
            ->join('user as u', 't.user_id', '=', 'u.user_id')
            ->whereIn('t.team_id', $userTeams)
            ->selectRaw('u.user_id, u.nname, u.surename, SUM(t.product_value) as total_value')
            ->groupBy('t.user_id', 'u.user_id', 'u.nname', 'u.surename')
            ->orderBy('total_value', 'desc')
            ->get();
        
        // Sales status distribution (count)
        $saleStatus = DB::table('transactional as t')
            ->leftJoin('step as s', 't.Step_id', '=', 's.level_id')
            ->whereIn('t.team_id', $userTeams)
            ->selectRaw('COALESCE(s.level, 0) as level, s.orderlv, COUNT(*) as count')
            ->groupBy('s.level', 's.orderlv')
            ->get();
        
        // Sales status distribution (value)
        $saleStatusValue = DB::table('transactional as t')
            ->leftJoin('step as s', 't.Step_id', '=', 's.level_id')
            ->whereIn('t.team_id', $userTeams)
            ->selectRaw('COALESCE(s.level, 0) as level, s.orderlv, SUM(t.product_value) as total_value')
            ->groupBy('s.level', 's.orderlv')
            ->get();
        
        // Top 10 products
        $topProducts = DB::table('transactional as t')
            ->join('product_group as p', 't.Product_id', '=', 'p.product_id')
            ->whereIn('t.team_id', $userTeams)
            ->selectRaw('p.product_id, p.product, SUM(t.product_value) as total_value')
            ->groupBy('p.product_id', 'p.product')
            ->orderBy('total_value', 'desc')
            ->limit(10)
            ->get();
        
        // Top 10 customers
        $topCustomers = DB::table('transactional as t')
            ->join('company_catalog as c', 't.company_id', '=', 'c.company_id')
            ->whereIn('t.team_id', $userTeams)
            ->selectRaw('c.company_id, c.company, SUM(t.product_value) as total_value')
            ->groupBy('c.company_id', 'c.company')
            ->orderBy('total_value', 'desc')
            ->limit(10)
            ->get();
        
        return view('teamadmin.dashboard', compact(
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
            'availableYears',
            'year',
            'quarter'
        ));
    }

    public function chartDetail(Request $request)
    {
        $user = Auth::user();
        $type = $request->get('type');
        $value = $request->get('value');
        $value2 = $request->get('value2');
        $year = $request->get('year');
        $quarter = $request->get('quarter');

        $userTeams = DB::table('transactional_team')
            ->where('user_id', $user->user_id)
            ->pluck('team_id')
            ->toArray();

        if (empty($userTeams)) {
            return response()->json([]);
        }

        $teamPlaceholders = implode(',', array_fill(0, count($userTeams), '?'));
        $params = $userTeams;
        $where = " AND t.team_id IN ({$teamPlaceholders})";

        if ($year) {
            $where .= " AND t.fiscalyear = ?";
            $params[] = $year;
        }
        $this->appendQuarterSqlFilter($where, $params, $year, $quarter, 't');

        $extraJoin = "";
        $extraWhere = "";
        $extraParams = [];

        // Step subquery for latest step
        $stepJoin = "
            JOIN transactional_step ts ON t.transac_id = ts.transac_id
            JOIN step s ON s.level_id = ts.level_id
            AND (ts.transacstep_id, ts.transac_id) IN (
                SELECT MAX(ts2.transacstep_id), ts2.transac_id
                FROM transactional_step ts2
                GROUP BY ts2.transac_id
            )
        ";

        switch ($type) {
            case 'month': // cumulative win by month
                $extraJoin = "
                    JOIN transactional_step ts_w ON ts_w.transac_id = t.transac_id
                    JOIN step s_w ON s_w.level_id = ts_w.level_id AND s_w.level = 5";
                $extraWhere = " AND DATE_FORMAT(ts_w.date, '%Y-%m') = ?";
                $extraParams[] = $value;
                break;
            case 'team':
                $extraWhere = " AND t.team_id = ?";
                $extraParams[] = $value;
                break;
            case 'user':
                $extraWhere = " AND t.user_id = ?";
                $extraParams[] = $value;
                break;
            case 'step':
                $extraJoin = $stepJoin;
                $extraWhere = " AND s.orderlv = ?";
                $extraParams[] = $value;
                break;
            case 'product':
                $extraWhere = " AND t.Product_id = ?";
                $extraParams[] = $value;
                break;
            case 'company':
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

    public function dashboardTable(Request $request)
    {
        $user = Auth::user();
        
        // Get filter parameters
        $year = $request->get('year');
        $quarter = $request->get('quarter');
        $userId = $request->get('user_id');
        
        // Get available years
        $availableYears = Cache::remember('dashboard:teamadmin:table:availableYears', 120, function () {
            return DB::table('transactional')
                ->select('fiscalyear')
                ->distinct()
                ->orderBy('fiscalyear', 'desc')
                ->pluck('fiscalyear');
        });
        
        // Get user's teams
        $userTeams = DB::table('transactional_team')
            ->where('user_id', $user->user_id)
            ->pluck('team_id')
            ->toArray();

        $availableUsers = collect();
        if (!empty($userTeams)) {
            $availableUsers = DB::table('user as u')
                ->join('transactional as t', 'u.user_id', '=', 't.user_id')
                ->whereIn('t.team_id', $userTeams)
                ->where('u.role_id', 3)
                ->select('u.user_id', 'u.nname', 'u.surename')
                ->distinct()
                ->orderBy('u.nname')
                ->orderBy('u.surename')
                ->get();
        }

        return view('teamadmin.dashboard_table', compact('availableYears', 'availableUsers', 'year', 'quarter', 'userId', 'userTeams'));
    }

    public function dashboardTableData(Request $request)
    {
        $user = Auth::user();
        $year = $request->get('year');
        $quarter = $request->get('quarter');
        $userId = $request->get('user_id');

        $userTeams = DB::table('transactional_team')
            ->where('user_id', $user->user_id)
            ->pluck('team_id')
            ->toArray();

        if (empty($userTeams)) {
            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

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
            ->leftJoin('step as s', 't.Step_id', '=', 's.level_id')
            ->whereIn('t.team_id', $userTeams);

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
                'action' => '<a href="' . route('teamadmin.sales.edit', $id) . '" class="btn btn-sm btn-info" title="แก้ไข"><i class="fas fa-pencil-alt"></i></a>',
            ];
        })->values();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data,
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

    public function profile()
    {
        $user = auth()->user();
        $roles = \App\Models\RoleCatalog::orderBy('role')->get();
        $positions = \App\Models\Position::orderBy('position')->get();
        $twoFactorEnabled = $user->two_factor_enabled;
        
        return view('teamadmin.profile', compact('user', 'roles', 'positions', 'twoFactorEnabled'));
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

            return redirect()->route('teamadmin.profile')->with('success', 'อัปเดตโปรไฟล์เรียบร้อยแล้ว');
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

    public function editSales($id)
    {
        $user = Auth::user();
        
        // Get user's teams
        $userTeams = DB::table('transactional_team')
            ->where('user_id', $user->user_id)
            ->pluck('team_id')
            ->toArray();
        
        // Find transaction and verify it belongs to user's team
        $transaction = \App\Models\Transactional::whereIn('team_id', $userTeams)
            ->findOrFail($id);
        
        // Cache master data for 1 hour
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
            return \App\Models\User::where('role_id', 3)->orderBy('nname')->get();
        });
        
        // Get transaction steps
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

    public function updateSales(\Illuminate\Http\Request $request, $id)
    {
        $user = Auth::user();
        
        // Get user's teams
        $userTeams = DB::table('transactional_team')
            ->where('user_id', $user->user_id)
            ->pluck('team_id')
            ->toArray();
        
        // Find transaction and verify it belongs to user's team
        $transaction = \App\Models\Transactional::whereIn('team_id', $userTeams)
            ->findOrFail($id);
        
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
            'step_date' => 'nullable|array',
            'step_date.*' => 'nullable|date',
        ]);

        try {
            $productValue = str_replace(',', '', $request->product_value);

            $stepId = null;
            if ($request->has('step') && is_array($request->step)) {
                $selectedSteps = array_keys(array_filter($request->step));
                if (!empty($selectedSteps)) {
                    $stepId = max($selectedSteps);
                }
            }

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

            DB::table('transactional_step')->where('transac_id', $id)->delete();

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

            return redirect()->route('teamadmin.dashboard.table')->with('success', 'อัพเดทข้อมูลเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
}
