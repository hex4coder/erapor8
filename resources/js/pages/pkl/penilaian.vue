<script setup>
definePage({
  meta: {
    action: 'read',
    subject: 'Pkl',
    title: 'Input Penilaian PKL',
  },
})
onMounted(async () => {
  loading.value.rombel = true
  await getData({
    data: 'rombel',
  }).then(() => {
    loading.value.rombel = false
  })
})
const defaultForm = ref({
  user_id: $user.user_id,
  guru_id: $user.guru_id,
  sekolah_id: $user.sekolah_id,
  semester_id: $semester.semester_id,
  periode_aktif: $semester.nama,
})
const form = ref({
  aksi: 'nilai',
  rombongan_belajar_id: null,
  pkl_id: null,
  nilai: {},
  deskripsi: {},
  catatan: {},
})
const errors = ref({
  rombongan_belajar_id: null,
  pkl_id: null,
})
const arrayData = ref({
  rombel: [],
  pkl: [],
  siswa: [],
  tp: [],
})
const loading = ref({
  rombel: false,
  pkl: false,
  body: false,
})
const getData = async (postData) => {
  const mergedForm = { ...postData, ...defaultForm.value, ...form.value }
  await $api("/praktik-kerja-lapangan/get-data", {
    method: "POST",
    body: mergedForm,
    async onResponse({ response }) {
      let getData = response._data
      if (postData.data == "rombel") {
        arrayData.value.rombel = getData
      }
      if (postData.data == "pkl") {
        arrayData.value.pkl = getData
      }
      if (postData.data == "siswa") {
        arrayData.value.siswa = getData.siswa
        arrayData.value.tp = getData.tp
        getData.siswa.forEach(siswa => {
          siswa.nilai_pkl.forEach(nilai => {
            form.value.nilai[`${nilai.tp_id}#${siswa.peserta_didik_id}`] = nilai.nilai
            form.value.deskripsi[`${nilai.tp_id}#${siswa.peserta_didik_id}`] = nilai.deskripsi
          })
          form.value.catatan[siswa.peserta_didik_id] = siswa.pd_pkl.catatan
        })
      }
    },
  })
}
const changeRombel = async (val) => {
  form.value.pkl_id = null
  arrayData.value.pkl = []
  if (val) {
    loading.value.pkl = true
    await getData({
      data: "pkl",
    }).then(() => {
      loading.value.pkl = false
    })
  }
}
const changeDudi = async (val) => {
  arrayData.value.siswa = []
  arrayData.value.tp = []
  if (val) {
    loading.value.body = true
    await getData({
      data: "siswa",
    }).then(() => {
      loading.value.body = false
    })
  }
}
const refVForm = ref();
const confirmed = ref(false)
const isNotifVisible = ref(false)
const isConfirmDialogVisible = ref(false)
const notif = ref({
  color: null,
  title: null,
  text: null,
})
const onFormSubmit = async () => {
  refVForm.value?.validate().then(({ valid: isValid }) => {
    if (isValid) saveData();
  });
}
const saveData = async () => {
  confirmed.value = true
  const mergedForm = { ...defaultForm.value, ...form.value }
  await $api('/praktik-kerja-lapangan/save', {
    method: 'POST',
    body: mergedForm,
    onResponse({ response }) {
      confirmed.value = false
      let getData = response._data
      if (getData.errors) {
        errors.value = getData.errors
      } else {
        formReset()
        isNotifVisible.value = true
        notif.value = getData
      }
    },
  })
}
const formReset = async () => {
  form.value = {
    aksi: 'nilai',
    rombongan_belajar_id: null,
    pkl_id: null,
    nilai: {},
    deskripsi: {},
    catatan: {},
  }
  errors.value = {
    rombongan_belajar_id: null,
    pkl_id: null,
  }
  arrayData.value = {
    rombel: [],
    pkl: [],
    siswa: [],
    tp: [],
  }
  loading.value.rombel = true
  await getData({
    data: 'rombel',
  }).then(() => {
    loading.value.rombel = false
  })
}
</script>
<template>
  <VCard class="mb-6">
    <VCardItem class="pb-4">
      <VCardTitle>Input Penilaian PKL</VCardTitle>
    </VCardItem>
    <VDivider />
    <VForm ref="refVForm" @submit.prevent="onFormSubmit">
      <VCardText>
        <VRow>
          <VCol cols="12">
            <VRow no-gutters>
              <VCol cols="12" md="3" class="d-flex align-items-center">
                <label class="v-label text-body-2 text-high-emphasis" for="rombongan_belajar_id">Rombongan
                  Belajar</label>
              </VCol>
              <VCol cols="12" md="9">
                <AppAutocomplete v-model="form.rombongan_belajar_id" placeholder="== Pilih Rombongan Belajar == "
                  :items="arrayData.rombel" clearable clear-icon="tabler-x" @update:model-value="changeRombel"
                  item-value="rombongan_belajar_id" item-title="nama" :loading="loading.rombel"
                  :disabled="loading.rombel" :error-messages="errors.rombongan_belajar_id" />
              </VCol>
            </VRow>
          </VCol>
          <VCol cols="12">
            <VRow no-gutters>
              <VCol cols="12" md="3" class="d-flex align-items-center">
                <label class="v-label text-body-2 text-high-emphasis" for="pkl_id">DUDI</label>
              </VCol>
              <VCol cols="12" md="9">
                <AppAutocomplete v-model="form.pkl_id" placeholder="== Pilih DUDI == " :items="arrayData.pkl" clearable
                  clear-icon="tabler-x" @update:model-value="changeDudi" item-value="pkl_id" item-title="nama_dudi"
                  :loading="loading.pkl" :disabled="loading.pkl" :error-messages="errors.pkl_id" />
              </VCol>
            </VRow>
          </VCol>
        </VRow>
      </VCardText>
      <VCardText class="text-center" v-if="loading.body">
        <VProgressCircular :size="60" indeterminate color="error" />
      </VCardText>
      <template v-if="arrayData.siswa.length">
        <VTable>
          <thead>
            <tr>
              <th>Nama Peserta Didik</th>
              <th>Tujuan Pembelajaran</th>
              <th class="text-center">Nilai</th>
              <th class="text-center">Deskripsi</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="siswa in arrayData.siswa">
              <tr>
                <td class="text-no-wrap border-right align-top" :rowspan="arrayData.tp.length + 2">
                  <ProfileSiswa :item="siswa" />
                </td>
              </tr>
              <template v-for="tp in arrayData.tp">
                <tr>
                  <td class="align-top">{{ tp.deskripsi }}</td>
                  <td class="align-top">
                    <AppTextField v-model="form.nilai[`${tp.tp_id}#${siswa.peserta_didik_id}`]"
                      :rules="[integerValidator, betweenValidator(form.nilai[`${tp.tp_id}#${siswa.peserta_didik_id}`], 0, 100)]"
                      placeholder="0-100" />
                  </td>
                  <td class="py-4">
                    <VTextarea label="Deskripsi" v-model="form.deskripsi[`${tp.tp_id}#${siswa.peserta_didik_id}`]" />
                  </td>
                </tr>
              </template>
              <tr>
                <td colspan="3" class="py-4">
                  <VTextarea placeholder="Catatan PKL..." v-model="form.catatan[siswa.peserta_didik_id]" />
                </td>
              </tr>
            </template>
          </tbody>
        </VTable>
        <VCardText class="d-flex justify-end flex-wrap gap-3 pt-5">
          <VBtn variant="elevated" type="submit" :loading="confirmed" :disabled="confirmed">
            Simpan
          </VBtn>
        </VCardText>
      </template>
    </VForm>
  </VCard>
  <ConfirmDialog v-model:isDialogVisible="isConfirmDialogVisible" v-model:isNotifVisible="isNotifVisible"
    confirmation-question="Apakah Anda yakin?" confirmation-text="Tindakan ini tidak dapat dikembalikan!"
    :confirm-color="notif.color" :confirm-title="notif.title" :confirm-msg="notif.text" @confirm="confirmDelete"
    @close="confirmClose" />
</template>
