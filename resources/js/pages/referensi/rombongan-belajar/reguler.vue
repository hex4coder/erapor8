<script setup>
import RombonganBelajar from '@/views/referensi/RombonganBelajar.vue';
import { themeConfig } from '@themeConfig';
import { useHead } from '@unhead/vue';
useHead({
  title: `Rombongan Belajar | ${themeConfig.app.title}`
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
    <VCard title="Data Rombongan Belajar">
      <VCardText></VCardText>
      <VDivider />
      <RombonganBelajar :jenis-rombel="1"></RombonganBelajar>
    </VCard>
  </div>
</template>
