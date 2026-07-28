<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assessment extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'type',
        'status',
        'access_key',
    ];

    /**
     * Get the questions for this assessment.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    /**
     * Get the scoring and rule configuration for this assessment.
     */
    public function rule(): HasOne
    {
        return $this->hasOne(AssessmentRule::class);
    }

    /**
     * Get all assignments for this assessment.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(AssessmentAssignment::class);
    }

    /**
     * Get panelists assigned to this assessment.
     */
    public function panelists(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'assessment_assignments', 'assessment_id', 'user_id')
            ->wherePivot('role', 'panelist')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get candidates assigned to this assessment.
     */
    public function candidates(): BelongsToMany
    {
        return $this->belongsToMany(Candidate::class, 'assessment_assignments', 'assessment_id', 'candidate_id')
            ->wherePivot('role', 'candidate')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get all evaluation score rows for this assessment.
     */
    public function evaluationScores(): HasMany
    {
        return $this->hasMany(EvaluationScore::class);
    }
}
