<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;

class SysUser extends Authenticatable implements JWTSubject
{
    use SoftDeletes;
    protected $table      = 'sys_users';
    protected $primaryKey = 'user_id';
    protected $fillable   = ['user_code','full_name','email','password_hash','role','department','contact_no','is_active','last_login_at'];
    protected $hidden     = ['password_hash'];
    protected $casts      = ['is_active'=>'boolean','last_login_at'=>'datetime'];
    public function getAuthPassword() { return $this->password_hash; }
    public function getJWTIdentifier() { return $this->getKey(); }
    public function getJWTCustomClaims() { return ['role'=>$this->role,'user_code'=>$this->user_code]; }
}
