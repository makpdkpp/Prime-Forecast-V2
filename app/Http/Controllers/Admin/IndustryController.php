<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;

class IndustryController extends MasterDataController
{
    protected $table = 'industry_group';
    protected $primaryKey = 'Industry_id';
    protected $nameField = 'Industry';
    protected $viewPath = 'admin.master.generic';
    protected $routeName = 'admin.industries';
    protected $title = 'อุตสาหกรรม';

    public function destroy($id)
    {
        $usageCount = DB::table('company_catalog')
            ->where('Industry_id', $id)
            ->count();

        if ($usageCount > 0) {
            return redirect()
                ->route("{$this->routeName}.index")
                ->with('error', "ไม่สามารถลบ{$this->title}นี้ได้ เนื่องจากมีบริษัทที่ใช้งานอยู่ {$usageCount} รายการ");
        }

        DB::table($this->table)->where($this->primaryKey, $id)->delete();
        return redirect()->route("{$this->routeName}.index")->with('success', "ลบ{$this->title}เรียบร้อยแล้ว");
    }
}
