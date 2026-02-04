<?php

namespace App\Http\Controllers\Admin;

class StepController extends MasterDataController
{
    protected $table = 'step';
    protected $primaryKey = 'level_id';
    protected $nameField = 'level';
    protected $viewPath = 'admin.master.generic';
    protected $routeName = 'admin.steps';
    protected $title = 'ขั้นตอนการขาย';
}
