<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriorityLevel extends Model
{
    use HasFactory;

    protected $table = 'priority_level';
    protected $primaryKey = 'priority_id';
    public $timestamps = false;

    protected $fillable = ['priority'];
}
