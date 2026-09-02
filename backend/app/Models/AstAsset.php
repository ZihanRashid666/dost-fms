<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AstAsset extends Model {
    use SoftDeletes;
    protected $table = 'ast_assets'; protected $primaryKey = 'asset_id';
    protected $guarded = [];
    public function category() { return $this->belongsTo(AstCategory::class,'category_id','category_id'); }
    public function facility() { return $this->belongsTo(FacFacility::class,'facility_id','facility_id'); }
    public function assignedTo() { return $this->belongsTo(SysUser::class,'assigned_to','user_id'); }
}
