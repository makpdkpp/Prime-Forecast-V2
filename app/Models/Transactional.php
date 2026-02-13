<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ConditionalSoftDeletes;

class Transactional extends Model
{
    use HasFactory, ConditionalSoftDeletes;

    protected $table = 'transactional';
    protected $primaryKey = 'transac_id';
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'company_id',
        'Product_id',
        'team_id',
        'priority_id',
        'Source_budget_id',
        'Product_detail',
        'product_value',
        'fiscalyear',
        'contact_start_date',
        'date_of_closing_of_sale',
        'sales_can_be_close',
        'remark',
        'Step_id',
        'present',
        'present_date',
        'budgeted',
        'budgeted_date',
        'tor',
        'tor_date',
        'bidding',
        'bidding_date',
        'win',
        'win_date',
        'lost',
        'lost_date',
        'timestamp',
    ];

    protected $casts = [
        'product_value' => 'decimal:2',
        'record_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function company()
    {
        return $this->belongsTo(CompanyCatalog::class, 'company_id', 'company_id');
    }

    public function productGroup()
    {
        return $this->belongsTo(ProductGroup::class, 'Product_id', 'product_id');
    }

    public function team()
    {
        return $this->belongsTo(TeamCatalog::class, 'team_id', 'team_id');
    }

    public function priority()
    {
        return $this->belongsTo(PriorityLevel::class, 'priority_id', 'priority_id');
    }

    public function sourceBudget()
    {
        return $this->belongsTo(SourceBudget::class, 'Source_budget_id', 'Source_budget_id');
    }

    public function steps()
    {
        return $this->hasMany(TransactionalStep::class, 'transac_id', 'transac_id');
    }

    public function latestStep()
    {
        return $this->hasOne(TransactionalStep::class, 'transac_id', 'transac_id')
            ->latest('date')
            ->latest('transacstep_id');
    }

    public function transferHistory()
    {
        return $this->hasMany(TransactionalTransferHistory::class, 'transac_id', 'transac_id')
            ->orderBy('transferred_at', 'desc');
    }

    // Query Scopes for performance optimization
    public function scopeByYear($query, $year)
    {
        return $query->where('fiscalyear', $year);
    }

    public function scopeByQuarter($query, $quarter)
    {
        return $query->whereRaw('QUARTER(contact_start_date) = ?', [$quarter]);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByTeam($query, $teamId)
    {
        return is_array($teamId) 
            ? $query->whereIn('team_id', $teamId)
            : $query->where('team_id', $teamId);
    }

    public function scopeWithAllRelations($query)
    {
        return $query->with([
            'company',
            'productGroup',
            'team',
            'priority',
            'user',
            'sourceBudget',
            'latestStep.step'
        ]);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('updated_at', 'desc')
            ->orderBy('transac_id', 'desc');
    }
}
