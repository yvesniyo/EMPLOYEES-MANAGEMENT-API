<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Services\CodeGenerator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Employee::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {


        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => CodeGenerator::PHONE_NUMBER(),
            "code"  => CodeGenerator::EMPLOYEE(),
            "national_id" => CodeGenerator::NATIONAL_ID(),
            "dob" =>  $this->faker->date(),
            "status" => $this->faker->randomElement(["ACTIVE", "INACTIVE"]),
            "position" => $this->faker->randomElement(["MANAGER", "DEVELOPER", "DESIGNER", "TESTER", "DEVOPS"]),
            "password" => app('hash')->make("password"),
            "created_at" => Carbon::now(),
        ];
    }
}
