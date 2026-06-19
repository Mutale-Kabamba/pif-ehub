<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiteracyScore extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'candidate_id',
        'assessment_date',
        'task1_directory',
        'task2_wordproc',
        'task3_research',
        'task4_formatting',
        'task5_saving',
        'task6_spreadsheet',
        'task7_screenshot',
        'task8_zip',
        'task9_sysspecs',
        'task10_notepad',
        'total_score',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'assessment_date' => 'date',
        'total_score' => 'decimal:1',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (LiteracyScore $score) {
            $score->total_score = (
                (int) $score->task1_directory +
                (int) $score->task2_wordproc +
                (int) $score->task3_research +
                (int) $score->task4_formatting +
                (int) $score->task5_saving +
                (int) $score->task6_spreadsheet +
                (int) $score->task7_screenshot +
                (int) $score->task8_zip +
                (int) $score->task9_sysspecs +
                (int) $score->task10_notepad
            );
        });
    }

    /**
     * Get the candidate that owns this literacy score.
     */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
}
