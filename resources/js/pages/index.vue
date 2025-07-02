<script setup>
import Administrator from '@/views/dashboard/Administrator.vue';
import Guru from '@/views/dashboard/Guru.vue';
import { themeConfig } from '@themeConfig';
import { useHead } from '@unhead/vue';
useHead({
  title: `Beranda | ${themeConfig.app.title}`
})
definePage({
  meta: {
    action: 'read',
    subject: 'Web',
  },
})
</script>

<template>
  <div>
    <template v-if="$can('read', 'Administrator')">
      <Administrator />
    </template>
    <template v-if="$can('read', 'Guru')">
      <Guru />
    </template>
  </div>
</template>
