<script setup>
const props = defineProps({
  role: {
    type: String,
    default: 'rider',
  },
})

const linksByRole = {
  rider: ['Dashboard', 'My Trips', 'Shop', 'Notifications', 'Settings'],
  driver: ['Dashboard', 'Available Rides', 'My Rides', 'Vehicles', 'Notifications', 'Settings'],
  fleet_manager: ['Dashboard', 'Vehicles', 'Products', 'Reports', 'Notifications', 'Settings'],
  admin: ['Dashboard', 'Users', 'All Rides', 'Reports', 'Notifications', 'Settings'],
}
</script>

<template>
  <aside class="sidebar glass-card">
    <p class="sidebar__title">Workspace</p>
    <nav class="sidebar__links">
      <a
        v-for="link in linksByRole[props.role] || linksByRole.rider"
        :key="link"
        href="#"
      >
        {{ link }}
      </a>
    </nav>
  </aside>
</template>

<style scoped>
.sidebar {
  padding: var(--space-6);
  min-height: 100%;
}

.sidebar__title {
  margin: 0 0 var(--space-4);
  color: var(--color-text-muted);
  font-size: 0.86rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.sidebar__links {
  display: grid;
  gap: var(--space-2);
}

.sidebar__links a {
  text-decoration: none;
  color: var(--color-text);
  border-radius: var(--radius-sm);
  padding: 10px 12px;
  transition: background var(--transition-fast), transform var(--transition-fast);
}

.sidebar__links a:hover {
  background: rgba(67, 97, 238, 0.18);
  transform: translateX(4px);
}
</style>
