<script setup>
definePage({
  meta: {
    action: 'read',
    subject: 'Administrator',
    title: 'Cek Pembaharuan',
  },
})
onMounted(async () => {
  await fetchData();
});
const loadingBody = ref(true)
const data = ref()
const fetchData = async () => {
  try {
    const response = await useApi(createUrl('/setting/check-update'));
    let getData = response.data
    data.value = getData.value?.data
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
        <VCardTitle>Cek Pembaharuan Aplikasi</VCardTitle>
      </VCardItem>
      <VDivider />
      <VCardText v-if="loadingBody">
        <VProgressLinear indeterminate bg-color="rgba(var(--v-theme-surface), 0.1)" :height="8" class="mb-0 mt-4" />
      </VCardText>
      <VCardText v-else-if="data.tersedia">
        <VAlert color="success" class="mb-4">
          Pembaharuan tersedia!
        </VAlert>
        <template v-if="data.win">
          <ol class="ps-1" type="a">
            <li><strong>Manual</strong>
              <ul style="padding-left: 10px;">
                <li>Buka Command Prompt (CMD) Run as Administrator</li>
                <li>Ketik <code>cd C:\eRaporSMK\dataweb</code> [enter]</li>
                <li>Ketik <code>php artisan app:version --force</code>. Tunggu sampai proses update versi aplikasi
                  selesai.
                </li>
                <li>Pastikan di akhir informasi di Command Prompt, versi aplikasi sudah berubah</li>
                <li>Cek kembali aplikasi e-Rapor SMK, jika ada yang gagal silahkan laporkan ke Tim Helpdesk</li>
              </ul>
            </li>
            <br>
            <li><strong>Menggunakan file .bat</strong>
              <ul style="padding-left: 10px;">
                <li>Silahkan download file <strong>updater e-Rapor SMK v8.0.0.bat</strong> <a
                    href="https://drive.google.com/drive/folders/1gjKuWR0-17xa8m5v2WNvqRvmnMgqDk8a?usp=drive_link"
                    target="_blank">disini</a>.</li>
                <li>Buka file <strong>updater e-Rapor SMK v8.0.0.bat</strong> dengan cara klik kanan dan pilih Run as
                  Administartor.</li>
                <li>Tunggu sampai proses update versi aplikasi selesai.</li>
              </ul>
            </li>
          </ol>
        </template>
        <template v-else>
          <ul class="ps-4">
            <li>Buka aplikasi Putty, jika belum ada, silahkan unduh <a
                href="https://www.chiark.greenend.org.uk/~sgtatham/putty/latest.html" target="_blank">disini</a>
              kemudian install</li>
            <li>Login ke SSH menggunakan username & password yang dimiliki</li>
            <li>Masuk ke root direktori aplikasi e-Rapor SMK di install.(Contoh <code>cd /var/www/html/erapor</code>
              [enter])</li>
            <li>Ketik <code>php artisan app:version --force</code>. Tunggu sampai proses update versi aplikasi selesai.
            </li>
            <li>Pastikan di akhir informasi di SSH, versi aplikasi sudah berubah</li>
            <li>Cek kembali aplikasi e-Rapor SMK, jika ada yang gagal silahkan laporkan ke Tim Helpdesk</li>
          </ul>
        </template>
      </VCardText>
      <VCardText v-else>
        <VAlert color="error">
          Pembaharuan belum tersedia!
        </VAlert>
      </VCardText>
    </VCard>
  </div>
</template>
