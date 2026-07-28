<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Candidate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'panel',
        'gender',
    ];

    /**
     * Get the literacy score for this candidate.
     */
    public function literacyScore(): HasOne
    {
        return $this->hasOne(LiteracyScore::class);
    }

    /**
     * Get the panel scores for this candidate.
     */
    public function panelScores(): HasMany
    {
        return $this->hasMany(PanelScore::class);
    }

    /**
     * Get assessment assignments for this candidate.
     */
    public function assessmentAssignments(): HasMany
    {
        return $this->hasMany(AssessmentAssignment::class);
    }

    /**
     * Get assessments assigned to this candidate.
     */
    public function assessments(): BelongsToMany
    {
        return $this->belongsToMany(Assessment::class, 'assessment_assignments', 'candidate_id', 'assessment_id')
            ->wherePivot('role', 'candidate')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get evaluation scores recorded for this candidate.
     */
    public function evaluationScores(): HasMany
    {
        return $this->hasMany(EvaluationScore::class);
    }
}
