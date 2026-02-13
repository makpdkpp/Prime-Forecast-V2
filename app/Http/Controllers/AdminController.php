<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $availableYears = DB::table('transactional')
            ->select('fiscalyear')
            ->distinct()
            ->orderBy('fiscalyear', 'desc')
            ->pluck('fiscalyear');
        
        // Build base query with filters
        $query = DB::table('transactional');
        
        if ($year) {
            $query->where('fiscalyear', $year);
        }
        
        if ($quarter) {
            $query->whereRaw('QUARTER(contact_start_date) = ?', [$quarter]);
        }
        
        // Get summary statistics
        $estimateValue = (clone $query)->sum('product_value');
        
        // Win value and count (latest step is WIN - level = 5)
        $winParams = [];
        $winWhere = "";
        if ($year) {
            $winWhere .= " AND t.fiscalyear = ?";
            $winParams[] = $year;
        }
        if ($quarter) {
            $winWhere .= " AND QUARTER(t.contact_start_date) = ?";
            $winParams[] = $quarter;
        }
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
        if ($quarter) {
            $lostWhere .= " AND QUARTER(t.contact_start_date) = ?";
            $lostParams[] = $quarter;
        }
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
        if ($quarter) {
            $cumulativeWinWhere .= " AND QUARTER(t.contact_start_date) = ?";
            $cumulativeWinParams[] = $quarter;
        }
        $cumulativeWin = DB::select("
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
        
        // Sum by team (only transactions whose latest step is WIN - level = 5)
        $sumByTeamParams = [];
        $sumByTeamWhere = "";
        if ($year) {
            $sumByTeamWhere .= " AND t.fiscalyear = ?";
            $sumByTeamParams[] = $year;
        }
        if ($quarter) {
            $sumByTeamWhere .= " AND QUARTER(t.contact_start_date) = ?";
            $sumByTeamParams[] = $quarter;
        }
        $sumByTeam = DB::select("
            SELECT 
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
        
        // Sum by person (only transactions whose latest step is WIN - level = 5)
        $sumByPersonParams = [];
        $sumByPersonWhere = "";
        if ($year) {
            $sumByPersonWhere .= " AND t.fiscalyear = ?";
            $sumByPersonParams[] = $year;
        }
        if ($quarter) {
            $sumByPersonWhere .= " AND QUARTER(t.contact_start_date) = ?";
            $sumByPersonParams[] = $quarter;
        }
        $sumByPerson = DB::select("
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
        
        // Sale status count by month and step level
        $saleStatusParams = [];
        $saleStatusWhere = "";
        if ($year) {
            $saleStatusWhere .= " AND t.fiscalyear = ?";
            $saleStatusParams[] = $year;
        }
        if ($quarter) {
            $saleStatusWhere .= " AND QUARTER(t.contact_start_date) = ?";
            $saleStatusParams[] = $quarter;
        }
        $saleStatus = DB::select("
            SELECT 
                DATE_FORMAT(ts.date, '%Y-%m') as sale_month,
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
            GROUP BY DATE_FORMAT(ts.date, '%Y-%m'), s.orderlv, s.level
            ORDER BY sale_month, s.orderlv
        ", $saleStatusParams);
        
        // Sale status value by month and step level
        $saleStatusValueParams = [];
        $saleStatusValueWhere = "";
        if ($year) {
            $saleStatusValueWhere .= " AND t.fiscalyear = ?";
            $saleStatusValueParams[] = $year;
        }
        if ($quarter) {
            $saleStatusValueWhere .= " AND QUARTER(t.contact_start_date) = ?";
            $saleStatusValueParams[] = $quarter;
        }
        $saleStatusValue = DB::select("
            SELECT 
                DATE_FORMAT(ts.date, '%Y-%m') as sale_month,
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
            GROUP BY DATE_FORMAT(ts.date, '%Y-%m'), s.orderlv, s.level
            ORDER BY sale_month, s.orderlv
        ", $saleStatusValueParams);
        
        // Top 10 products (only WIN - level = 5)
        $topProductsParams = [];
        $topProductsWhere = "";
        if ($year) {
            $topProductsWhere .= " AND t.fiscalyear = ?";
            $topProductsParams[] = $year;
        }
        if ($quarter) {
            $topProductsWhere .= " AND QUARTER(t.contact_start_date) = ?";
            $topProductsParams[] = $quarter;
        }
        $topProducts = DB::select("
            SELECT 
                pg.product,
                SUM(t.product_value) as total_value
            FROM transactional t
            JOIN product_group pg ON t.Product_id = pg.Product_id
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
        
        // Top 10 customers (only WIN - level = 5)
        $topCustomersParams = [];
        $topCustomersWhere = "";
        if ($year) {
            $topCustomersWhere .= " AND t.fiscalyear = ?";
            $topCustomersParams[] = $year;
        }
        if ($quarter) {
            $topCustomersWhere .= " AND QUARTER(t.contact_start_date) = ?";
            $topCustomersParams[] = $quarter;
        }
        $topCustomers = DB::select("
            SELECT 
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
        
        // Target/Forecast/Win comparison per user
        $tfwParams = [];
        $tfwWhere = "";
        $tfwTargetWhere = "";
        if ($year) {
            $tfwWhere .= " AND t.fiscalyear = ?";
            $tfwTargetWhere .= " AND uft.fiscal_year = ?";
            $tfwParams[] = $year;
        }
        if ($quarter) {
            $tfwWhere .= " AND QUARTER(t.contact_start_date) = ?";
        }
        // Build params for forecast subquery
        $forecastParams = [];
        if ($year) $forecastParams[] = $year;
        if ($quarter) $forecastParams[] = $quarter;
        // Build params for win subquery
        $winParams = $forecastParams;
        // Build params for target subquery
        $targetParams = [];
        if ($year) $targetParams[] = $year;

        $targetForecastWin = DB::select("
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
        if ($quarter) {
            $where .= " AND QUARTER(t.contact_start_date) = ?";
            $params[] = $quarter;
        }

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
        
        // Get available years
        $availableYears = DB::table('transactional')
            ->select('fiscalyear')
            ->distinct()
            ->orderBy('fiscalyear', 'desc')
            ->pluck('fiscalyear');
        
        // Use Eloquent with eager loading to prevent N+1 queries
        $query = Transactional::with([
            'company',
            'productGroup',
            'team',
            'priority',
            'user',
            'sourceBudget',
            'latestStep.step'
        ]);
        
        // Apply filters
        if ($year) {
            $query->where('fiscalyear', $year);
        }
        
        if ($quarter) {
            $query->whereRaw('QUARTER(contact_start_date) = ?', [$quarter]);
        }
        
        // Get transactions
        $transactions = $query->orderBy('updated_at', 'desc')
            ->orderBy('transac_id', 'desc')
            ->get();

        return view('admin.dashboard_table', compact('transactions', 'availableYears', 'year', 'quarter'));
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
        
        if ($quarter) {
            $where[] = "QUARTER({$alias}.contact_start_date) = ?";
            $params[] = $quarter;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        return ['where' => $whereClause, 'params' => $params];
    }
}
