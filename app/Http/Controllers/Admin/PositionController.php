<?php

namespace App\Http\Controllers\Admin;

class PositionController extends MasterDataController
{
    protected $table = 'position';
    protected $primaryKey = 'position_id';
    protected $nameField = 'position';
    protected $viewPath = 'admin.master.generic';
    protected $routeName = 'admin.positions';
    protected $title = 'ตำแหน่ง';
}
