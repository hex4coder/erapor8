<?php

namespace App\Console\Commands;
use Illuminate\Support\Facades\Http;
use Illuminate\Console\Command;
use App\Models\MstWilayah;
use App\Models\User;
use App\Models\Role;
use App\Models\Team;
use App\Models\Semester;
use App\Models\Sekolah;

class Register extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:register';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $npsn = $this->inputan('NPSN');
        $email = $this->inputan('Email Admin Dapodik');
        $password = $this->inputan('Password Dapodik');
        $valid = filter_var($email, FILTER_VALIDATE_EMAIL);
        if(!$valid){
            $this->error('Email tidak valid');
            $email = $this->inputan('Email Admin Dapodik');
        }
        try {
            $this->info('Proses registrasi aplikasi...');
            $response = Http::withOptions([
                'verify' => false,
            ])->retry(3, 100)->post('http://sync.erapor-smk.net/api/v8/register', [
                'npsn' => $npsn,
                'email' => $email,
                'password' => $password,
                'sekolah_id' => NULL,
            ]);
            $object = $response->object();
            $register = $this->create_user($object, $email, $password);
            if($register['error']){
                $this->error($register['message']);
            } else {
                $this->info($register['message']);
                if (PHP_OS_FAMILY === 'Windows') {
                    $url = 'http://localhost:8154';
                    exec("start $url");
                }
            }
        } catch (\Illuminate\Http\Client\RequestException $e) {
            if ($e->response->status() === 401) {
                $object = $e->response->object();
                $this->error(str_replace('<br>', ' ', $object->message));
            } else {
                $this->error("Error: " . $e->getMessage());
            }
        }
    }
    private function inputan($input){
        $data = $this->ask($input);
        if(!$data){
            $this->error($input.' tidak boleh kosong!');
            return $this->inputan($input);
        }
        return $data;
    }
    private function create_user($data, $email, $password){
        if(!$data->data){
            $this->error($data->message);
            exit;
        }
        $set_data = $data->data->sekolah;
        $bentuk_pendidikan = config('erapor.bentuk_pendidikan');
        $allowed = FALSE;
        if($bentuk_pendidikan){
            if(in_array($set_data->bentuk_pendidikan_id, $bentuk_pendidikan)){
                $allowed = TRUE;
            }
        }
        if($allowed){
            $get_kode_wilayah = $set_data->wilayah;
            $kode_wilayah = $set_data->kode_wilayah;
            $kecamatan = '-';
            $kabupaten = '-';
            $provinsi = '-';
            if($get_kode_wilayah){
                $kode_wilayah = $get_kode_wilayah->kode_wilayah;
                if($get_kode_wilayah->parrent_recursive){
                    $kecamatan = $get_kode_wilayah->parrent_recursive->nama;
                    if($get_kode_wilayah->parrent_recursive->parrent_recursive){
                        $kabupaten = $get_kode_wilayah->parrent_recursive->parrent_recursive->nama;
                        if($get_kode_wilayah->parrent_recursive->parrent_recursive->parrent_recursive){
                            $provinsi = $get_kode_wilayah->parrent_recursive->parrent_recursive->parrent_recursive->nama;
                            MstWilayah::updateOrCreate(
                                [
                                    'kode_wilayah' => $get_kode_wilayah->parrent_recursive->parrent_recursive->parrent_recursive->kode_wilayah,
                                ],
                                [
                                    'nama' => $get_kode_wilayah->parrent_recursive->parrent_recursive->parrent_recursive->nama,
                                    'id_level_wilayah' => $get_kode_wilayah->parrent_recursive->parrent_recursive->parrent_recursive->id_level_wilayah,
                                    'mst_kode_wilayah' => $get_kode_wilayah->parrent_recursive->parrent_recursive->parrent_recursive->mst_kode_wilayah,
                                    'negara_id' => $get_kode_wilayah->parrent_recursive->parrent_recursive->parrent_recursive->negara_id,
                                    'last_sync' => now(),
                                ]
                            );
                        }
                        MstWilayah::updateOrCreate(
                            [
                                'kode_wilayah' => $get_kode_wilayah->parrent_recursive->parrent_recursive->kode_wilayah,
                            ],
                            [
                                'nama' => $get_kode_wilayah->parrent_recursive->parrent_recursive->nama,
                                'id_level_wilayah' => $get_kode_wilayah->parrent_recursive->parrent_recursive->id_level_wilayah,
                                'mst_kode_wilayah' => $get_kode_wilayah->parrent_recursive->parrent_recursive->mst_kode_wilayah,
                                'negara_id' => $get_kode_wilayah->parrent_recursive->parrent_recursive->negara_id,
                                'last_sync' => now(),
                            ]
                        );
                    }
                    MstWilayah::updateOrCreate(
                        [
                            'kode_wilayah' => $get_kode_wilayah->parrent_recursive->kode_wilayah,
                        ],
                        [
                            'nama' => $get_kode_wilayah->parrent_recursive->nama,
                            'id_level_wilayah' => $get_kode_wilayah->parrent_recursive->id_level_wilayah,
                            'mst_kode_wilayah' => $get_kode_wilayah->parrent_recursive->mst_kode_wilayah,
                            'negara_id' => $get_kode_wilayah->parrent_recursive->negara_id,
                            'last_sync' => now(),
                        ]
                    );
                }
                MstWilayah::updateOrCreate(
                    [
                        'kode_wilayah' => $get_kode_wilayah->kode_wilayah,
                    ],
                    [
                        'nama' => $get_kode_wilayah->nama,
                        'id_level_wilayah' => $get_kode_wilayah->id_level_wilayah,
                        'mst_kode_wilayah' => $get_kode_wilayah->mst_kode_wilayah,
                        'negara_id' => $get_kode_wilayah->negara_id,
                        'last_sync' => now(),
                    ]
                );
            }
            $sekolah = Sekolah::updateOrCreate(
                ['sekolah_id' => $set_data->sekolah_id],
                [
                    'npsn' 					=> $set_data->npsn,
                    'nss' 					=> $set_data->nss,
                    'nama' 					=> $set_data->nama,
                    'alamat' 				=> $set_data->alamat_jalan,
                    'desa_kelurahan'		=> $set_data->desa_kelurahan,
                    'kode_wilayah'			=> $kode_wilayah,
                    'kecamatan' 			=> $kecamatan,
                    'kabupaten' 			=> $kabupaten,
                    'provinsi' 				=> $provinsi,
                    'kode_pos' 				=> $set_data->kode_pos,
                    'lintang' 				=> $set_data->lintang,
                    'bujur' 				=> $set_data->bujur,
                    'no_telp' 				=> $set_data->nomor_telepon,
                    'no_fax' 				=> $set_data->nomor_fax,
                    'email' 				=> $set_data->email,
                    'website' 				=> $set_data->website,
                    'status_sekolah'		=> $set_data->status_sekolah,
                    'bentuk_pendidikan_id'  => $set_data->bentuk_pendidikan_id,
                    'last_sync'				=> now(),
                ]
            );
            $semester = Semester::where('periode_aktif', 1)->first();
            $user = User::create([
                'sekolah_id' => $sekolah->sekolah_id,
                'name' => 'Administrator',
                'email' => $email,
                'password' => bcrypt($password),
                'last_sync' => now(),
            ]);
            $adminRole = Role::where('name', 'admin')->first();
            $team = Team::updateOrCreate([
                'name' => $semester->nama,
                'display_name' => $semester->nama,
                'description' => $semester->nama,
            ]);
            $user->addRole($adminRole, $team);
            return [
                'error'=> FALSE,
                'message' => 'Register berhasil'
            ];
        } else {
            return [
                'error'=> TRUE,
                'message' => 'Jenjang Sekolah Salah'
            ];
        }
    }
}
