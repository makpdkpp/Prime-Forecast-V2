<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyCatalog extends Model
{
    use HasFactory;

    protected $table = 'company_catalog';
    protected $primaryKey = 'company_id';
    public $timestamps = true;

    protected $fillable = ['company'];
}
