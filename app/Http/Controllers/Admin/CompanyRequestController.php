<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CompanyRequest;

class CompanyRequestController extends Controller
{
    public function index()
    {
        $requests = CompanyRequest::with('user')
            ->orderBy('request_date', 'desc')
            ->get();
        
        return view('admin.company-requests.index', compact('requests'));
    }

    public function approve($id)
    {
        $request = CompanyRequest::findOrFail($id);
        
        // Check if company already exists
        $existingCompany = DB::table('company_catalog')
            ->where('company', $request->company_name)
            ->first();
        
        if ($existingCompany) {
            return redirect()->back()->with('error', 'บริษัทนี้มีอยู่ในระบบแล้ว');
        }
        
        // Add company to catalog (default industry_id = 1 or first available)
        $firstIndustry = DB::table('industry_group')->first();
        DB::table('company_catalog')->insert([
            'company' => $request->company_name,
            'Industry_id' => $firstIndustry->Industry_id ?? 1,
        ]);
        
        // Update request status
        $request->update(['status' => 'approved']);
        
        return redirect()->route('admin.company-requests.index')
            ->with('success', 'อนุมัติคำขอและเพิ่มบริษัทเรียบร้อยแล้ว');
    }

    public function reject($id)
    {
        $request = CompanyRequest::findOrFail($id);
        $request->update(['status' => 'rejected']);
        
        return redirect()->route('admin.company-requests.index')
            ->with('success', 'ปฏิเสธคำขอเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $request = CompanyRequest::findOrFail($id);
        $request->delete();
        
        return redirect()->route('admin.company-requests.index')
            ->with('success', 'ลบคำขอเรียบร้อยแล้ว');
    }
}
