<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditFindingItem extends Model
{
    protected $fillable = ['description', 'sort_order'];
}
