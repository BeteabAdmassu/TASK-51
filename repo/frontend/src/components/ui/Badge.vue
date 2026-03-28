<script setup>
defineProps({
  tone: {
    type: String,
    default: 'info',
  },
})
</script>

<template>
  <span class="badge" :class="`badge--${tone}`">
    <slot />
  </span>
</template>

<style scoped>
.badge {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  padding: 4px 10px;
  font-size: 0.76rem;
  font-weight: 600;
  letter-spacing: 0.02em;
}

.badge--info {
  color: #dfe6ff;
  background: rgba(67, 97, 238, 0.25);
}

.badge--success {
  color: #cdfbe9;
  background: rgba(6, 214, 160, 0.25);
}
</style>
