<script setup>
definePage({
  meta: {
    action: 'read',
    subject: 'Internal',
    title: 'Input Nilai UKK',
  },
})
const form = ref({
  semester_id: $semester.semester_id,
  user_id: $user.user_id,
  guru_id: $user.guru_id,
  sekolah_id: $user.sekolah_id,
  periode_aktif: $semester.nama,
  data: null,
  nilai: {},
  rencana_ukk_id: null,
})
const errors = ref({
  rencana_ukk_id: undefined,
})
const loading = ref({
  body: false,
  table: false,
})
const showBtn = ref(false)
const confirmed = ref(false)
const rencanaUkk = ref([])
const dataSiswa = ref([])
const isConfirmDialogVisible = ref(false)
const isNotifVisible = ref(false)
const notif = ref({
  color: null,
  title: null,
  text: null,
})
onMounted(async () => {
  form.value.data = 'rencana'
  loading.value.body = true;
  await fetchData().then(() => {
    loading.value.body = false
  });
});
const fetchData = async () => {
  await $api('/ukk', {
    method: 'POST',
    body: form.value,
    onResponse({ response }) {
      let getData = response._data
      if (form.value.data === 'rencana') {
        rencanaUkk.value = getData.rencana_ukk
      }
      dataSiswa.value = getData.data_siswa
      getData.data_siswa.forEach((item) => {
        form.value.nilai[`${item.peserta_didik_id}#${item.nilai_ukk.anggota_rombel_id}`] = item.nilai_ukk.nilai
      })
    },
  })
}
const changeRencana = async (val) => {
  form.value.data = 'siswa'
  form.value.rencana_ukk_id = val
  loading.value.table = true
  console.log(val);
  await fetchData().then(() => {
    loading.value.table = false
    showBtn.value = true
  });
}
const onSubmit = async () => {
  confirmed.value = true
  form.value.data = 'nilai'
  await $api('/ukk/save', {
    method: 'POST',
    body: form.value,
    onResponse({ response }) {
      let getData = response._data
      confirmed.value = false
      if (getData.errors) {
        errors.value = getData.errors
      } else {
        isNotifVisible.value = true
        notif.value = getData
      }
    },
  })
}
const kesimpulanUkk = (item) => {
  var predikat = ''
  if (item.nilai_ukk && item.nilai_ukk.nilai) {
    var nilai = item.nilai_ukk.nilai
    /*if (nilai >= 90) {
        predikat = 'Sangat Kompeten';
    } else if (nilai >= 75 && nilai <= 89) {
        predikat = 'Kompeten';
    } else if (nilai >= 70 && nilai <= 74) {
        predikat = 'Cukup Kompeten';
    } else if (nilai < 70) {
        predikat = 'Belum Kompeten';
    }*/
    if (nilai >= 70) {
      predikat = 'Kompeten';
    } else {
      predikat = 'Belum Kompeten';
    }
  }
  return predikat;
}
const generateLink = (item) => {
  var link_cetak = null
  if (item.nilai_ukk.nilai) {
    link_cetak = `/cetak/sertifikat/${item.nilai_ukk.anggota_rombel_id}/${item.nilai_ukk.rencana_ukk_id}`
  }
  return link_cetak
}
const confirmClose = async () => {
  form.value.data = 'rencana'
  await fetchData();
}
</script>
<template>
  <VCard class="mb-6">
    <VCardItem class="pb-4">
      <VCardTitle>Input Nilai UKK</VCardTitle>
    </VCardItem>
    <VDivider />
    <VForm @submit.prevent="onSubmit">
      <template v-if="loading.body">
        <VCardText class="text-center">
          <VProgressCircular :size="60" indeterminate color="error" class="my-10" />
        </VCardText>
      </template>
      <template v-else>
        <VCardText>
          <VRow>
            <VCol cols="12">
              <VRow no-gutters>
                <VCol cols="12" md="3" class="d-flex align-items-center">
                  <label class="v-label text-body-2 text-high-emphasis" for="semester_id">Tahun Pelajaran</label>
                </VCol>
                <VCol cols="12" md="9">
                  <AppTextField id="semester_id" :value="$semester.nama" disabled />
                </VCol>
              </VRow>
            </VCol>
            <VCol cols="12">
              <VRow no-gutters>
                <VCol cols="12" md="3" class="d-flex align-items-center">
                  <label class="v-label text-body-2 text-high-emphasis" for="rencana_ukk_id">Paket Kompetensi</label>
                </VCol>
                <VCol cols="12" md="9">
                  <AppAutocomplete v-model="form.rencana_ukk_id" placeholder="== Pilih Paket Kompetensi =="
                    :items="rencanaUkk" clearable clear-icon="tabler-x" item-value="rencana_ukk_id" item-title="nama"
                    :error-messages="errors.rencana_ukk_id" @update:model-value="changeRencana" />
                </VCol>
              </VRow>
            </VCol>
          </VRow>
        </VCardText>
        <VCardText class="text-center" v-if="loading.table">
          <VProgressCircular :size="60" indeterminate color="error" class="my-10" />
        </VCardText>
        <VDivider />
        <template v-if="dataSiswa.length">
          <VTable class="text-no-wrap">
            <thead>
              <tr>
                <th>Nama Peserta Didik</th>
                <th class="text-center">Nilai</th>
                <th class="text-center">Kesimpan</th>
                <th class="text-center">Cetak</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="siswa in dataSiswa">
                <td>
                  <ProfileSiswa :item="siswa" />
                </td>
                <td>
                  <AppTextField
                    v-model="form.nilai[`${siswa.peserta_didik_id}#${siswa.nilai_ukk.anggota_rombel_id}`]" />
                </td>
                <td>{{ kesimpulanUkk(siswa) }}</td>
                <td class="text-center">
                  <VBtn prepend-icon="tabler-printer" color="success" size="small" :href="generateLink(siswa)"
                    target="_blank" v-if="generateLink(siswa)">Cetak</VBtn>
                </td>
              </tr>
            </tbody>
          </VTable>
          <VDivider />
        </template>
        <VCardText class="d-flex justify-end flex-wrap gap-3 pt-5 overflow-visible" v-if="showBtn">
          <VBtn variant="elevated" type="submit" :loading="confirmed" :disabled="confirmed">
            Simpan
          </VBtn>
        </VCardText>
      </template>
    </VForm>
    <ConfirmDialog v-model:isDialogVisible="isConfirmDialogVisible" v-model:isNotifVisible="isNotifVisible"
      confirmation-question="Apakah Anda yakin?" confirmation-text="" :confirm-color="notif.color"
      :confirm-title="notif.title" :confirm-msg="notif.text" @close="confirmClose" />
  </VCard>
</template>
