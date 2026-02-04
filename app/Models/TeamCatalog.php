<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamCatalog extends Model
{
    use HasFactory;

    protected $table = 'team_catalog';
    protected $primaryKey = 'team_id';
    public $timestamps = false;

    protected $fillable = ['team'];
}
