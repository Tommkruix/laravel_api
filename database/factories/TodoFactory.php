<?php

namespace Database\Factories;

use App\Models\Todo;
use Illuminate\Database\Eloquent\Factories\Factory;

class TodoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Todo::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'fullName' => $this->faker->sentence, 
            'professionalQualification' => $this->faker->sentence,
            'educationDegree' => $this->faker->sentence,
            'dob' => $this->faker->sentence,
            'phoneNumber' => $this->faker->sentence,
            'nextOfKin' => $this->faker->sentence,
            'maritalStatus' => $this->faker->sentence,
            'homeAddress' => $this->faker->paragraph,
            'created_by' => rand(1, 10)
        ];
    }
}
