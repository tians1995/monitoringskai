<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Audit extends Model { protected $fillable=['name','year']; public function findings(){return $this->hasMany(AuditFinding::class);} }
