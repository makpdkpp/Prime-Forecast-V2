<?php

namespace App\Http\Controllers\Admin;

class SourceController extends MasterDataController
{
    protected $table = 'source_of_the_budget';
    protected $primaryKey = 'Source_budget_id';
    protected $nameField = 'Source_budge';
    protected $viewPath = 'admin.master.generic';
    protected $routeName = 'admin.sources';
    protected $title = 'ที่มาของงบประมาณ';
}
