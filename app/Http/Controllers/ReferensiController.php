<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\MataPelajaran;
use App\Models\Ekstrakurikuler;
use App\Models\Dudi;
use App\Models\PesertaDidik;
use App\Models\KompetensiDasar;
use App\Models\RombonganBelajar;
use App\Models\Pembelajaran;

class ReferensiController extends Controller
{
    public function index(){
        $data = MataPelajaran::whereHas('pembelajaran', function($query){
            $query->where('sekolah_id', request()->sekolah_id);
            $query->where('semester_id', request()->semester_id);
        })->orderBy(request()->sortby, request()->sortbydesc)
        ->when(request()->q, function($query) {
            $query->where('nama', 'ILIKE', '%' . request()->q . '%');
            $query->orWhere('mata_pelajaran_id', 'ILIKE', '%' . request()->q . '%');
        })->paginate(request()->per_page);
        return response()->json(['status' => 'success', 'data' => $data]);
    }
    public function ekstrakurikuler(){
        $data = Ekstrakurikuler::where(function($query){
            $query->whereHas('rombongan_belajar', function($query){
                $query->where('sekolah_id', request()->sekolah_id);
                $query->where('semester_id', request()->semester_id);
            });
        })->with([
            'guru' => function($query){
                $query->select('guru_id', 'nama', 'gelar_depan', 'gelar_belakang');
            },
            'rombongan_belajar' => function($query){
                $query->select('rombongan_belajar_id');
                $query->withCount('anggota_rombel');
            }
        ])
        ->orderBy(request()->sortby, request()->sortbydesc)
        ->when(request()->q, function($query) {
            $query->where('nama_ekskul', 'ILIKE', '%' . request()->q . '%');
            $query->orWhere('nama_ketua', 'ILIKE', '%' . request()->q . '%');
            $query->orWhereHas('guru', function($query){
                $query->where('nama', 'ILIKE', '%' . request()->q . '%');
            });
        })->paginate(request()->per_page);
        return response()->json(['status' => 'success', 'data' => $data]);
    }
    public function dudi(){
        $data = Dudi::where(function($query){
            $query->where('sekolah_id', request()->sekolah_id);
        })->withCount(['akt_pd'])->orderBy(request()->sortby, request()->sortbydesc)
        ->when(request()->q, function($query) {
            $query->where('nama', 'ILIKE', '%' . request()->q . '%');
            $query->orWhere('nama_bidang_usaha', 'ILIKE', '%' . request()->q . '%');
        })->paginate(request()->per_page);
        return response()->json(['status' => 'success', 'data' => $data]);
    }
    public function detil_dudi(){
        $data = Dudi::with(['mou' => function($query){
            $query->with(['akt_pd' => function($query){
                $query->with([
                    'bimbing_pd' => function($query){
                        $query->with(['guru' => function($query){
                            $query->select('guru_id', 'nama', 'gelar_depan', 'gelar_belakang', 'photo', 'email');
                        }]);
                        $query->orderBy('urutan_pembimbing');
                    }
                ]);
                $query->withCount(['anggota_akt_pd' => function($query){
                    $query->whereHas('anggota_rombel', function($query){
                        $query->where('semester_id', request()->semester_id);
                    });
                }]);
            }]);
        }])->find(request()->dudi_id);
        return response()->json($data);
    }
    public function anggota_prakerin(){
        $data = PesertaDidik::whereHas('anggota_akt_pd', function($query){
            $query->where('akt_pd_id', request()->akt_pd_id);
            $query->whereHas('anggota_rombel', function($query){
                $query->where('semester_id', request()->semester_id);
            });
        })->with([
            'anggota_akt_pd' => function($query){
                $query->where('akt_pd_id', request()->akt_pd_id);
                $query->whereHas('anggota_rombel', function($query){
                    $query->where('semester_id', request()->semester_id);
                });
            },
            'agama',
            'kelas' => function($query){
                $query->where('jenis_rombel', 1);
                $query->where('rombongan_belajar.semester_id', request()->semester_id);
            }
        ])->get();
        return response()->json($data);
    }
    private function kondisiPembelajaran(){
        return function($query){
            if(request()->pembelajaran_id){
                $query->where('pembelajaran.pembelajaran_id', request()->pembelajaran_id);
            }
            if(request()->rombongan_belajar_id){
                $query->where('rombongan_belajar_id', request()->rombongan_belajar_id);
            }
            if(hasRole('pembimbing', request()->periode_aktif)){
                $query->where('guru_id', request()->guru_id);
                $query->where('mata_pelajaran_id', '800001000');
                $query->where('sekolah_id', request()->sekolah_id);
                $query->where('semester_id', request()->semester_id);
                $query->orWhere('guru_id', request()->guru_id);
                $query->whereNotNull('kelompok_id');
                $query->whereNotNull('no_urut');
                //$query->whereNull('induk_pembelajaran_id');
                if(request()->add_kd){
                    $query->whereHas('rombongan_belajar', function($query){
                        $query->whereHas('kurikulum', function($query){
                            $query->where('nama_kurikulum', 'ILIKE', '%REV%');
                        });
                    });
                }
                if(request()->add_cp){
                    $query->whereHas('rombongan_belajar', function($query){
                        $query->whereHas('kurikulum', function($query){
                            $query->where('nama_kurikulum', 'ILIKE', '%Merdeka%');
                        });
                    });
                }
                $query->where('sekolah_id', request()->sekolah_id);
                $query->where('semester_id', request()->semester_id);
                $query->orWhere('guru_pengajar_id', request()->guru_id);
                if(request()->rombongan_belajar_id){
                    $query->where('rombongan_belajar_id', request()->rombongan_belajar_id);
                }
                $query->whereNotNull('kelompok_id');
                $query->whereNotNull('no_urut');
                //$query->whereNull('induk_pembelajaran_id');
                if(request()->add_kd){
                    $query->whereHas('rombongan_belajar', function($query){
                        $query->whereHas('kurikulum', function($query){
                            $query->where('nama_kurikulum', 'ILIKE', '%REV%');
                        });
                    });
                } 
                if(request()->add_cp){
                    $query->whereHas('rombongan_belajar', function($query){
                        $query->whereHas('kurikulum', function($query){
                            $query->where('nama_kurikulum', 'ILIKE', '%Merdeka%');
                        });
                    });
                }
                $query->where('sekolah_id', request()->sekolah_id);
                $query->where('semester_id', request()->semester_id);
            } else {
                $query->where('guru_id', request()->guru_id);
                $query->whereNotNull('kelompok_id');
                $query->whereNotNull('no_urut');
                //$query->whereNull('induk_pembelajaran_id');
                if(request()->add_kd){
                    $query->whereHas('rombongan_belajar', function($query){
                        $query->whereHas('kurikulum', function($query){
                            $query->where('nama_kurikulum', 'ILIKE', '%REV%');
                        });
                    });
                }
                if(request()->add_cp){
                    $query->whereHas('rombongan_belajar', function($query){
                        $query->whereHas('kurikulum', function($query){
                            $query->where('nama_kurikulum', 'ILIKE', '%Merdeka%');
                        });
                    });
                }
                $query->where('sekolah_id', request()->sekolah_id);
                $query->where('semester_id', request()->semester_id);
                $query->orWhere('guru_pengajar_id', request()->guru_id);
                if(request()->rombongan_belajar_id){
                    $query->where('rombongan_belajar_id', request()->rombongan_belajar_id);
                }
                $query->whereNotNull('kelompok_id');
                $query->whereNotNull('no_urut');
                //$query->whereNull('induk_pembelajaran_id');
                if(request()->add_kd){
                    $query->whereHas('rombongan_belajar', function($query){
                        $query->whereHas('kurikulum', function($query){
                            $query->where('nama_kurikulum', 'ILIKE', '%REV%');
                        });
                    });
                } 
                if(request()->add_cp){
                    $query->whereHas('rombongan_belajar', function($query){
                        $query->whereHas('kurikulum', function($query){
                            $query->where('nama_kurikulum', 'ILIKE', '%Merdeka%');
                        });
                    });
                }
                $query->where('sekolah_id', request()->sekolah_id);
                $query->where('semester_id', request()->semester_id);
            }
        };
    }
    public function kompetensi_dasar(){
        $data = KompetensiDasar::withWhereHas('mata_pelajaran')->where(function($query){
            $query->whereHas('pembelajaran', $this->kondisiPembelajaran());
            $query->whereNotIn('kurikulum', [2006, 2013, 2022]);
        })
        ->orderBy(request()->sortby, request()->sortbydesc)
        ->when(request()->q, function($query) {
            $query->where('id_kompetensi', 'ilike', '%'.request()->q.'%');
            $query->whereHas('pembelajaran', $this->kondisiPembelajaran());
            $query->orWhere('kompetensi_dasar', 'ilike', '%'.request()->q.'%');
            $query->whereHas('pembelajaran', $this->kondisiPembelajaran());
            $query->orWhere('kurikulum', 'ilike', '%'.request()->q.'%');
            $query->whereHas('pembelajaran', $this->kondisiPembelajaran());
            $query->orWhereHas('mata_pelajaran', function($query){ 
                $query->where('mata_pelajaran_id', 'ilike', '%'.request()->q.'%');
                $query->orWhere('nama', 'ilike', '%'.request()->q.'%');
            });
            $query->whereHas('pembelajaran', $this->kondisiPembelajaran());
        })
        ->when(request()->tingkat, function($query) {
            $query->where('kelas_'.request()->tingkat, '1');
        })
        ->when(request()->rombongan_belajar_id, function($query) {
            $query->whereHas('pembelajaran', function($query){
                $query->where('guru_id', request()->guru_id);
                $query->where('rombongan_belajar_id', request()->rombongan_belajar_id);
                $query->whereNotNull('kelompok_id');
                $query->where('sekolah_id', request()->sekolah_id);
                $query->where('semester_id', request()->semester_id);
                $query->orWhere('guru_pengajar_id', request()->guru_id);
                $query->where('rombongan_belajar_id', request()->rombongan_belajar_id);
                $query->whereNotNull('kelompok_id');
                $query->where('sekolah_id', request()->sekolah_id);
                $query->where('semester_id', request()->semester_id);
            });
        })
        ->when(request()->pembelajaran_id, function($query) {
            $query->whereHas('pembelajaran', function($query){
                $query->where('pembelajaran_id', request()->pembelajaran_id);
            });
        })
        ->paginate(request()->per_page);
        return response()->json(['status' => 'success', 'data' => $data]);
    }
    private function cariPembelajaran(){
        return function($query){
            $query->where('guru_id', request()->guru_id);
            if(request()->rombongan_belajar_id){
                $query->where('rombongan_belajar_id', request()->rombongan_belajar_id);
            }
            $query->whereNotNull('kelompok_id');
            $query->where('sekolah_id', request()->sekolah_id);
            $query->where('semester_id', request()->semester_id);
            $query->orWhere('guru_pengajar_id', request()->guru_id);
            if(request()->rombongan_belajar_id){
                $query->where('rombongan_belajar_id', request()->rombongan_belajar_id);
            }
            $query->whereNotNull('kelompok_id');
            $query->where('sekolah_id', request()->sekolah_id);
            $query->where('semester_id', request()->semester_id);
        };
    }
    public function get_data(){
        $data = [];
        if(request()->data == 'rombel'){
            $data = RombonganBelajar::where(function($query){
                $query->where('tingkat', request()->tingkat);
                $query->where('semester_id', request()->semester_id);
                $query->where('sekolah_id', request()->sekolah_id);
                $query->whereIn('jenis_rombel', [1, 16]);
                if(request()->add_kd){
                    $query->whereHas('pembelajaran', $this->kondisiPembelajaran());
                } else {
                    $query->whereHas('pembelajaran', $this->cariPembelajaran());
                }
            })->orderBy('nama')->get();
        }
        if(request()->data == 'mapel'){
            if(request()->add_kd){
                $data = Pembelajaran::where($this->kondisiPembelajaran())->orderBy('nama_mata_pelajaran')->get();
            } else {
                $data = Pembelajaran::where($this->cariPembelajaran())->orderBy('nama_mata_pelajaran')->get();
            }
        }
        return response()->json($data);
    }
    private function kurikulum($string){
        if(Str::contains($string, 'REV')){
            $kurikulum = 2017;
        } elseif(Str::contains($string, 'KTSP')){
            $kurikulum = 2006;
        } elseif(Str::contains($string, 'Pusat')){
            $kurikulum = 2021;
        } else {
            $kurikulum = 2013;
        }
        return $kurikulum;
    }
    public function save_kd(){
        request()->validate(
            [
                'tingkat' => 'required_if:add_kd,1',
                'rombongan_belajar_id' => 'required_if:add_kd,1',
                'mata_pelajaran_id' => 'required_if:add_kd,1',
                'kompetensi_id' => 'required_if:add_kd,1',
                'id_kompetensi' => 'required_if:add_kd,1',
                'kompetensi_dasar' => 'required_if:add_kd,1',
                'kompetensi_dasar_alias' => 'required_with:kompetensi_dasar_id',
            ],
            [
                'tingkat.required_if' => 'Tingkat Kelas tidak boleh kosong!!',
                'rombongan_belajar_id.required_if' => 'Rombongan Belajar tidak boleh kosong!!',
                'mata_pelajaran_id.required_if' => 'Mata Pelajaran tidak boleh kosong!!',
                'kompetensi_id.required_if' => 'Aspek Penilaian tidak boleh kosong!!',
                'id_kompetensi.required_if' => 'Kode Kompetensi Dasar tidak boleh kosong!!',
                'kompetensi_dasar.required_if' => 'Deskripsi Kompetensi Dasar tidak boleh kosong!!',
                'kompetensi_dasar.required_with' => 'Deskripsi Kompetensi Dasar Baru tidak boleh kosong!!',
            ]
        );
        if(request()->add_kd){
            $rombel = RombonganBelajar::select('rombongan_belajar_id', 'kurikulum_id')->with(['kurikulum' => function($query){
                $query->select('kurikulum_id', 'nama_kurikulum');
            }])->find(request()->rombongan_belajar_id);
            $kurikulum = $this->kurikulum($rombel->kurikulum->nama_kurikulum);
            $insert = KompetensiDasar::create([
                'kompetensi_dasar_id' => Str::uuid(),
                'id_kompetensi' => request()->id_kompetensi,
                'kompetensi_id' => request()->kompetensi_id,
                'mata_pelajaran_id' => request()->mata_pelajaran_id,
                'kelas_10' => (request()->tingkat == 10) ? 1 : 0,
                'kelas_11' => (request()->tingkat == 11) ? 1 : 0,
                'kelas_12' => (request()->tingkat == 12) ? 1 : 0,
                'kelas_13' => (request()->tingkat == 13) ? 1 : 0,
                'aktif'				=> 1,
                'kurikulum'			=> $kurikulum,
                'kompetensi_dasar' => request()->kompetensi_dasar,
                'user_id' => request()->user_id,
                'last_sync' => now(),
            ]);
            if($insert){
                $data = [
                    'color' => 'success',
                    'title' => 'Berhasil!',
                    'text' => 'Data Kompetensi Dasar berhasil disimpan',
                ];
            } else {
                $data = [
                    'color' => 'error',
                    'title' => 'Gagal!',
                    'text' => 'Data Kompetensi Dasar gagal disimpan. Silahkan coba beberapa saat lagi!',
                ];
            }
        } else {
            $kd = KompetensiDasar::find(request()->kompetensi_dasar_id);
            $kd->kompetensi_dasar_alias = request()->kompetensi_dasar_alias;
            if($kd->save()){
                $data = [
                    'color' => 'success',
                    'title' => 'Berhasil!',
                    'text' => 'Deskripsi Kompetensi Dasar berhasil diperbaharui',
                ];
            } else {
                $data = [
                    'color' => 'error',
                    'title' => 'Gagal!',
                    'text' => 'Deskripsi Kompetensi Dasar gagal diperbaharui. Silahkan coba beberapa saat lagi!',
                ];
            }
        }
        return response()->json($data);
    }
    public function update(){
        $insert = NULL;
        $kd = KompetensiDasar::find(request()->kompetensi_dasar_id);
        if(request()->mata_pelajaran_id){
            $mapel = MataPelajaran::withWhereHas('kompetensi_dasar')->find(request()->mata_pelajaran_id);
            foreach($mapel->kompetensi_dasar as $kd){
                $kompetensi_dasar_id[str_replace('.','',$kd->id_kompetensi)] = $kd->kompetensi_dasar_id;
            }
            $insert = KompetensiDasar::where('mata_pelajaran_id', request()->mata_pelajaran_id)->whereNotIn('kompetensi_dasar_id', $kompetensi_dasar_id)->update(['aktif' => 0]);
            $text = 'KD Mapel '.$mapel->nama.' dinonaktifkan sebanyak ('.$insert.')';
        } else {
            if(request()->has('aktif')){
                $kd->aktif = (request()->aktif) ? 0 : 1;
                $text = (request()->aktif) ? 'Data KD berhasil di non aktifkan!' : 'Data KD berhasil di aktifkan!';
            } else {
                $kd->kompetensi_dasar_alias = request()->kompetensi_dasar_alias;
                $text = 'Deskripsi Kompetensi Dasar berhasil diperbaharui';
            }
            $insert = $kd->save();
        }
        if($insert){
            $data = [
                'color' => 'success',
                'title' => 'Berhasil!',
                'text' => $text,
            ];
        } else {
            $data = [
                'color' => 'error',
                'title' => 'Gagal!',
                'text' => 'Data KD gagal diperbaharui. Silahkan coba beberapa saat lagi!',
            ];
        }
        return response()->json($data);
    }
}
