import { createRouter, createWebHistory } from 'vue-router'
import DashboardPage from '@/pages/DashboardPage.vue'
import DriverAvailableRidesPage from '@/pages/driver/DriverAvailableRidesPage.vue'
import DriverMyRidesPage from '@/pages/driver/DriverMyRidesPage.vue'
import DriverRideDetailPage from '@/pages/driver/DriverRideDetailPage.vue'
import LoginPage from '@/pages/LoginPage.vue'
import RegisterPage from '@/pages/RegisterPage.vue'
import RiderTripDetailPage from '@/pages/rider/RiderTripDetailPage.vue'
import RiderTripsPage from '@/pages/rider/RiderTripsPage.vue'
import { useAuthStore } from '@/stores/authStore'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      redirect: '/dashboard',
    },
    {
      path: '/login',
      name: 'login',
      component: LoginPage,
      meta: { public: true },
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterPage,
      meta: { public: true },
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: DashboardPage,
      meta: {
        requiresAuth: true,
        roles: ['rider', 'driver', 'fleet_manager', 'admin'],
      },
    },
    {
      path: '/rider/trips',
      name: 'rider-trips',
      component: RiderTripsPage,
      meta: {
        requiresAuth: true,
        roles: ['rider'],
      },
    },
    {
      path: '/rider/trips/:id',
      name: 'rider-trip-detail',
      component: RiderTripDetailPage,
      meta: {
        requiresAuth: true,
        roles: ['rider', 'admin'],
      },
    },
    {
      path: '/driver/available-rides',
      name: 'driver-available-rides',
      component: DriverAvailableRidesPage,
      meta: {
        requiresAuth: true,
        roles: ['driver', 'admin'],
      },
    },
    {
      path: '/driver/my-rides',
      name: 'driver-my-rides',
      component: DriverMyRidesPage,
      meta: {
        requiresAuth: true,
        roles: ['driver', 'admin'],
      },
    },
    {
      path: '/driver/my-rides/:id',
      name: 'driver-ride-detail',
      component: DriverRideDetailPage,
      meta: {
        requiresAuth: true,
        roles: ['driver', 'admin'],
      },
    },
  ],
})

router.beforeEach(async (to) => {
  const authStore = useAuthStore()
  await authStore.initialize()

  const requiresAuth = to.meta.requiresAuth
  const isPublic = to.meta.public

  if (requiresAuth && !authStore.token) {
    return { path: '/login' }
  }

  if (authStore.token && isPublic) {
    return { path: '/dashboard' }
  }

  if (requiresAuth && Array.isArray(to.meta.roles)) {
    const roleAllowed = to.meta.roles.includes(authStore.user?.role)
    if (!roleAllowed) {
      sessionStorage.setItem('roadlink_toast_message', 'You do not have access to this area.')
      sessionStorage.setItem('roadlink_toast_type', 'error')
      return { path: '/dashboard' }
    }
  }

  return true
})

export default router
