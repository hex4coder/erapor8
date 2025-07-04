<script setup>
definePage({
  meta: {
    action: 'read',
    subject: 'Web',
    title: 'Pusat Unduhan',
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
    const response = await useApi(createUrl('/setting/unduhan'));
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
        <VCardTitle>Pusat Unduhan</VCardTitle>
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
