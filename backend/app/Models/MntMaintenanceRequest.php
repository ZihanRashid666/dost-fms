<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MntMaintenanceRequest extends Model {
    protected $table = 'mnt_maintenance_requests'; protected $primaryKey = 'request_id';
    protected $guarded = [];
    protected $casts = ['reviewed_at'=>'datetime'];
    public function asset()       { return $this->belongsTo(AstAsset::class,'asset_id','asset_id'); }
    public function facility()    { return $this->belongsTo(FacFacility::class,'facility_id','facility_id'); }
    public function submittedBy() { return $this->belongsTo(SysUser::class,'submitted_by','user_id'); }
    public function reviewedBy()  { return $this->belongsTo(SysUser::class,'reviewed_by','user_id'); }
    public function workOrder()   { return $this->belongsTo(WkoWorkOrder::class,'work_order_id','work_order_id'); }
}
