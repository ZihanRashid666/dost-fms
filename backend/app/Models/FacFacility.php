<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class FacFacility extends Model {
    use SoftDeletes;
    protected $table = 'fac_facilities'; protected $primaryKey = 'facility_id';
    protected $guarded = [];
    public function manager() { return $this->belongsTo(SysUser::class,'managed_by','user_id'); }
    public function assets() { return $this->hasMany(AstAsset::class,'facility_id','facility_id'); }
}
