<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Unit extends Model { protected $fillable=['name']; public function findings(){return $this->hasMany(AuditFinding::class);} }
