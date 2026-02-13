<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionalTeam extends Model
{
    use HasFactory;

    protected $table = 'transactional_team';
    protected $primaryKey = 'transacteam_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'team_id',
    ];
}
