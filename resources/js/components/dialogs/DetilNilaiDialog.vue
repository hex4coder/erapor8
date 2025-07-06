<script setup>
const props = defineProps({
    isDialogVisible: {
        type: Boolean,
        required: true,
    },
    itemData: {
        type: Object,
        required: true,
    },
    titleDetilNilai: {
        type: String,
        required: true,
    },
    merdeka: {
        type: Boolean,
        required: true,
    },
    isPpa: {
        type: Boolean,
        required: true,
    },
    dataSiswa: {
        type: Array,
        required: true,
    },
    subMapel: {
        type: Number,
        default: () => 0,
    }
})
const updateModelValue = val => {
    emit('update:isDialogVisible', val)
}
const emit = defineEmits([
    'update:isDialogVisible',
    'refresh'
])
const loadingBtn = ref(false)
const isAlertDialogVisible = ref(false)
const notif = ref({
    color: '',
    title: '',
    text: '',
})
const generateNilai = async () => {
    loadingBtn.value = true
    console.log('generateNilai');
    await $api('/dashboard/generate-nilai', {
        method: 'POST',
        body: {
            pembelajaran_id: props.itemData.pembelajaran_id,
            mata_pelajaran_id: props.itemData.mata_pelajaran_id,
            rombongan_belajar_id: props.itemData.rombongan_belajar_id,
        },
        onResponse({ request, response, options }) {
            let getData = response._data
            isAlertDialogVisible.value = true
            notif.value = {
                color: getData.color,
                title: getData.title,
                text: getData.text,
            }
        },
    })
}
const confirmAlert = () => {
    loadingBtn.value = false
    emit('refresh')
}
</script>

<template>
    <VDialog :model-value="props.isDialogVisible" @update:model-value="updateModelValue" max-width="100%" scrollable
        persistent content-class="scrollable-dialog">
        <DialogCloseBtn @click="updateModelValue(false)" v-if="!loadingBtn" />
        <VCard>
            <VCardItem class="pb-5">
                <VCardTitle>{{ titleDetilNilai }}</VCardTitle>
            </VCardItem>
            <VDivider />
            <VTable>
                <thead>
                    <template v-if="merdeka || isPpa">
                        <tr>
                            <th class="text-center">No</th>
                            <th class="text-center">Nama</th>
                            <th class="text-center">NISN</th>
                            <th class="text-center">Agama</th>
                            <th class="text-center">Nilai Akhir</th>
                            <th class="text-center">Capaian Kompetensi</th>
                        </tr>
                    </template>
                    <template v-else>
                        <tr>
                            <th class="text-center align-middle" rowspan="2">No</th>
                            <th class="text-center align-middle" rowspan="2">Nama</th>
                            <th class="text-center align-middle" rowspan="2">NISN</th>
                            <th class="text-center align-middle" rowspan="2">Agama</th>
                            <th class="text-center align-middle" colspan="2">Nilai Pengetahuan</th>
                            <th class="text-center align-middle" colspan="2">Nilai Keterampilan</th>
                        </tr>
                        <tr>
                            <th class="text-center">Angka</th>
                            <th class="text-center">Predikat</th>
                            <th class="text-center">Angka</th>
                            <th class="text-center">Predikat</th>
                        </tr>
                    </template>
                </thead>
                <tbody>
                    <template v-if="dataSiswa.length">
                        <tr v-for="(pd, index) in dataSiswa">
                            <td class="text-center">{{ index + 1 }}</td>
                            <td>{{ pd.nama }}</td>
                            <td class="text-center">{{ pd.nisn }}</td>
                            <td class="text-center">{{ pd.agama.nama }}</td>
                            <template v-if="merdeka || isPpa">
                                <td class="text-center">
                                    <template v-if="merdeka">
                                        {{ pd.nilai_akhir_kurmer?.nilai }}
                                    </template>
                                    <template v-else>
                                        {{ pd.nilai_akhir_pengetahuan?.nilai }}
                                    </template>
                                </td>
                            </template>
                            <td>
                                <template v-if="pd.deskripsi_mapel">
                                    <template
                                        v-if="pd.deskripsi_mapel.deskripsi_pengetahuan && pd.deskripsi_mapel.deskripsi_keterampilan">
                                        {{ pd.deskripsi_mapel.deskripsi_pengetahuan }}
                                        <hr>
                                        {{ pd.deskripsi_mapel.deskripsi_keterampilan }}
                                    </template>
                                    <template
                                        v-if="pd.deskripsi_mapel.deskripsi_pengetahuan && !pd.deskripsi_mapel.deskripsi_keterampilan">
                                        {{ pd.deskripsi_mapel.deskripsi_pengetahuan }}
                                    </template>
                                    <template
                                        v-if="!pd.deskripsi_mapel.deskripsi_pengetahuan && pd.deskripsi_mapel.deskripsi_keterampilan">
                                        {{ pd.deskripsi_mapel.deskripsi_keterampilan }}
                                    </template>
                                </template>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </VTable>
            <VDivider />
            <VCardText class="d-flex justify-end flex-wrap gap-3 pt-5 overflow-visible">
                <VBtn @click="$emit('update:isDialogVisible', false)" size="small" color="secondary"
                    :loading="loadingBtn" :disabled="loadingBtn">Tutup</VBtn>
                <VBtn @click="generateNilai" size="small" :loading="loadingBtn" :disabled="loadingBtn" v-if="subMapel">
                    Generate Nilai</VBtn>
            </VCardText>
        </VCard>
        <AlertDialog v-model:isDialogVisible="isAlertDialogVisible" :confirm-color="notif.color"
            :confirm-title="notif.title" :confirm-msg="notif.text" @confirm="confirmAlert"></AlertDialog>
    </VDialog>
</template>
