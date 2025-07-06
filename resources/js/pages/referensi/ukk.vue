<script setup>
definePage({
  meta: {
    action: 'read',
    subject: 'Kaprog',
    title: 'Ref. Uji Kompetensi Keahlian',
  },
})
const options = ref({
  page: 1,
  itemsPerPage: 10,
  searchQuery: '',
  sortby: 'name',
  sortbydesc: 'ASC',
});
// Headers
const headers = [
  {
    title: 'Kompetensi Keahlian',
    key: 'jurusan',
    sortable: false,
  },
  {
    title: 'Nomor Paket',
    key: 'nomor_paket',
    sortable: false,
  },
  {
    title: 'Nama Paket',
    key: 'nama_paket_en',
    sortable: false,
  },
  {
    title: 'Jml Unit',
    key: 'unit_ukk_count',
    align: 'center',
    sortable: false,
  },
  {
    title: 'Status',
    key: 'status',
    align: 'center',
    sortable: false,
  },
  {
    title: 'Aksi',
    key: 'actions',
    align: 'center',
    sortable: false,
    nowrap: true,
  },
]
const items = ref([])
const total = ref(0)
const loadingTable = ref(false)
onMounted(async () => {
  await fetchData();
});
watch(options, async () => {
  await fetchData();
}, { deep: true });
watch(
  () => options.value.searchQuery,
  () => {
    options.value.page = 1
  }
)
const updateSortBy = (val) => {
  options.value.sortby = val[0]?.key
  options.value.sortbydesc = val[0]?.order
}
const fetchData = async () => {
  loadingTable.value = true;
  try {
    const response = await useApi(createUrl('/referensi/ukk', {
      query: {
        q: options.value.searchQuery,
        page: options.value.page,
        per_page: options.value.itemsPerPage,
        periode_aktif: $semester.nama,
        sekolah_id: $user.sekolah_id,
        role_id: options.value.selectedRole,
        sortby: options.value.sortby,
        sortbydesc: options.value.sortbydesc,
      },
    }));
    let getData = response.data
    items.value = getData.value.data.data
    total.value = getData.value.data.total
  } catch (error) {
    console.error(error);
  } finally {
    loadingTable.value = false;
  }
}
const isDialogVisible = ref(false)
const dialogTitle = ref()
const errors = ref({
  jurusan_id: null,
  kurikulum_id: null,
})
const defaultForm = {
  semester_id: $semester.semester_id,
  user_id: $user.user_id,
  guru_id: $user.guru_id,
  sekolah_id: $user.sekolah_id
}
const form = ref({
  jurusan_id: null,
  kurikulum_id: null,
  nomor_paket: {},
  nama_paket_id: {},
  nama_paket_en: {},
  status: {},
  kode_unit: {},
  nama_unit_id: {},
  nama_unit_en: {}
})
const dataJurusan = ref([])
const dataKurikulum = ref([])
const loadingJurusan = ref(false)
const loadingKurikulum = ref(false)
const ambilData = async (data) => {
  const newForm = {
    data: data,
    user_id: $user.user_id,
    guru_id: $user.guru_id,
    sekolah_id: $user.sekolah_id,
    semester_id: $semester.semester_id,
    periode_aktif: $semester.nama
  };
  const mergedForm = { ...newForm, ...form.value };
  await $api('/referensi/get-data', {
    method: 'POST',
    body: mergedForm,
    onResponse({ response }) {
      let getData = response._data
      loadingJurusan.value = false
      loadingKurikulum.value = false
      if (data == 'jurusan') {
        dataJurusan.value = getData
      }
      if (data == 'kurikulum') {
        dataKurikulum.value = getData
      }
    }
  })
}
const getAksi = ref()
const addNew = async () => {
  getItem.value = { data: 'add' }
  getAksi.value = 'add'
  loadingJurusan.value = true
  loadingKurikulum.value = true
  isDialogVisible.value = true
  dialogTitle.value = 'Tambah Data UKK'
  await ambilData('jurusan');
}
const changeJurusan = async (val) => {
  console.log('changeJurusan', val)
  await ambilData('kurikulum')
}
const changeKurikulum = async (val) => {
  console.log('changeKurikulum', val)
}
const getItem = ref()
const isConfirmDialogVisible = ref(false)
const isNotifVisible = ref(false)
const notif = ref({
  color: null,
  title: null,
  text: null,
})
const isSubmitBtn = ref()
const confirmationText = ref()
const changeStatus = async (item, status) => {
  getItem.value = { data: 'status', paket_ukk_id: item.paket_ukk_id, status: status }
  isConfirmDialogVisible.value = true
  confirmationText.value = (status) ? 'Tindakan ini akan menonaktifkan data Paket UKK!' : 'Tindakan ini akan mengaktifkan data Paket UKK!'
}
const aksi = async (aksi, item) => {
  console.log('aksi: ', aksi);

  getAksi.value = aksi
  getItem.value = { data: aksi, ...item }
  if (aksi == 'add_unit') {
    dialogTitle.value = 'Tambah Data Unit UKK'
  }
  if (aksi == 'edit') {
    dialogTitle.value = 'Perbaharui Data UKK'
  }
  if (aksi == 'detil') {
    isSubmitBtn.value = false
    dialogTitle.value = 'Detil Data UKK'
  } else {
    isSubmitBtn.value = true
  }
  if (aksi == 'hapus') {
    /*if (item.paket_ukk_id) {
      getItem.value = { data: 'hapus', paket_ukk_id: item.paket_ukk_id }
    } else {
      getItem.value = { data: 'hapus_unit', unit_ukk_id: item.unit_ukk_id }
    }*/
    isConfirmDialogVisible.value = true
    confirmationText.value = 'Tindakan ini tidak dapat dikembalikan!'
  } else {
    isDialogVisible.value = true
  }
}
const confirmDelete = async (val) => {
  if (val) {
    await $api('/referensi/ukk/update', {
      method: 'POST',
      body: getItem.value,
      onResponse({ response }) {
        let getData = response._data
        getItem.value = null
        isDialogVisible.value = false
        isNotifVisible.value = true
        notif.value = getData
      },
    })
  }
}
const confirmClose = async () => {
  await fetchData();
}
const postData = async () => {
  const mergedForm = { ...defaultForm, ...form.value, ...getItem.value };
  await $api('/referensi/ukk/save', {
    method: 'POST',
    body: mergedForm,
    onResponse({ response }) {
      let getData = response._data
      if (getData.errors) {
        errors.value = getData.errors
      } else {
        isDialogVisible.value = false
        isNotifVisible.value = true
        notif.value = getData
        form.value = {
          jurusan_id: null,
          kurikulum_id: null,
          nomor_paket: {},
          nama_paket_id: {},
          nama_paket_en: {},
          status: {},
          kode_unit: {},
          nama_unit_id: {},
          nama_unit_en: {}
        }
        errors.value = {
          jurusan_id: null,
          kurikulum_id: null,
        }
      }
    },
  })
}
const saveData = async (val) => {
  if (val) {
    await postData()
  }
}
</script>
<template>
  <div>
    <VCard class="mb-6">
      <VCardItem class="pb-4">
        <VCardTitle>Referensi Uji Kompetensi Keahlian</VCardTitle>
      </VCardItem>
      <VDivider />
      <VCardText class="d-flex flex-wrap gap-4">
        <div class="me-3 d-flex gap-3">
          <AppSelect v-model="options.itemsPerPage" :items="[
            { value: 10, title: '10' },
            { value: 25, title: '25' },
            { value: 50, title: '50' },
            { value: 100, title: '100' },
          ]" style="inline-size: 6.25rem;" />
        </div>
        <VSpacer />

        <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">
          <div style="inline-size: 15.625rem;">
            <AppTextField v-model="options.searchQuery" placeholder="Cari Data" />
          </div>
          <VBtn prepend-icon="tabler-plus" @click="addNew">
            Tambah Data
          </VBtn>
        </div>
      </VCardText>
      <VDivider />
      <VDataTableServer :items="items" :server-items-length="total" :headers="headers" :options="options"
        :loading="loadingTable" loading-text="Loading..." @update:sortBy="updateSortBy">
        <template #item.jurusan="{ item }">
          {{ item.jurusan.nama_jurusan }}
        </template>
        <template #item.status="{ item }">
          <VChip size="x-small" color="success" variant="elevated" v-if="item.status">
            Aktif
          </VChip>
          <VChip size="x-small" color="error" variant="elevated" v-else>
            Non Aktif
          </VChip>
        </template>
        <template #item.actions="{ item }">
          <IconBtn @click="aksi('add_unit', item)">
            <VTooltip activator="parent" location="top">
              Tambah Unit
            </VTooltip>
            <VIcon icon="tabler-plus" />
          </IconBtn>
          <VBtn icon variant="text" color="medium-emphasis">
            <VIcon icon="tabler-dots-vertical" />
            <VMenu activator="parent">
              <VList>
                <VListItem @click="aksi('detil', item)">
                  <template #prepend>
                    <VIcon icon="tabler-search" />
                  </template>
                  <VListItemTitle>Detil</VListItemTitle>
                </VListItem>
                <VListItem @click="changeStatus(item, item.status)">
                  <template #prepend>
                    <VIcon icon="tabler-x" v-if="item.status" />
                    <VIcon icon="tabler-check" v-else />
                  </template>
                  <VListItemTitle v-if="item.status">Non Aktifkan</VListItemTitle>
                  <VListItemTitle v-else>Aktifkan</VListItemTitle>
                </VListItem>
                <VListItem @click="aksi('edit', item)">
                  <template #prepend>
                    <VIcon icon="tabler-pencil" />
                  </template>
                  <VListItemTitle>Ubah Data</VListItemTitle>
                </VListItem>
                <VListItem @click="aksi('hapus', item)">
                  <template #prepend>
                    <VIcon icon="tabler-trash" />
                  </template>
                  <VListItemTitle>Hapus Data</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>
          </VBtn>
        </template>
        <template #bottom>
          <TablePagination v-model:page="options.page" :items-per-page="options.itemsPerPage" :total-items="total" />
        </template>
      </VDataTableServer>
      <DefaultDialog v-model:isDialogVisible="isDialogVisible" v-model:isSubmitBtn="isSubmitBtn"
        :dialog-title="dialogTitle" :errors="errors" @confirm="saveData">
        <template #content>
          <template v-if="getAksi == 'add'">
            <VRow>
              <VCol cols="12">
                <VRow no-gutters>
                  <VCol cols="12" md="3" class="d-flex align-items-center">
                    <label class="v-label text-body-2 text-high-emphasis" for="jurusan_id">Kompetensi Keahlian</label>
                  </VCol>
                  <VCol cols="12" md="9">
                    <AppSelect v-model="form.jurusan_id" placeholder="== Pilih Kompetensi Keahlian == "
                      :items="dataJurusan" clearable clear-icon="tabler-x" @update:model-value="changeJurusan"
                      item-value="jurusan_id" item-title="nama_jurusan_sp" :loading="loadingJurusan"
                      :disabled="loadingJurusan" :error-messages="errors.jurusan_id" />
                  </VCol>
                </VRow>
              </VCol>
              <VCol cols="12">
                <VRow no-gutters>
                  <VCol cols="12" md="3" class="d-flex align-items-center">
                    <label class="v-label text-body-2 text-high-emphasis" for="kurikulum_id">Kurikulum</label>
                  </VCol>
                  <VCol cols="12" md="9">
                    <AppSelect v-model="form.kurikulum_id" placeholder="== Pilih Kurikulum ==" :items="dataKurikulum"
                      clearable clear-icon="tabler-x" @update:model-value="changeKurikulum" item-value="kurikulum_id"
                      item-title="nama_kurikulum" :loading="loadingKurikulum" :disabled="loadingKurikulum"
                      :error-messages="errors.kurikulum_id" />
                  </VCol>
                </VRow>
              </VCol>
            </VRow>
          </template>
          <template v-if="getAksi == 'add_unit'">
            <VRow>
              <VCol cols="12">
                <VRow no-gutters>
                  <VCol cols="12" md="3" class="d-flex align-items-center">
                    <label class="v-label text-body-2 text-high-emphasis" for="jurusan_id">Kompetensi Keahlian</label>
                  </VCol>
                  <VCol cols="12" md="9">
                    <AppTextField v-model="getItem.jurusan.nama_jurusan" disabled />
                  </VCol>
                </VRow>
              </VCol>
              <VCol cols="12">
                <VRow no-gutters>
                  <VCol cols="12" md="3" class="d-flex align-items-center">
                    <label class="v-label text-body-2 text-high-emphasis">Kode Kompetensi</label>
                  </VCol>
                  <VCol cols="12" md="9">
                    <AppTextField v-model="getItem.kode_kompetensi" disabled />
                  </VCol>
                </VRow>
              </VCol>
              <VCol cols="12">
                <VRow no-gutters>
                  <VCol cols="12" md="3" class="d-flex align-items-center">
                    <label class="v-label text-body-2 text-high-emphasis">Nomor Paket</label>
                  </VCol>
                  <VCol cols="12" md="9">
                    <AppTextField v-model="getItem.nomor_paket" disabled />
                  </VCol>
                </VRow>
              </VCol>
              <VCol cols="12">
                <VRow no-gutters>
                  <VCol cols="12" md="3" class="d-flex align-items-center">
                    <label class="v-label text-body-2 text-high-emphasis">Judul Paket</label>
                  </VCol>
                  <VCol cols="12" md="9">
                    <AppTextField v-model="getItem.nama_paket_id" disabled />
                  </VCol>
                </VRow>
              </VCol>
            </VRow>
          </template>
          <template v-if="getAksi == 'edit'">
            <VRow>
              <VCol cols="12">
                <VRow no-gutters>
                  <VCol cols="12" md="3" class="d-flex align-items-center">
                    <label class="v-label text-body-2 text-high-emphasis" for="jurusan_id">Kompetensi Keahlian</label>
                  </VCol>
                  <VCol cols="12" md="9">
                    <AppTextField v-model="getItem.jurusan.nama_jurusan" disabled />
                  </VCol>
                </VRow>
              </VCol>
              <VCol cols="12">
                <VRow no-gutters>
                  <VCol cols="12" md="3" class="d-flex align-items-center">
                    <label class="v-label text-body-2 text-high-emphasis">Kode Kompetensi</label>
                  </VCol>
                  <VCol cols="12" md="9">
                    <AppTextField v-model="getItem.kode_kompetensi" disabled />
                  </VCol>
                </VRow>
              </VCol>
              <VCol cols="12">
                <VRow no-gutters>
                  <VCol cols="12" md="3" class="d-flex align-items-center">
                    <label class="v-label text-body-2 text-high-emphasis">Nomor Paket</label>
                  </VCol>
                  <VCol cols="12" md="9">
                    <AppTextField v-model="getItem.nomor_paket" />
                  </VCol>
                </VRow>
              </VCol>
              <VCol cols="12">
                <VRow no-gutters>
                  <VCol cols="12" md="3" class="d-flex align-items-center">
                    <label class="v-label text-body-2 text-high-emphasis">Judul Paket (ID)</label>
                  </VCol>
                  <VCol cols="12" md="9">
                    <AppTextField v-model="getItem.nama_paket_id" />
                  </VCol>
                </VRow>
              </VCol>
              <VCol cols="12">
                <VRow no-gutters>
                  <VCol cols="12" md="3" class="d-flex align-items-center">
                    <label class="v-label text-body-2 text-high-emphasis">Judul Paket (EN)</label>
                  </VCol>
                  <VCol cols="12" md="9">
                    <AppTextField v-model="getItem.nama_paket_en" />
                  </VCol>
                </VRow>
              </VCol>
              <VCol cols="12">
                <VRow no-gutters>
                  <VCol cols="12" md="3" class="d-flex align-items-center">
                    <label class="v-label text-body-2 text-high-emphasis">Status</label>
                  </VCol>
                  <VCol cols="12" md="9">
                    <AppSelect v-model="getItem.status" :items="[
                      { value: null, title: '== Pilih Status ==' },
                      { value: 1, title: 'Aktif' },
                      { value: 0, title: 'Tidak Aktif' },
                    ]" />
                  </VCol>
                </VRow>
              </VCol>
            </VRow>
          </template>
        </template>
        <template #table>
          <template v-if="getAksi == 'add'">
            <VTable>
              <thead>
                <tr>
                  <th class="text-center">Nomor Paket</th>
                  <th class="text-center">Judul Paket (ID)</th>
                  <th class="text-center">Judul Paket (EN)</th>
                  <th class="text-center">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="i in 10">
                  <td>
                    <AppTextField v-model="form.nomor_paket[i]" />
                  </td>
                  <td>
                    <AppTextField v-model="form.nama_paket_id[i]" />
                  </td>
                  <td>
                    <AppTextField v-model="form.nama_paket_en[i]" />
                  </td>
                  <td>
                    <AppSelect v-model="form.status[i]" :items="[
                      { value: null, title: '== Pilih Status ==' },
                      { value: 1, title: 'Aktif' },
                      { value: 0, title: 'Tidak Aktif' },
                    ]" />
                  </td>
                </tr>
              </tbody>
            </VTable>
          </template>
          <template v-if="getAksi == 'add_unit'">
            <VTable>
              <thead>
                <tr>
                  <th class="text-center" rowspan="2">Kode Unit</th>
                  <th class="text-center" colspan="2">Nama Unit Kompetensi</th>
                </tr>
                <tr>
                  <th class="text-center">Bahasa Indonesia</th>
                  <th class="text-center">Bahasa Inggris</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="i in 10">
                  <td>
                    <AppTextField v-model="form.kode_unit[i]" />
                  </td>
                  <td>
                    <AppTextField v-model="form.nama_unit_id[i]" />
                  </td>
                  <td>
                    <AppTextField v-model="form.nama_unit_en[i]" />
                  </td>
                </tr>
              </tbody>
            </VTable>
          </template>
          <template v-if="getAksi == 'edit'">
            <VTable>
              <thead>
                <tr>
                  <th class="text-center" rowspan="2">Kode Unit</th>
                  <th class="text-center" colspan="2">Nama Unit Kompetensi</th>
                  <th class="text-center" rowspan="2">#</th>
                </tr>
                <tr>
                  <th class="text-center">Bahasa Indonesia</th>
                  <th class="text-center">Bahasa Inggris</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(unit_ukk, index) in getItem.unit_ukk">
                  <td>
                    <AppTextField v-model="unit_ukk.kode_unit" />
                  </td>
                  <td>
                    <AppTextField v-model="unit_ukk.nama_unit_id" />
                  </td>
                  <td>
                    <AppTextField v-model="unit_ukk.nama_unit_en" />
                  </td>
                  <td class="text-center">
                    <IconBtn @click="aksi('hapus', { unit_ukk_id: unit_ukk.unit_ukk_id })">
                      <VTooltip activator="parent" location="top">
                        Hapus Unit
                      </VTooltip>
                      <VIcon icon="tabler-trash" color="error" />
                    </IconBtn>
                  </td>
                </tr>
              </tbody>
            </VTable>
          </template>
          <template v-if="getAksi == 'detil'">
            <VTable>
              <tbody>
                <tr>
                  <td>Kompetensi Keahlian</td>
                  <td>: {{ getItem.jurusan.nama_jurusan }}</td>
                </tr>
                <tr>
                  <td>Kurikulum</td>
                  <td>: {{ getItem.kurikulum.nama_kurikulum }}</td>
                </tr>
                <tr>
                  <td>Nomor Paket</td>
                  <td>: {{ getItem.nomor_paket }}</td>
                </tr>
                <tr>
                  <td>Judul Paket (ID)</td>
                  <td>: {{ getItem.nama_paket_id }}</td>
                </tr>
                <tr>
                  <td>Judul Paket (EN)</td>
                  <td>: {{ getItem.nama_paket_en }}</td>
                </tr>
                <tr>
                  <td>Status</td>
                  <td>:
                    <VChip size="x-small" color="success" variant="elevated" v-if="getItem.status">
                      Aktif
                    </VChip>
                    <VChip size="x-small" color="error" variant="elevated" v-else>
                      Non Aktif
                    </VChip>
                  </td>
                </tr>
                <tr>
                  <td>Unit UKK ({{ getItem.unit_ukk_count }})</td>
                  <td>
                    <VTable density="compact" v-if="getItem.unit_ukk_count">
                      <thead>
                        <tr>
                          <th rowspan="2" class="text-center align-middle">Kode Unit</th>
                          <th colspan="2" class="text-center">Nama Unit Kompetensi</th>
                        </tr>
                        <tr>
                          <th class="text-center">Bahasa Indonesia</th>
                          <th class="text-center">Bahasa Inggris</th>
                        </tr>
                      </thead>
              <tbody>
                <tr v-for="(unit_ukk, index) in getItem.unit_ukk">
                  <td>{{ unit_ukk.kode_unit }}</td>
                  <td>{{ unit_ukk.nama_unit_id }}</td>
                  <td>{{ unit_ukk.nama_unit_en }}</td>
                </tr>
              </tbody>
            </VTable>
            </td>
            </tr>
            </tbody>
            </VTable>
          </template>
        </template>
      </DefaultDialog>
    </VCard>
    <ConfirmDialog v-model:isDialogVisible="isConfirmDialogVisible" v-model:isNotifVisible="isNotifVisible"
      confirmation-question="Apakah Anda yakin?" :confirmation-text="confirmationText" :confirm-color="notif.color"
      :confirm-title="notif.title" :confirm-msg="notif.text" @confirm="confirmDelete" @close="confirmClose" />
  </div>
</template>
