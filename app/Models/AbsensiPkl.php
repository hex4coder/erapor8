<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AbsensiPkl extends Model
{
    use HasUuids;
    protected $table = 'absensi_pkl';
	protected $primaryKey = 'absensi_pkl_id';
	protected $guarded = [];
}
