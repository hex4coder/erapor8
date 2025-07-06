<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class UnitUkk extends Model
{
    use HasUuids;
    protected $table = 'unit_ukk';
	protected $primaryKey = 'unit_ukk_id';
	protected $guarded = [];
}
