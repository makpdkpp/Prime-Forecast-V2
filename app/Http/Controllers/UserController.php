<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\Transactional;
use App\Models\TransactionalStep;
use App\Models\CompanyCatalog;
use App\Models\ProductGroup;
use App\Models\PriorityLevel;
use App\Models\SourceBudget;
use App\Models\Step;
use App\Models\TeamCatalog;
use App\Models\TransactionalTeam;
use App\Models\RoleCatalog;
use App\Models\Position;
use App\Models\CompanyRequest;
use App\Models\User;

class UserController extends Controller
{
    public function dashboard(Request $request)
    {
        $userId = auth()->id();
        
        // Get filter parameters
        $year = $request->get('year');
        $quarter = $request->get('quarter');
        
        // Get available years from transactional data
        $availableYears = DB::table('transactional')
            ->where('user_id', $userId)
            ->select('fiscalyear')
            ->distinct()
            ->orderBy('fiscalyear', 'desc')
            ->pluck('fiscalyear');
        
        // Load data for charts from the database
        $saleStepData = $this->getSaleStepData($userId, $year, $quarter);
        $winForecastData = $this->getWinForecastData($userId, $year, $quarter);
        $sumValuePercentData = $this->getSumValuePercentData($userId, $year, $quarter);
        
        return view('user.dashboard', compact('saleStepData', 'winForecastData', 'sumValuePercentData', 'availableYears', 'year', 'quarter'));
    }

    public function dashboardTable(Request $request)
    {
        $userId = auth()->id();
        
        // Get filter parameters
        $year = $request->get('year');
        $quarter = $request->get('quarter');
        
        // Get available years
        $availableYears = DB::table('transactional')
            ->where('user_id', $userId)
            ->select('fiscalyear')
            ->distinct()
            ->orderBy('fiscalyear', 'desc')
            ->pluck('fiscalyear');
        
        // Build query with filters and eager load all relationships
        $query = Transactional::with([
            'company', 
            'productGroup', 
            'team', 
            'priority', 
            'sourceBudget',
            'latestStep.step'  // Fix N+1 query problem
        ])
            ->where('user_id', $userId);
        
        if ($year) {
            $query->where('fiscalyear', $year);
        }
        
        if ($quarter) {
            $query->whereRaw('QUARTER(contact_start_date) = ?', [$quarter]);
        }
        
        $transactions = $query->orderBy('updated_at', 'desc')
            ->orderBy('transac_id', 'desc')
            ->get();
        
        return view('user.dashboard_table', compact('transactions', 'availableYears', 'year', 'quarter'));
    }

    public function createSales()
    {
        // Cache master data for 1 hour
        $companies = \Cache::remember('companies_list', 3600, function() {
            return CompanyCatalog::orderBy('company')->get();
        });
        
        $products = \Cache::remember('products_list', 3600, function() {
            return ProductGroup::orderBy('product')->get();
        });
        
        $priorities = \Cache::remember('priorities_list', 3600, function() {
            return PriorityLevel::orderBy('priority')->get();
        });
        
        $sources = \Cache::remember('sources_list', 3600, function() {
            return SourceBudget::orderBy('Source_budge')->get();
        });
        
        $steps = \Cache::remember('steps_list', 3600, function() {
            return Step::orderBy('orderlv')->get();
        });
        
        // Get teams for this user (user-specific, not cached)
        $teamIds = TransactionalTeam::where('user_id', auth()->id())->pluck('team_id');
        $teams = TeamCatalog::whereIn('team_id', $teamIds)->orderBy('team')->get();
        
        return view('user.sales.create', compact('companies', 'products', 'priorities', 'sources', 'steps', 'teams'));
    }

