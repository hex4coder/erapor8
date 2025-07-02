<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Asesor extends Model
{
    use HasUuids;
    protected $table = 'asesor';
	protected $primaryKey = 'asesor_id';
	protected $guarded = [];
	public function guru(){
		return $this->belongsTo(Guru::class, 'guru_id', 'guru_id');
	}
	public function dudi(){
		return $this->belongsTo(Dudi::class, 'dudi_id', 'dudi_id');
	}
}
