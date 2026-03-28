import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import { setUnauthorizedHandler } from './services/api'
import { useAuthStore } from './stores/authStore'
import './assets/css/variables.css'
import './assets/css/base.css'
import './assets/css/components.css'
import App from './App.vue'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

const authStore = useAuthStore(pinia)

setUnauthorizedHandler(async () => {
  authStore.forceLogout()
  if (router.currentRoute.value.path !== '/login') {
    await router.push('/login')
  }
})

app.mount('#app')
