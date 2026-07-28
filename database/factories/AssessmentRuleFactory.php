<?php

namespace Database\Factories;

use App\Models\Assessment;
use App\Models\AssessmentRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AssessmentRule>
 */
class AssessmentRuleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\AssessmentRule>
     */
    protected $model = AssessmentRule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assessment_id' => Assessment::factory(),
            'max_panelists' => fake()->optional()->numberBetween(1, 5),
            'score_cap' => fake()->optional()->randomFloat(2, 10, 100),
            'passing_threshold' => fake()->optional()->randomFloat(2, 5, 90),
            'rules_json' => [
                'allow_partial_submission' => fake()->boolean(),
            ],
        ];
    }
}
