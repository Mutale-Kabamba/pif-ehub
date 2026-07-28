<?php

namespace Database\Factories;

use App\Models\Assessment;
use App\Models\AssessmentAssignment;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AssessmentAssignment>
 */
class AssessmentAssignmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\AssessmentAssignment>
     */
    protected $model = AssessmentAssignment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $role = fake()->randomElement(['panelist', 'candidate']);

        return [
            'assessment_id' => Assessment::factory(),
            'user_id' => $role === 'panelist' ? User::factory() : null,
            'candidate_id' => $role === 'candidate' ? Candidate::factory() : null,
            'role' => $role,
        ];
    }
}