    public function storeSales(Request $request)
    {
        // Validation - simplified for better performance
        $validated = $request->validate([
            'Product_detail' => 'required|max:255',
            'company_id' => 'required|integer',
            'product_value' => 'required',
            'Source_budget_id' => 'required|integer',
            'fiscalyear' => 'required|integer',
            'Product_id' => 'required|integer',
            'team_id' => 'required|integer',
            'priority_id' => 'nullable|integer',
            'contact_start_date' => 'required|date',
            'date_of_closing_of_sale' => 'nullable|date',
            'sales_can_be_close' => 'nullable|date',
        ]);

        // Remove comma from product_value and convert to number
        $productValue = str_replace(',', '', $request->product_value);

        // Get the highest step level selected
        $stepId = null;
        if ($request->has('step') && is_array($request->step)) {
            $selectedSteps = array_keys(array_filter($request->step));
            if (!empty($selectedSteps)) {
                $stepId = max($selectedSteps);
            }
        }

        // Create transactional record
        $transactional = Transactional::create([
            'user_id' => auth()->id(),
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
            'Step_id' => $stepId ?? 1,
            'present' => 0,
            'present_date' => null,
            'budgeted' => 0,
            'budgeted_date' => null,
            'tor' => 0,
            'tor_date' => null,
            'bidding' => 0,
            'bidding_date' => null,
            'win' => 0,
            'win_date' => null,
            'lost' => 0,
            'lost_date' => null,
        ]);

        // Save transactional steps if any
        if ($request->has('step') && is_array($request->step)) {
            foreach ($request->step as $levelId => $value) {
                if ($value && isset($request->step_date[$levelId]) && $request->step_date[$levelId]) {
                    TransactionalStep::create([
                        'transac_id' => $transactional->transac_id,
                        'level_id' => $levelId,
                        'date' => $request->step_date[$levelId],
                    ]);
                }
            }
        }

        return redirect()->route('user.dashboard')->with('success', 'บันทึกข้อมูลการขายเรียบร้อยแล้ว');
    }

