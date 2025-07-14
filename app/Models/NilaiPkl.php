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
    public function praktik_kerja_lapangan()
	{
		return $this->belongsTo(PraktikKerjaLapangan::class, 'pkl_id', 'pkl_id');
	}
    public function tp_pkl()
	{
		return $this->belongsTo(TpPkl::class, 'tp_id', 'tp_id');
	}
	public function tp()
	{
		return $this->belongsTo(TujuanPembelajaran::class, 'tp_id', 'tp_id');
	}
}
