<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class WkoWorkOrder extends Model {
    use SoftDeletes;
    protected $table = 'wko_work_orders'; protected $primaryKey = 'work_order_id';
    protected $guarded = [];
    protected $casts = ['sla_breached'=>'boolean','requested_at'=>'datetime','approved_at'=>'datetime','started_at'=>'datetime','completed_at'=>'datetime','sla_deadline'=>'datetime'];
    public function asset()       { return $this->belongsTo(AstAsset::class,'asset_id','asset_id'); }
    public function facility()    { return $this->belongsTo(FacFacility::class,'facility_id','facility_id'); }
    public function requestedBy() { return $this->belongsTo(SysUser::class,'requested_by','user_id'); }
    public function assignedTo()  { return $this->belongsTo(SysUser::class,'assigned_to','user_id'); }
    public function approvedBy()  { return $this->belongsTo(SysUser::class,'approved_by','user_id'); }
}
