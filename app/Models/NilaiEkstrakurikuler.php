<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class NilaiEkstrakurikuler extends Model
{
    use SoftDeletes, HasUuids;
    protected $table = 'nilai_ekstrakurikuler';
	protected $primaryKey = 'nilai_ekstrakurikuler_id';
	protected $guarded = [];
	public function ekstrakurikuler(){
		return $this->hasOne(Ekstrakurikuler::class, 'ekstrakurikuler_id', 'ekstrakurikuler_id');
	}
    public function anggota_rombel(){
		return $this->hasOne(AnggotaRombel::class, 'anggota_rombel_id', 'anggota_rombel_id');
	}
	public function rombongan_belajar(){
		return $this->hasOneThrough(
            RombonganBelajar::class,
            AnggotaRombel::class,
            'anggota_rombel_id',
            'rombongan_belajar_id',
            'anggota_rombel_id',
            'rombongan_belajar_id'
        );
	}
    public function peserta_didik(){
		return $this->hasOneThrough(
            PesertaDidik::class,
            AnggotaRombel::class,
            'anggota_rombel_id',
            'peserta_didik_id',
            'anggota_rombel_id',
            'peserta_didik_id'
        );
	}
}
