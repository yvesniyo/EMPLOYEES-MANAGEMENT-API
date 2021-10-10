<?php

namespace App\Imports;

use App\Models\Employee;
use App\Exceptions\CustomExcelImportException;
use App\Jobs\SendWelcomeToNewEmployeeJob;
use App\Services\CodeGenerator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Validators\Failure;

class EmployeesImport implements
    WithHeadingRow,
    WithValidation,
    OnEachRow,
    SkipsOnFailure,
    WithEvents,
    WithCalculatedFormulas
{

    use Importable, SkipsFailures, RegistersEventListeners;

    private static $all_failures = array();
    private static $all_successes = array();
    /**
     * @param array $row
     *
     * @return Passangers|null
     */

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }
    /**
     * @param Failure[] $failures
     */
    public function onFailure(Failure ...$failures)
    {
        self::$all_failures[] = $failures;
    }

    public static function afterImport(AfterImport $event)
    {
        if (count(self::$all_failures) > 0) {
            throw new CustomExcelImportException(self::$all_failures, self::$all_successes);
        }
    }

    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row      = $row->toArray();

        $row["phone"] = str_replace("+", "", $row["phone"]);

        $code =  CodeGenerator::EMPLOYEE();

        while (Employee::whereCode($code)->exists()) {
            $code =  CodeGenerator::EMPLOYEE();
        }

        $employeeDetails = [
            "name" => $row["name"],
            "phone" => "+" . ((string) ((int) $row["phone"])),
            "email" => $row["email"],
            "code" => $code,
            "password" => Hash::make("password"),
            "national_id" => $row["national_id"],
            "position" => strtoupper($row["position"]),
            "dob" => $row["dob"],
            "status" => strtoupper($row["status"]),
        ];

        if (!isOver18($employeeDetails["dob"])) {
            self::$all_failures[] =  new Failure(($rowIndex), "dob", [
                "Employee Date of birth should be over 18, Employee:" . $employeeDetails["name"]
            ], $row);
            return;
        }

        if (Employee::query()->whereEmail($employeeDetails["email"])->exists()) {
            self::$all_failures[] =  new Failure(($rowIndex), "email", [
                "Employee exists with  this email " . $employeeDetails["email"]
            ], $row);
            return;
        }

        if (Employee::query()->wherePhone($employeeDetails["phone"])->exists()) {
            self::$all_failures[] =  new Failure(($rowIndex), "phone", [
                "Employee exists with  this phone " . $employeeDetails["phone"]
            ], $row);
            return;
        }


        if (Employee::query()->whereNationalId($employeeDetails["national_id"])->exists()) {
            self::$all_failures[] =  new Failure(($rowIndex), "national_id", [
                "Employee exists with  this national_id " . $employeeDetails["national_id"]
            ], $row);
            return;
        }



        $employee = Employee::create($employeeDetails);
        if (!$employee) {
            self::$all_failures[] =  new Failure(($rowIndex), "unknown", [
                "Could not create employee"
            ], $row);
            return;
        }

        dispatch(new SendWelcomeToNewEmployeeJob($employee));

        self::$all_successes[] = $row;
    }


    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function rules(): array
    {
        return [
            "name" => "string|required",
            "email" => "email|required",
            "phone" => "phone|required",
            "national_id" => "national_id|required",
            "position" => [Rule::in(["MANAGER", "DEVELOPER", "DESIGNER", "TESTER", "DEVOPS"]), "required"],
            "status" => [Rule::in(["ACTIVE", "INACTIVE"]), "required"],
            "dob" => "date|required",
        ];
    }
}
