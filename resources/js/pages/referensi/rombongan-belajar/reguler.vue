<script setup>
import RombonganBelajar from '@/views/referensi/RombonganBelajar.vue';
definePage({
  meta: {
    action: 'read',
    subject: 'Ref_Rombel',
    title: 'Rombongan Belajar',
  },
})
</script>
<template>
  <RombonganBelajar :jenis-rombel="1" card-title="Data Rombongan Belajar"></RombonganBelajar>
</template>
