<?php

namespace App;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;

class Users extends Eloquent implements Authenticatable
{
    use AuthenticatableTrait;
    public $table='users';
    protected $primaryKey='user_ID';
    public $timestamps=false;
    public $incrementing=false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_ID','username','password','email','firstname','lastname','gender','isadmin','diagnostic','remember_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'user_ID','password','isadmin','diagnostic','remember_token'
    ];

    public function is_admin(){
        if($this->isadmin){
            return true;
        }else{
            return false;
        }
    }

    public function revcenter(){
        return $this->hasOne('App\RevCenter','revcenter_ID','revcenter_ID');
    }
}
