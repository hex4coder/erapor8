<script setup>
definePage({
  meta: {
    action: 'read',
    subject: 'Web',
    title: 'Daftar Perubahan Aplikasi',
  },
})
onMounted(async () => {
  await fetchData();
});
const loadingBody = ref(false)
const data = ref()
const fetchData = async () => {
  loadingBody.value = true;
  try {
    const response = await useApi(createUrl('/setting/changelog'));
    let getData = response.data
    data.value = getData.value.data
  } catch (error) {
    console.error(error);
  } finally {
    loadingBody.value = false;
  }
}
</script>
<template>
  <div>
    <VCard>
      <VCardItem class="pb-4">
        <VCardTitle>Daftar Perubahan Aplikasi</VCardTitle>
      </VCardItem>
      <VDivider />
      <VCardText class="text-center" v-if="loadingBody">
        <VProgressCircular :size="60" indeterminate color="error" class="my-10" />
      </VCardText>
      <VCardText v-else>
        <span v-html="data"></span>
      </VCardText>
    </VCard>
  </div>
</template>
<style lang="scss">
h3 {
  margin-bottom: 10px !important;
}

ol {
  margin: 0 20px 20px;
}
</style>
