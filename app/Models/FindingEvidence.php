<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FindingEvidence extends Model { protected $fillable=['finding_id','file_name','file_path','uploaded_by']; public function finding(){return $this->belongsTo(AuditFinding::class);} public function uploader(){return $this->belongsTo(User::class,'uploaded_by');} }
