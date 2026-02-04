<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleCatalog extends Model
{
    use HasFactory;

    protected $table = 'role_catalog';
    protected $primaryKey = 'role_id';
    public $timestamps = false;

    protected $fillable = ['role'];
}
