<script setup>
import RombonganBelajar from '@/views/referensi/RombonganBelajar.vue';
import { themeConfig } from '@themeConfig';
import { useHead } from '@unhead/vue';
useHead({
  title: `Rombel Matpel Pilihan | ${themeConfig.app.title}`
})
definePage({
  meta: {
    action: 'read',
    subject: 'Ref_Rombel',
  },
})
</script>
<template>
  <div>
    <VCard title="Rombel Matpel Pilihan">
      <VCardText></VCardText>
      <VDivider />
      <RombonganBelajar :jenis-rombel="16"></RombonganBelajar>
    </VCard>
  </div>
</template>
