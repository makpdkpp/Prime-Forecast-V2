<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionalTransferHistory extends Model
{
    use HasFactory;

    protected $table = 'transactional_transfer_history';
    protected $primaryKey = 'transfer_id';
    public $timestamps = true;

    protected $fillable = [
        'transac_id',
        'from_user_id',
        'to_user_id',
        'transferred_by',
        'transfer_reason',
        'old_team_id',
        'new_team_id',
        'transferred_at',
    ];

    protected $casts = [
        'transferred_at' => 'datetime',
    ];

    public function transactional()
    {
        return $this->belongsTo(Transactional::class, 'transac_id', 'transac_id');
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id', 'user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id', 'user_id');
    }

    public function transferredByUser()
    {
        return $this->belongsTo(User::class, 'transferred_by', 'user_id');
    }

    public function oldTeam()
    {
        return $this->belongsTo(TeamCatalog::class, 'old_team_id', 'team_id');
    }

    public function newTeam()
    {
        return $this->belongsTo(TeamCatalog::class, 'new_team_id', 'team_id');
    }
}
