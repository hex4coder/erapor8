<script setup>
definePage({
  meta: {
    action: 'read',
    subject: 'Pkl',
    title: 'Daftar PKL'
  },
})
const loadingBody = ref(false)
const dataSiswa = ref([])
onMounted(async () => {
  loadingBody.value = true
  await $api("/praktik-kerja-lapangan/get-data", {
    method: "POST",
    body: {
      data: 'rapor',
      user_id: $user.user_id,
      guru_id: $user.guru_id,
      sekolah_id: $user.sekolah_id,
      semester_id: $semester.semester_id,
      periode_aktif: $semester.nama,
    },
    onResponse({ response }) {
      let getData = response._data
      loadingBody.value = false
      dataSiswa.value = getData
    },
  })
})
const cetakPkl = (peserta_didik_id, pkl_id) => {
  window.open(`/cetak/rapor-pkl/${peserta_didik_id}/${pkl_id}/${$user.guru_id}/${$semester.semester_id}`, `_blank`);
}
</script>
<template>
  <VCard class="mb-6">
    <VCardItem class="pb-4">
      <VCardTitle>Daftar Nilai PKL</VCardTitle>
    </VCardItem>
    <template v-if="loadingBody">
      <VDivider />
      <VCardText class="text-center">
        <VProgressCircular :size="60" indeterminate color="error" />
      </VCardText>
    </template>
    <template v-if="dataSiswa.length">
      <VTable>
        <thead>
          <tr>
            <th class="text-center">Nama Peserta Didik</th>
            <th class="text-center">Kelas</th>
            <th class="text-center">Prakerin</th>
            <th class="text-center">Dudi</th>
            <th class="text-center">Cetak</th>
          </tr>
        </thead>
        <tbody>
          <template v-for="siswa in dataSiswa">
            <tr>
              <template v-if="siswa.all_pd_pkl.length > 1">
                <td class="text-no-wrap border-right py-2" :rowspan="siswa.all_pd_pkl.length + 1">
                  <ProfileSiswa :item="siswa" />
                </td>
                <td :rowspan="siswa.all_pd_pkl.length + 1" class="border-right">
                  {{ siswa.kelas.nama }}
                </td>
              </template>
              <template v-else>
                <td class="text-no-wrap border-right py-2">
                  <ProfileSiswa :item="siswa" />
                </td>
                <td>
                  {{ siswa.kelas.nama }}
                </td>
                <td>
                  {{ siswa.all_pd_pkl[0].praktik_kerja_lapangan.dudi.judul_mou }}
                </td>
                <td>
                  {{ siswa.all_pd_pkl[0].praktik_kerja_lapangan.nama_dudi }}
                </td>
                <td class="text-center">
                  <VBtn prepend-icon="tabler-printer" color="success" size="small"
                    @click="cetakPkl(siswa.peserta_didik_id, siswa.all_pd_pkl[0].pkl_id)" target="_blank"
                    v-if="siswa.all_pd_pkl[0].nilai_pkl_count">Cetak</VBtn>
                </td>
              </template>
            </tr>
            <template v-for="pkl in siswa.all_pd_pkl" v-if="siswa.all_pd_pkl.length > 1">
              <tr>
                <td>
                  {{ pkl.praktik_kerja_lapangan.dudi.judul_mou }}
                </td>
                <td>
                  {{ pkl.praktik_kerja_lapangan.nama_dudi }}
                </td>
                <td>
                  <VBtn prepend-icon="tabler-printer" color="success" size="small"
                    @click="cetakPkl(siswa.peserta_didik_id, pkl.pkl_id)" target="_blank" v-if="pkl.nilai_pkl_count">
                    Cetak</VBtn>
                </td>
              </tr>
            </template>
          </template>
        </tbody>
      </VTable>
    </template>
  </VCard>
</template>
