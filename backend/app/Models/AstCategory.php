<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AstCategory extends Model {
    protected $table = 'ast_categories'; protected $primaryKey = 'category_id';
    protected $guarded = [];
    public function assets() { return $this->hasMany(AstAsset::class,'category_id','category_id'); }
}
