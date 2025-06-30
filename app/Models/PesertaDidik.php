<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PesertaDidik extends Model
{
    use SoftDeletes;
    public $incrementing = false;
	public $keyType = 'string';
	protected $table = 'peserta_didik';
	protected $primaryKey = 'peserta_didik_id';
	protected $guarded = [];
	public function anggota_rombel()
	{
		return $this->hasOne(AnggotaRombel::class, 'peserta_didik_id', 'peserta_didik_id');
	}
	public function pd_keluar()
	{
		return $this->hasOne(PdKeluar::class, 'peserta_didik_id', 'peserta_didik_id');
	}
	public function nilai_akhir_kurmer(){
		return $this->hasOneThrough(
            NilaiAkhir::class,
            AnggotaRombel::class,
            'peserta_didik_id',
            'anggota_rombel_id',
            'peserta_didik_id',
            'anggota_rombel_id'
        )->where('kompetensi_id', 4);
	}
	public function nilai_akhir_pengetahuan(){
		return $this->hasOneThrough(
            NilaiAkhir::class,
            AnggotaRombel::class,
            'peserta_didik_id',
            'anggota_rombel_id',
            'peserta_didik_id',
            'anggota_rombel_id'
        )->where('kompetensi_id', 1);
	}
	public function nilai_akhir_keterampilan(){
		return $this->hasOneThrough(
            NilaiAkhir::class,
            AnggotaRombel::class,
            'peserta_didik_id',
            'anggota_rombel_id',
            'peserta_didik_id',
            'anggota_rombel_id'
        )->where('kompetensi_id', 2);
	}
	public function deskripsi_mapel(){
		return $this->hasOneThrough(
            DeskripsiMataPelajaran::class,
            AnggotaRombel::class,
            'peserta_didik_id',
            'anggota_rombel_id',
            'peserta_didik_id',
            'anggota_rombel_id'
        );
	}
	public function agama()
	{
		return $this->belongsTo(Agama::class, 'agama_id', 'agama_id');
	}
    public function nilai_akhir_induk(){
		return $this->hasOneThrough(
            NilaiAkhir::class,
            AnggotaRombel::class,
            'peserta_didik_id',
            'anggota_rombel_id',
            'peserta_didik_id',
            'anggota_rombel_id'
        )->where('kompetensi_id', 4);
	}
}
