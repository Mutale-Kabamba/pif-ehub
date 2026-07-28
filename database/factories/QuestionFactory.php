<?php

namespace Database\Factories;

use App\Models\Assessment;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Question>
     */
    protected $model = Question::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assessment_id' => Assessment::factory(),
            'question_text' => fake()->sentence(8),
            'type' => fake()->randomElement(['scale', 'text', 'multiple_choice', 'boolean']),
            'weight' => fake()->randomFloat(2, 0.5, 3.0),
            'order' => fake()->numberBetween(1, 20),
        ];
    }
}
