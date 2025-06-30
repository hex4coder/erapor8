<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PdPkl extends Model
{
    use HasUuids;
    protected $table = 'pd_pkl';
	protected $primaryKey = 'pd_pkl_id';
	protected $guarded = [];
    public function nilai_pkl(){
		return $this->hasMany(NilaiPkl::class, 'peserta_didik_id', 'peserta_didik_id');
	}
	public function pd()
	{
		return $this->belongsTo(PesertaDidik::class, 'peserta_didik_id', 'peserta_didik_id');
	}
	public function anggota_rombel()
	{
		return $this->belongsTo(AnggotaRombel::class, 'peserta_didik_id', 'peserta_didik_id');
	}
	public function praktik_kerja_lapangan()
	{
		return $this->belongsTo(PraktikKerjaLapangan::class, 'pkl_id', 'pkl_id');
	}
	public function absensi_pkl(){
		return $this->hasMany(AbsensiPkl::class, 'peserta_didik_id', 'peserta_didik_id');
	}
}
