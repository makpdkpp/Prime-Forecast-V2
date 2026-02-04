<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Step extends Model
{
    use HasFactory;

    protected $table = 'step';
    protected $primaryKey = 'level_id';
    public $timestamps = false;

    protected $fillable = ['level', 'orderlv'];
}
