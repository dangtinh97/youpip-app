<?php

namespace App\Models;


use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @property string $_id
 * @property string $username
 * @property int    $id
 * @property string $full_name
 * @property string  $short_username
 * @property bool $verify_account
 * @property string $email
 */
class User extends Authenticatable implements JWTSubject
{
    protected $collection = 'users';
    protected $fillable = ['username','id','password','short_username','token_fcm','email','verify_account','os-version'];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
