<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class NilaiUkk extends Model
{
    use HasUuids;
    protected $table = 'nilai_ukk';
	protected $primaryKey = 'nilai_ukk_id';
	protected $guarded = [];
    public function rencana_ukk(){
		return $this->hasOne(RencanaUkk::class, 'rencana_ukk_id', 'rencana_ukk_id');
	}
}
