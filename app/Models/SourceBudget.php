<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SourceBudget extends Model
{
    use HasFactory;

    protected $table = 'source_of_the_budget';
    protected $primaryKey = 'Source_budget_id';
    public $timestamps = false;

    protected $fillable = ['Source_budge'];
}
