<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = DB::table('company_catalog as cc')
            ->leftJoin('industry_group as ig', 'cc.Industry_id', '=', 'ig.Industry_id')
            ->select('cc.*', 'ig.Industry')
            ->orderBy('cc.company')
            ->get();
        
        return view('admin.master.companies.index', compact('companies'));
    }

    public function create()
    {
        $industries = DB::table('industry_group')->orderBy('Industry')->get();
        return view('admin.master.companies.create', compact('industries'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company' => 'required|string|max:255',
            'Industry_id' => 'required|integer',
        ]);

        DB::table('company_catalog')->insert([
            'company' => $request->company,
            'Industry_id' => $request->Industry_id,
        ]);

        return redirect()->route('admin.companies.index')->with('success', 'เพิ่มข้อมูลบริษัทเรียบร้อยแล้ว');
    }

    public function edit($id)
    {
        $company = DB::table('company_catalog')->where('company_id', $id)->first();
        $industries = DB::table('industry_group')->orderBy('Industry')->get();
        
        return view('admin.master.companies.edit', compact('company', 'industries'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'company' => 'required|string|max:255',
            'Industry_id' => 'required|integer',
        ]);

        DB::table('company_catalog')
            ->where('company_id', $id)
            ->update([
                'company' => $request->company,
                'Industry_id' => $request->Industry_id,
            ]);

        return redirect()->route('admin.companies.index')->with('success', 'อัพเดทข้อมูลบริษัทเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        DB::table('company_catalog')->where('company_id', $id)->delete();
        return redirect()->route('admin.companies.index')->with('success', 'ลบข้อมูลบริษัทเรียบร้อยแล้ว');
    }
}
