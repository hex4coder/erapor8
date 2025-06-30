<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class NilaiPkl extends Model
{
    use HasUuids;
    protected $table = 'nilai_pkl';
	protected $primaryKey = 'nilai_pkl_id';
	protected $guarded = [];
}
