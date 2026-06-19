<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'survey_type',
        'q1_os_filemgmt',
        'q2_spreadsheets',
        'q3_ux_design',
        'q4_frontend',
        'q5_js_logic',
        'q6_fullstack',
        'q7_resilience',
        'q8_troubleshooting',
        'q9_freelance',
        'q10_livingstone_tourism',
        'q11_career_efficacy',
        'qual1_why_join',
        'qual2_skills_hoped',
        'qual3_success_criteria',
        'qual4_challenges',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'survey_type' => 'string',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;
}
