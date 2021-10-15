<?php

namespace App\Exports;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class EmployeesExport  implements FromCollection, WithStrictNullComparison
{


    public array $where;
    public array $columns;


    public function __construct(array $columns = ["*"], array $where = ["*"])
    {
        $this->where = $where;
        $this->columns = $columns;
    }

    public function collection()
    {

        /** @var Collection */
        $datas = Employee::select($this->columns)
            ->where($this->where)
            ->get();

        $first = $datas->first();

        if (!$first) {
            return Collection::make([]);
        }

        $headings = Collection::make($first->getAttributes())
            ->except([
                ...$first->getHidden(),
                "reset_code",
                "reset_code_expires_in"
            ])
            ->keys()
            ->flatten();


        $columns = array();
        foreach ($headings as $key => $value) {
            $columns[$value] = $value;
        }

        $new_datas = [$columns];

        $datas_len = count($datas);

        for ($i = 0; $i < $datas_len; $i++) {
            $new_datas[] = $datas[$i]->toArray();
        }

        return  Collection::make($new_datas);
    }
}
