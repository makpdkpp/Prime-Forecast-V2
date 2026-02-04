<?php

namespace App\Http\Controllers\Admin;

class IndustryController extends MasterDataController
{
    protected $table = 'industry_group';
    protected $primaryKey = 'Industry_id';
    protected $nameField = 'Industry';
    protected $viewPath = 'admin.master.generic';
    protected $routeName = 'admin.industries';
    protected $title = 'อุตสาหกรรม';
}
