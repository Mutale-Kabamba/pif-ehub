<?php

namespace Database\Factories;

use App\Models\Assessment;
use App\Models\Candidate;
use App\Models\EvaluationScore;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EvaluationScore>
 */
class EvaluationScoreFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\EvaluationScore>
     */
    protected $model = EvaluationScore::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assessment_id' => Assessment::factory(),
            'candidate_id' => Candidate::factory(),
            'evaluator_id' => User::factory(),
            'question_id' => Question::factory(),
            'score' => fake()->randomFloat(2, 0, 5),
            'text_response' => fake()->optional()->sentence(),
        ];
    }
}