    public function editSales($id)
    {
        $transaction = Transactional::where('transac_id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        
        // Cache master data for 1 hour
        $companies = \Cache::remember('companies_list', 3600, function() {
            return CompanyCatalog::orderBy('company')->get();
        });
        
        $products = \Cache::remember('products_list', 3600, function() {
            return ProductGroup::orderBy('product')->get();
        });
        
        $priorities = \Cache::remember('priorities_list', 3600, function() {
            return PriorityLevel::orderBy('priority')->get();
        });
        
        $sources = \Cache::remember('sources_list', 3600, function() {
            return SourceBudget::orderBy('Source_budge')->get();
        });
        
        $steps = \Cache::remember('steps_list', 3600, function() {
            return Step::orderBy('orderlv')->get();
        });
        
        // Get teams for this user (user-specific, not cached)
        $teamIds = TransactionalTeam::where('user_id', auth()->id())->pluck('team_id');
        $teams = TeamCatalog::whereIn('team_id', $teamIds)->orderBy('team')->get();
        
        // Get transaction steps (transaction-specific, not cached)
        $transactionSteps = TransactionalStep::where('transac_id', $id)
            ->get()
            ->keyBy('level_id');
        
        return view('user.sales.edit', compact(
            'transaction',
            'companies',
            'products',
            'priorities',
            'sources',
            'steps',
            'teams',
            'transactionSteps'
        ));
    }

    public function updateSales(Request $request, $id)
    {
        $transaction = Transactional::where('transac_id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        
        $request->validate([
            'Product_detail' => 'required|max:255',
            'company_id' => 'required|integer',
            'product_value' => 'required',
            'Source_budget_id' => 'required|integer',
            'fiscalyear' => 'required|integer',
            'Product_id' => 'required|integer',
            'team_id' => 'required|integer',
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

            TransactionalStep::where('transac_id', $id)->delete();

            if ($request->has('step') && is_array($request->step)) {
                foreach ($request->step as $levelId => $value) {
                    if ($value && isset($request->step_date[$levelId]) && $request->step_date[$levelId]) {
                        TransactionalStep::create([
                            'transac_id' => $id,
                            'level_id' => $levelId,
                            'date' => $request->step_date[$levelId],
                        ]);
                    }
                }
            }

            return redirect()->route('user.dashboard.table')->with('success', 'อัพเดทข้อมูลเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function profile()
    {
        $user = auth()->user();
        $roles = RoleCatalog::orderBy('role')->get();
        $positions = Position::orderBy('position')->get();
        $twoFactorEnabled = $user->two_factor_enabled;
        
        return view('user.profile', compact('user', 'roles', 'positions', 'twoFactorEnabled'));
    }

    public function toggleTwoFactor(Request $request)
    {
        $user = auth()->user();
        
        // Toggle 2FA status
        $user->two_factor_enabled = !$user->two_factor_enabled;
        $user->save();
        
        $status = $user->two_factor_enabled ? 'เปิด' : 'ปิด';
        
        return response()->json([
            'success' => true,
            'enabled' => $user->two_factor_enabled,
            'message' => $status . 'การใช้งาน 2FA เรียบร้อยแล้ว'
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        // Validation
        $request->validate([
            'nname' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            // Update name and surname
            $user->nname = $request->nname;
            $user->surename = $request->surname;

            // Handle avatar upload
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
                
                // Create directory if not exists (cross-platform)
                $uploadPath = public_path('uploads' . DIRECTORY_SEPARATOR . 'avatars');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                // Move uploaded file
                $avatar->move($uploadPath, $fileName);
                
                // Update avatar path (use forward slash for web path)
                $user->avatar_path = 'uploads/avatars/' . $fileName;
            }

            $user->save();

            return redirect()->route('user.profile')->with('success', 'บันทึกข้อมูลสำเร็จ');
        } catch (\Exception $e) {
            return redirect()->route('user.profile')->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function requestCompany(Request $request)
    {
        // Validation
        $request->validate([
            'company_name' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        try {
            // Create company request
            $companyRequest = CompanyRequest::create([
                'company_name' => $request->company_name,
                'notes' => $request->notes ?? '',
                'user_id' => auth()->id(),
                'request_date' => now(),
                'status' => 'pending',
            ]);

            // Get all Super Admin emails (role_id = 1)
            $adminEmails = User::where('role_id', 1)->pluck('email')->toArray();

            // Send email notification to admins
            if (!empty($adminEmails)) {
                $userEmail = auth()->user()->email;
                $companyName = $request->company_name;
                $notes = $request->notes ?? '';

                Mail::send([], [], function ($message) use ($adminEmails, $userEmail, $companyName, $notes) {
                    $message->to($adminEmails)
                        ->subject('มีคำขอเพิ่มบริษัทใหม่')
                        ->from('no-reply@primeforecast.com', 'PrimeForecast System')
                        ->html("
                            <h2>มีคำขอเพิ่มบริษัทใหม่เข้าระบบ</h2>
                            <p>คุณ <strong>{$userEmail}</strong> ได้ส่งคำขอเพิ่มข้อมูลบริษัทใหม่ ดังนี้:</p>
                            <hr>
                            <p><strong>ชื่อบริษัทที่ขอเพิ่ม:</strong> {$companyName}</p>
                            <p><strong>รายละเอียดเพิ่มเติม:</strong><br>{$notes}</p>
                            <hr>
                            <p>กรุณาเข้าระบบเพื่อตรวจสอบและดำเนินการอนุมัติคำขอนี้</p>
                        ");
                });
            }

            return response()->json([
                'success' => true,
                'message' => 'คำขอถูกบันทึกแล้ว ระบบจะแจ้งเตือนผู้ดูแลระบบให้ตรวจสอบ'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getSaleStepData($userId, $year = null, $quarter = null)
    {
        $params = [$userId];
        $extraWhere = "";
        
        if ($year) {
            $extraWhere .= ' AND t.fiscalyear = ?';
            $params[] = $year;
        }
        
        if ($quarter) {
            $extraWhere .= ' AND QUARTER(t.contact_start_date) = ?';
            $params[] = $quarter;
        }
        
        return DB::select("
            SELECT 
                DATE_FORMAT(ts.date, '%Y-%m') as month,
                SUM(CASE WHEN s.orderlv = 1 THEN t.product_value ELSE 0 END) as present_value,
                SUM(CASE WHEN s.orderlv = 2 THEN t.product_value ELSE 0 END) as budgeted_value,
                SUM(CASE WHEN s.orderlv = 3 THEN t.product_value ELSE 0 END) as tor_value,
                SUM(CASE WHEN s.orderlv = 4 THEN t.product_value ELSE 0 END) as bidding_value,
                SUM(CASE WHEN s.orderlv = 5 THEN t.product_value ELSE 0 END) as win_value,
                SUM(CASE WHEN s.orderlv = 6 THEN t.product_value ELSE 0 END) as lost_value
            FROM transactional t
            JOIN transactional_step ts ON t.transac_id = ts.transac_id
            JOIN step s ON s.level_id = ts.level_id
            WHERE (ts.transacstep_id, ts.transac_id) IN (
                SELECT MAX(ts2.transacstep_id), ts2.transac_id
                FROM transactional_step ts2
                GROUP BY ts2.transac_id
            )
            AND t.user_id = ?
            {$extraWhere}
            GROUP BY DATE_FORMAT(ts.date, '%Y-%m')
            ORDER BY month
        ", $params);
    }

    private function getWinForecastData($userId, $year = null, $quarter = null)
    {
        // Target from user_forecast_target
        $targetParams = [$userId];
        $targetWhere = "";
        if ($year) {
            $targetWhere .= " AND fiscal_year = ?";
            $targetParams[] = $year;
        }
        $targetResult = DB::select("
            SELECT COALESCE(SUM(target_value), 0) as target_value
            FROM user_forecast_target
            WHERE user_id = ? {$targetWhere}
        ", $targetParams);
        $target = $targetResult[0]->target_value ?? 0;

        // Forecast = sum of all transactions for this user
        $forecastParams = [$userId];
        $forecastWhere = "";
        if ($year) {
            $forecastWhere .= " AND t.fiscalyear = ?";
            $forecastParams[] = $year;
        }
        if ($quarter) {
            $forecastWhere .= " AND QUARTER(t.contact_start_date) = ?";
            $forecastParams[] = $quarter;
        }
        $forecastResult = DB::select("
            SELECT COALESCE(SUM(t.product_value), 0) as forecast_value
            FROM transactional t
            WHERE t.user_id = ? {$forecastWhere}
        ", $forecastParams);
        $forecast = $forecastResult[0]->forecast_value ?? 0;

        // Win = sum of transactions whose latest step is WIN (level = 5)
        $winParams = [$userId];
        $winWhere = "";
        if ($year) {
            $winWhere .= " AND t.fiscalyear = ?";
            $winParams[] = $year;
        }
        if ($quarter) {
            $winWhere .= " AND QUARTER(t.contact_start_date) = ?";
            $winParams[] = $quarter;
        }
        $winResult = DB::select("
            SELECT COALESCE(SUM(t.product_value), 0) as win_value
            FROM transactional t
            JOIN (
                SELECT ts.transac_id
                FROM transactional_step ts
                JOIN step s ON s.level_id = ts.level_id
                WHERE s.orderlv = 5
                AND (ts.transacstep_id, ts.transac_id) IN (
                    SELECT MAX(ts2.transacstep_id), ts2.transac_id
                    FROM transactional_step ts2
                    GROUP BY ts2.transac_id
                )
            ) wintrans ON wintrans.transac_id = t.transac_id
            WHERE t.user_id = ? {$winWhere}
        ", $winParams);
        $win = $winResult[0]->win_value ?? 0;

        return (object)[
            'Target' => $target,
            'Forecast' => $forecast,
            'Win' => $win,
        ];
    }

    private function getSumValuePercentData($userId, $year = null, $quarter = null)
    {
        $params = [$userId];
        $whereClause = 'WHERE t.user_id = ?';
        
        if ($year) {
            $whereClause .= ' AND t.fiscalyear = ?';
            $params[] = $year;
        }
        
        if ($quarter) {
            $whereClause .= ' AND QUARTER(t.contact_start_date) = ?';
            $params[] = $quarter;
        }
        
        return DB::select("
            SELECT 
                pg.product,
                SUM(t.product_value) as sum_value
            FROM transactional t
            JOIN product_group pg ON pg.product_id = t.Product_id
            {$whereClause}
            GROUP BY pg.product_id, pg.product
        ", $params);
    }
}
