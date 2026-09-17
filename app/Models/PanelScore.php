<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PanelScore extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'candidate_id',
        'panelist_id',
        'crit1_motivation',
        'crit2_availability',
        'crit3_resilience',
        'crit4_communication',
        'comments',
        'is_valid',
    ];

    /**
     * Get the candidate that owns this panel score.
     */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    /**
     * Get the panelist (user) that assigned this score.
     */
    public function panelist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'panelist_id');
    }
}
