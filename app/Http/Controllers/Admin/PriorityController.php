<?php

namespace App\Http\Controllers\Admin;

class PriorityController extends MasterDataController
{
    protected $table = 'priority_level';
    protected $primaryKey = 'priority_id';
    protected $nameField = 'priority';
    protected $viewPath = 'admin.master.generic';
    protected $routeName = 'admin.priorities';
    protected $title = 'โอกาสการชนะ';
}
