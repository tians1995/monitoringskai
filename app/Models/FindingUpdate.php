<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FindingUpdate extends Model { protected $fillable=['finding_id','user_id','status','progress','notes','old_target_date','new_target_date']; protected $casts=['old_target_date'=>'date','new_target_date'=>'date']; public function finding(){return $this->belongsTo(AuditFinding::class);} public function user(){return $this->belongsTo(User::class);} }
