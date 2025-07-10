<script setup>
definePage({
  meta: {
    action: 'read',
    subject: 'Guru',
    title: 'Penilaian Sikap',
  },
})

const options = ref({
  page: 1,
  itemsPerPage: 10,
  searchQuery: '',
  selectedRole: null,
  sortby: 'updated_at',
  sortbydesc: 'DESC',
});
// Headers
const headers = [
  {
    title: 'Nama PD',
    key: 'nama_pd',
    sortable: false,
    nowrap: true,
  },
  {
    title: 'Kelas',
    key: 'rombel',
    sortable: false,
    nowrap: true,
  },
  {
    title: 'Dimensi Sikap',
    key: 'dimensi_sikap',
    sortable: false,
  },
  {
    title: 'Elemen Sikap',
    key: 'elemen_sikap',
    sortable: false,
  },
  {
    key: 'opsi_sikap',
    title: 'Opsi Sikap',
    sortable: false,
    align: 'center',
  },
  {
    key: 'deskripsi',
    title: 'Uraian Sikap',
    sortable: false,
  },
  {
    title: 'Aksi',
    key: 'aksi',
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
const isKurtilas = ref()
const fetchData = async () => {
  loadingTable.value = true;
  try {
    const response = await useApi(createUrl('/penilaian/nilai-sikap', {
      query: {
        user_id: $user.user_id,
        guru_id: $user.guru_id,
        sekolah_id: $user.sekolah_id,
        semester_id: $semester.semester_id,
        q: options.value.searchQuery,
        page: options.value.page,
        per_page: options.value.itemsPerPage,
        sortby: options.value.sortby,
        sortbydesc: options.value.sortbydesc,
      },
    }));
    let getData = response.data
    items.value = getData.value.data.data
    total.value = getData.value.data.total
    isKurtilas.value = getData.value.kurtilas
  } catch (error) {
    console.error(error);
  } finally {
    loadingTable.value = false;
  }
}
const isConfirmDialogVisible = ref(false)
const isNotifVisible = ref(false)
const notif = ref({
  color: '',
  title: '',
  text: '',
})
const deletedId = ref()
const aksi = (id, aksi) => {
  if (aksi == 'hapus') {
    deletedId.value = id
    isConfirmDialogVisible.value = true
  } else {
    //
  }
  console.log(aksi, id);
}
const confirmDelete = async () => {
  await $api("/penilaian/destroy", {
    method: "POST",
    body: {
      data: 'nilai-sikap',
      id: deletedId.value,
    },
    onResponse({ response }) {
      let getData = response._data;
      deletedId.value = undefined
      isNotifVisible.value = true
      notif.value = getData
    },
  });
}
const confirmClose = async () => {
  await fetchData();
}
</script>
<template>
  <section>
    <VCard class="mb-6">
      <VCardItem class="pb-4">
        <VCardTitle>Penilaian Sikap</VCardTitle>
      </VCardItem>
      <VDivider />
      <template v-if="isKurtilas">
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

            <VBtn prepend-icon="tabler-plus" :to="{ name: 'nilai-akademik-nilai-sikap-input-nilai' }">
              Input Nilai
            </VBtn>
          </div>
        </VCardText>
        <VDivider />
        <!-- SECTION datatable -->
        <VDataTableServer :items="items" :server-items-length="total" :headers="headers" :options="options"
          :loading="loadingTable" loading-text="Loading..." @update:sortBy="updateSortBy">
          <template #item.nama_pd="{ item }">
            <div class="d-flex align-center gap-x-4">
              <VAvatar size="34" :variant="!item.anggota_rombel.peserta_didik.photo ? 'tonal' : undefined"
                :color="!item.anggota_rombel.peserta_didik.photo ? 'success' : undefined">
                <VImg :src="item.anggota_rombel.peserta_didik.photo" />
              </VAvatar>
              <div class="d-flex flex-column">
                <h6 class="text-base">{{ item.anggota_rombel.peserta_didik.nama }}</h6>
                <div class="text-sm">
                  {{ item.anggota_rombel.peserta_didik.nisn }}
                </div>
              </div>
            </div>
          </template>
          <template #item.rombel="{ item }">
            {{ item.anggota_rombel.rombongan_belajar.nama }}
          </template>
          <template #item.dimensi_sikap="{ item }">
            {{ item.budaya_kerja?.aspek }}
          </template>
          <template #item.elemen_sikap="{ item }">
            {{ item.elemen_budaya_kerja?.elemen }}
          </template>
          <template #item.opsi_sikap="{ item }">
            <VChip :color="item.opsi_id ? 'success' : 'error'">
              {{ item.opsi_id ? 'Positif' : 'Negatif' }}
            </VChip>
          </template>
          <template #item.aksi="{ item }">
            <IconBtn :to="{ name: 'nilai-akademik-nilai-sikap-id', params: { id: item.nilai_budaya_kerja_id } }">
              <VTooltip activator="parent" location="top">
                Ubah Data
              </VTooltip>
              <VIcon icon="tabler-pencil" />
            </IconBtn>

            <IconBtn @click="aksi(item.nilai_budaya_kerja_id, 'hapus')">
              <VTooltip activator="parent" location="top">
                Hapus Data
              </VTooltip>
              <VIcon icon="tabler-trash" />
            </IconBtn>
          </template>
          <!-- pagination -->
          <template #bottom>
            <TablePagination v-model:page="options.page" :items-per-page="options.itemsPerPage" :total-items="total" />
          </template>
        </VDataTableServer>
        <!-- SECTION -->
      </template>
      <template v-else>
        <VCardText>
          <VAlert color="error" variant="tonal">
            Penilaian Sikap hanya bagi Satuan Pendidikan yang memiliki Rombongan Belajar Kurikulum 2013!
          </VAlert>
        </VCardText>
      </template>
    </VCard>
    <ConfirmDialog v-model:isDialogVisible="isConfirmDialogVisible" v-model:isNotifVisible="isNotifVisible"
      confirmation-question="Apakah Anda yakin?" confirmation-text="Tindakan ini tidak dapat dikembalikan!"
      :confirm-color="notif.color" :confirm-title="notif.title" :confirm-msg="notif.text" @confirm="confirmDelete"
      @close="confirmClose" />
  </section>
</template>
