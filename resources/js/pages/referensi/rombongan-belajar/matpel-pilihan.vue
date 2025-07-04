<script setup>
import RombonganBelajar from '@/views/referensi/RombonganBelajar.vue';
definePage({
  meta: {
    action: 'read',
    subject: 'Ref_Rombel',
    title: 'Rombel Matpel Pilihan',
  },
})
</script>
<template>
  <RombonganBelajar :jenis-rombel="16" card-title="Rombel Matpel Pilihan"></RombonganBelajar>
</template>
