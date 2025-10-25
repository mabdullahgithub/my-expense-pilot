<?php

namespace Database\Factories;

use App\Models\EmploymentHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmploymentHistoryFactory extends Factory
{
    protected $model = EmploymentHistory::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-5 years', '-1 month');
        $isCurrent = $this->faker->boolean(30); // 30% chance of being current
        
        return [
            'user_id' => User::factory(),
            'company_name' => $this->faker->company(),
            'position' => $this->faker->randomElement([
                'Software Engineer',
                'Senior Developer',
                'Project Manager',
                'Data Analyst',
                'Marketing Specialist',
                'Sales Representative',
                'Accountant',
                'HR Manager',
                'Graphic Designer',
                'Content Writer',
            ]),
            'employment_type' => $this->faker->randomElement([
                'full_time',
                'part_time',
                'contract',
                'internship',
                'freelance',
            ]),
            'salary' => $this->faker->randomFloat(2, 30000, 150000),
            'salary_frequency' => $this->faker->randomElement(['hourly', 'monthly', 'yearly']),
            'start_date' => $startDate,
            'end_date' => $isCurrent ? null : $this->faker->dateTimeBetween($startDate, 'now'),
            'description' => $this->faker->optional(0.7)->paragraph(),
            'is_current' => $isCurrent,
        ];
    }

    public function current(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_current' => true,
            'end_date' => null,
        ]);
    }

    public function past(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_current' => false,
            'end_date' => $this->faker->dateTimeBetween($attributes['start_date'], 'now'),
        ]);
    }
}