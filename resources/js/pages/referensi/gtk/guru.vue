<script setup>
import Ptk from '@/views/referensi/Ptk.vue';
import { themeConfig } from '@themeConfig';
import { useHead } from '@unhead/vue';
useHead({
  title: `Data Guru | ${themeConfig.app.title}`
})
definePage({
  meta: {
    action: 'read',
    subject: 'Ref_Guru',
  },
})
</script>
<template>
  <div>
    <VCard title="Data Guru">
      <VCardText></VCardText>
      <VDivider />
      <Ptk :data="'guru'"></Ptk>
    </VCard>
  </div>
</template>
