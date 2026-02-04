<?php

namespace App\Http\Controllers\Admin;

class TeamController extends MasterDataController
{
    protected $table = 'team_catalog';
    protected $primaryKey = 'team_id';
    protected $nameField = 'team';
    protected $viewPath = 'admin.master.generic';
    protected $routeName = 'admin.teams';
    protected $title = 'ทีมขาย';
}
