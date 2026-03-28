<script setup>
import { onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import Toast from './components/ui/Toast.vue'

const route = useRoute()
const toastMessage = ref('')
const toastType = ref('info')

const consumeToast = () => {
  const cachedToast = sessionStorage.getItem('roadlink_toast_message')
  const cachedType = sessionStorage.getItem('roadlink_toast_type')

  if (cachedToast) {
    toastMessage.value = cachedToast
    toastType.value = cachedType || 'info'
    sessionStorage.removeItem('roadlink_toast_message')
    sessionStorage.removeItem('roadlink_toast_type')
  }
}

onMounted(() => {
  const theme = localStorage.getItem('roadlink_theme') || 'dark'
  document.documentElement.dataset.theme = theme
  consumeToast()
})

watch(() => route.fullPath, consumeToast)
</script>

<template>
  <RouterView />
  <Toast
    :message="toastMessage"
    :type="toastType"
    @close="toastMessage = ''"
  />
</template>
