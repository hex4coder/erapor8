<script setup>
import { themeConfig } from '@themeConfig';
import { useHead } from '@unhead/vue';
useHead({
  title: `Profil Pengguna | ${themeConfig.app.title}`
})
definePage({
  meta: {
    action: 'read',
    subject: 'Web',
  },
})
const loadingBody = ref(true)
const refVForm = ref()
const profilePhotoPath = ref()
const form = ref({
  name: null,
  email: null,
  password: null,
  confirm_password: null,
  photo: null,
})
onMounted(async () => {
  await fetchData();
});
const fetchData = async () => {
  try {
    const response = await useApi(createUrl('/auth/user'))
    let getData = response.data.value
    form.value.name = getData.name
    form.value.email = getData.email
    profilePhotoPath.value = getData.profile_photo_path
  } catch (error) {
    console.error(error);
  } finally {
    loadingBody.value = false;
  }
}
const onFormSubmit = () => {
  refVForm.value?.validate().then(({ valid: isValid }) => {
    if (isValid) {
      submitForm()
    }
  });
}
const photoError = ref(false)
const photoErrorMessage = ref()
const submitForm = async () => {
  const dataForm = new FormData();
  dataForm.append('data', 'user');
  dataForm.append('photo', (form.value.file) ? form.value.file : '');
  await $api('/auth/user', {
    method: 'POST',
    body: dataForm,
    onResponse({ request, response, options }) {
      let getData = response._data
      photoError.value = false
      photoErrorMessage.value = null
      if (getData.errors) {
        photoError.value = true
        photoErrorMessage.value = getData.message
      } else {
        isDialogVisible.value = true
      }
    }
  })
}
</script>
<template>
  <div>
    <VRow>
      <VCol cols="12">
        <VCard title="Informasi Profil Pengguna">
          <template #append>
            <span class="text-disabled text-subtitle-2">Perbaharui informasi profil dan alamat email akun Anda jika
              diperlukan.</span>
          </template>
          <VCardText></VCardText>
          <VCardText class="text-center" v-if="loadingBody">
            <VProgressCircular :size="60" indeterminate color="error" />
          </VCardText>
          <VCardText v-else>
            <VForm ref="refVForm" @submit.prevent="onFormSubmit">
              <VRow>
                <VCol cols="8">
                  <VRow>
                    <VCol cols="12">
                      <VRow no-gutters>
                        <VCol cols="12" md="3" class="d-flex align-items-center">
                          <label class="v-label text-body-2 text-high-emphasis" for="name">Nama Lengkap</label>
                        </VCol>
                        <VCol cols="12" md="9">
                          <AppTextField id="name" v-model="form.name" placeholder="Nama Lengkap"
                            persistent-placeholder />
                        </VCol>
                      </VRow>
                    </VCol>
                    <VCol cols="12">
                      <VRow no-gutters>
                        <VCol cols="12" md="3" class="d-flex align-items-center">
                          <label class="v-label text-body-2 text-high-emphasis" for="email">Email</label>
                        </VCol>
                        <VCol cols="12" md="9">
                          <AppTextField id="email" type="email" v-model="form.email" placeholder="Email"
                            persistent-placeholder />
                        </VCol>
                      </VRow>
                    </VCol>
                    <VCol cols="12">
                      <VRow no-gutters>
                        <VCol cols="12" md="3" class="d-flex align-items-center">
                          <label class="v-label text-body-2 text-high-emphasis" for="password">Kata Sandi Baru</label>
                        </VCol>
                        <VCol cols="12" md="9">
                          <AppTextField id="password" type="password" v-model="form.password"
                            placeholder="Kosongkan jika tidak ingin mengganti kata sandi" persistent-placeholder />
                        </VCol>
                      </VRow>
                    </VCol>
                    <VCol cols="12">
                      <VRow no-gutters>
                        <VCol cols="12" md="3" class="d-flex align-items-center">
                          <label class="v-label text-body-2 text-high-emphasis" for="confirm_password">Konfirmasi Kata
                            Sandi</label>
                        </VCol>
                        <VCol cols="12" md="9">
                          <AppTextField id="confirm_password" type="password" v-model="form.confirm_password"
                            placeholder="Kosongkan jika tidak ingin mengganti kata sandi" persistent-placeholder />
                        </VCol>
                      </VRow>
                    </VCol>
                    <VCol offset-md="3" cols="12" md="9" class="d-flex gap-4">
                      <VBtn type="submit">
                        Submit
                      </VBtn>
                    </VCol>
                  </VRow>
                </VCol>
                <VCol cols="4" class="text-center">
                  <VImg :alt="form.name" :src="profilePhotoPath" cover class="mx-auto mb-10" width="250px" />
                  <VFileInput v-model="form.file" :error="photoError" :error-messages="photoErrorMessage" accept="image/*"
                    label="Unggah Foto Profil" />
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
          <VCardText></VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
