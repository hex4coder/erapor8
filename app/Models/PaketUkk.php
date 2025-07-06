<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PaketUkk extends Model
{
    use HasUuids;
    protected $table = 'ref.paket_ukk';
	protected $primaryKey = 'paket_ukk_id';
	protected $guarded = [];
	public function jurusan(){
		return $this->hasOne(Jurusan::class, 'jurusan_id', 'jurusan_id');
	}
	public function kurikulum(){
		return $this->hasOne(Kurikulum::class, 'kurikulum_id', 'kurikulum_id');
	}
	public function unit_ukk(){
		return $this->hasMany(UnitUkk::class, 'paket_ukk_id', 'paket_ukk_id');
	}
	public function rencana_ukk(){
		return $this->hasMany(RencanaUkk::class, 'paket_ukk_id', 'paket_ukk_id');
	}
}
