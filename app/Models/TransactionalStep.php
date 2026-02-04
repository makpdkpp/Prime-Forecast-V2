<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionalStep extends Model
{
    use HasFactory;

    protected $table = 'transactional_step';
    protected $primaryKey = 'transacstep_id';
    public $timestamps = false;

    protected $fillable = [
        'transac_id',
        'level_id',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function transactional()
    {
        return $this->belongsTo(Transactional::class, 'transac_id', 'transac_id');
    }

    public function step()
    {
        return $this->belongsTo(Step::class, 'level_id', 'level_id');
    }
}
