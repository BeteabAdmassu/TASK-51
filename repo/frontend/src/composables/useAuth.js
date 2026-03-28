import { computed } from 'vue'
import { useAuthStore } from '@/stores/authStore'

export const useAuth = () => {
  const authStore = useAuthStore()

  return {
    user: computed(() => authStore.user),
    isAuthenticated: computed(() => authStore.isAuthenticated),
    isLoading: computed(() => authStore.isLoading),
    error: computed(() => authStore.error),
    login: authStore.login,
    register: authStore.register,
    logout: authStore.logout,
    fetchMe: authStore.fetchMe,
    initialize: authStore.initialize,
  }
}
