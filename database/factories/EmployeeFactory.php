<?php

namespace Database\Factories;

use App\Models\Employee;
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
        $numbers = "0123456789012345678901234567890123456789";
        $code = str_shuffle($numbers);
        $code = substr($code, 1, 4);

        $nationa_id = substr(str_shuffle($numbers), 1, 16);

        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->e164PhoneNumber(),
            "code"  => "EMP" . $code,
            "national_id" => $nationa_id,
            "dob" =>  $this->faker->date(),
            "status" => $this->faker->randomElement(["ACTIVE", "INACTIVE"]),
            "position" => $this->faker->randomElement(["MANAGER", "DEVELOPER", "DESIGNER", "TESTER", "DEVOPS"]),
            "password" => app('hash')->make("password"),
            "created_at" => Carbon::now(),
        ];
    }
}
