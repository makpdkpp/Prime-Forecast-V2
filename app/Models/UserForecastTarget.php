<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserForecastTarget extends Model
{
    use HasFactory;
    
    protected $table = 'user_forecast_target';
    
    protected $fillable = [
        'user_id',
        'fiscal_year',
        'target_value',
    ];
    
    protected $casts = [
        'target_value' => 'decimal:2',
    ];
    
    /**
     * Get the user that owns the forecast target.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
