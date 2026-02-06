<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TeamAdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        
        // Get filter parameters
        $year = $request->get('year');
        $quarter = $request->get('quarter');
        
        // Get available years from transactional data
        $availableYears = DB::table('transactional')
            ->select('fiscalyear')
            ->distinct()
            ->orderBy('fiscalyear', 'desc')
            ->pluck('fiscalyear');
        
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
        
        if ($quarter) {
            $query->whereRaw('QUARTER(contact_start_date) = ?', [$quarter]);
        }
        
        // Summary statistics for user's teams
        $estimateValue = (clone $query)->sum('product_value');
        
        $winQuery = (clone $query)->where('win', 1);
        $winData = $winQuery->selectRaw('SUM(product_value) as total, COUNT(*) as count')->first();
        
        $winValue = $winData->total ?? 0;
        $winCount = $winData->count ?? 0;
        
        $lostCount = (clone $query)->where('lost', 1)->count();
        
        // Cumulative win by month
        $cumulativeWin = DB::table('transactional as t')
            ->join('transactional_step as ts', 't.transac_id', '=', 'ts.transac_id')
            ->join('step as s', 's.level_id', '=', 'ts.level_id')
            ->whereIn('t.team_id', $userTeams)
            ->where('s.level', 5)
            ->selectRaw('DATE_FORMAT(ts.date, "%Y-%m") as sale_month, SUM(t.product_value) as cumulative_win_value')
            ->groupBy('sale_month')
            ->orderBy('sale_month')
            ->get();
        
        // Sum by team
        $sumByTeam = DB::table('transactional as t')
            ->join('team_catalog as tc', 't.team_id', '=', 'tc.team_id')
            ->whereIn('t.team_id', $userTeams)
            ->selectRaw('tc.team, SUM(t.product_value) as total')
            ->groupBy('tc.team')
            ->orderBy('total', 'desc')
            ->get();
        
        // Sales by person in teams (rename for consistency)
        $sumByPerson = DB::table('transactional as t')
            ->join('user as u', 't.user_id', '=', 'u.user_id')
            ->whereIn('t.team_id', $userTeams)
            ->selectRaw('CONCAT(u.nname, " ", u.surename) as name, SUM(t.product_value) as total')
            ->groupBy('t.user_id', 'u.nname', 'u.surename')
            ->orderBy('total', 'desc')
            ->get();
        
        // Sales status distribution (count)
        $saleStatus = DB::table('transactional as t')
            ->leftJoin('step as s', 't.Step_id', '=', 's.level_id')
            ->whereIn('t.team_id', $userTeams)
            ->selectRaw('COALESCE(s.level, 0) as level, COUNT(*) as count')
            ->groupBy('s.level')
            ->get();
        
        // Sales status distribution (value)
        $saleStatusValue = DB::table('transactional as t')
            ->leftJoin('step as s', 't.Step_id', '=', 's.level_id')
            ->whereIn('t.team_id', $userTeams)
            ->selectRaw('COALESCE(s.level, 0) as level, SUM(t.product_value) as total')
            ->groupBy('s.level')
            ->get();
        
        // Top 10 products
        $topProducts = DB::table('transactional as t')
            ->join('product_group as p', 't.Product_id', '=', 'p.product_id')
            ->whereIn('t.team_id', $userTeams)
            ->selectRaw('p.product, SUM(t.product_value) as total')
            ->groupBy('p.product')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();
        
        // Top 10 customers
        $topCustomers = DB::table('transactional as t')
            ->join('company_catalog as c', 't.company_id', '=', 'c.company_id')
            ->whereIn('t.team_id', $userTeams)
            ->selectRaw('c.company, SUM(t.product_value) as total')
            ->groupBy('c.company')
            ->orderBy('total', 'desc')
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

    public function dashboardTable(Request $request)
    {
        $user = Auth::user();
        
        // Get filter parameters
        $year = $request->get('year');
        $quarter = $request->get('quarter');
        
        // Get available years
        $availableYears = DB::table('transactional')
            ->select('fiscalyear')
            ->distinct()
            ->orderBy('fiscalyear', 'desc')
            ->pluck('fiscalyear');
        
        // Get user's teams
        $userTeams = DB::table('transactional_team')
            ->where('user_id', $user->user_id)
            ->pluck('team_id')
            ->toArray();
        
        // If no teams, return empty
        if (empty($userTeams)) {
            return view('teamadmin.dashboard_table', ['transactions' => [], 'availableYears' => $availableYears, 'year' => $year, 'quarter' => $quarter]);
        }
        
        // Use Eloquent with eager loading to prevent N+1 queries
        $query = \App\Models\Transactional::with([
            'company',
            'productGroup',
            'team',
            'priority',
            'user',
            'sourceBudget',
            'latestStep.step'
        ])->whereIn('team_id', $userTeams);
        
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
        
        return view('teamadmin.dashboard_table', compact('transactions', 'availableYears', 'year', 'quarter'));
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
