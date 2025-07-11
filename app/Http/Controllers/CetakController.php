<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnggotaRombel;
use App\Models\RencanaUkk;
use App\Models\NilaiUkk;
use App\Models\PaketUkk;
use App\Models\Ptk;
use App\Models\Sekolah;
use PDF;

class CetakController extends Controller
{
    public function sertifikat($anggota_rombel_id, $rencana_ukk_id){
		$anggota_rombel = AnggotaRombel::with('peserta_didik')->find($anggota_rombel_id);
		$callback = function($query) use ($anggota_rombel_id){
			$query->where('anggota_rombel_id', $anggota_rombel_id);
		};
		$rencana_ukk = RencanaUkk::with('guru_internal')->with(['guru_eksternal' => function($query){
			$query->with('dudi');
		}])->with(['nilai_ukk' => $callback])->find($rencana_ukk_id);
		$count_penilaian_ukk = NilaiUkk::where('peserta_didik_id', $anggota_rombel->peserta_didik_id)->count();
		$data['siswa'] = $anggota_rombel;
		$data['sekolah_id'] = $anggota_rombel->sekolah_id;
		$data['rencana_ukk'] = $rencana_ukk;
		$data['count_penilaian_ukk'] = $count_penilaian_ukk;
		$data['paket'] = PaketUkk::with('jurusan')->with(['unit_ukk' => function($query){
			$query->orderBy('kode_unit');
		}])->find($rencana_ukk->paket_ukk_id);
		$data['asesor'] = Ptk::with('dudi')->find($rencana_ukk->eksternal);
		$data['sekolah'] = Sekolah::with(['kepala_sekolah' => function($query) use ($anggota_rombel){
			$query->where('semester_id', $anggota_rombel->semester_id);
		}])->find($anggota_rombel->sekolah_id);
		$pdf = PDF::loadView('cetak.sertifikat1', $data);
		$pdf->getMpdf()->AddPage('P');
		$rapor_cover= view('cetak.sertifikat2', $data);
		$pdf->getMpdf()->WriteHTML($rapor_cover);
		$general_title = strtoupper($anggota_rombel->peserta_didik->nama);
		return $pdf->stream($general_title.'-SERTIFIKAT.pdf');
	}
}
