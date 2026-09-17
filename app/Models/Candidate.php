<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
}
