<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Traits\LogsActivity;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @OA\Schema()
 */

class Employee extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable, HasFactory, CausesActivity, LogsActivity;


    protected $table = "employees";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'code',
        'national_id',
        'phone',
        'dob',
        'status',
        'position',
        'password',
    ];


    protected $casts = [
        "reset_code_expires_in" => "datetime",
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];


    // /**
    //  * The employee code
    //  * @var string
    //  * @OA\Property()
    //  */
    // public string $code;

    // /**
    //  * The employee name
    //  * @var string
    //  * @OA\Property()
    //  */
    // public string $name;


    // /**
    //  * The employee email
    //  * @var string
    //  * @OA\Property()
    //  */
    // public string $email;

    // /**
    //  * The employee national_id
    //  * @var string
    //  * @OA\Property()
    //  */
    // public string $national_id;


    // /**
    //  * The employee phone
    //  * @var string
    //  * @OA\Property()
    //  */
    // public string $phone;


    // /**
    //  * The employee date of birth
    //  * @var date
    //  * @OA\Property()
    //  */
    // public string $dob;


    // /**
    //  * The employee status
    //  * @var string
    //  * @OA\Property()
    //  */
    // public string $status;

    // /**
    //  * The employee position
    //  * @var string
    //  * @OA\Property()
    //  */
    // public string $position;


    // /**
    //  * The employee create date
    //  * @var string
    //  * @OA\Property()
    //  */
    // public string $created_at;


    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
        // return [
        //     "code" => $this->code,
        //     "email" => $this->email,
        // ];
    }


    public function scopeManager($query)
    {
        $query->where("position", "MANAGER");
    }


    public function scopeActive($query)
    {
        $query->where("status", "ACTIVE");
    }

    public function scopeCode($query, $code)
    {
        $query->whereCode($code);
    }


    public function isManager()
    {
        return $this->position == "MANAGER";
    }
}
