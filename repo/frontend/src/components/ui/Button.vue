<script setup>
const props = defineProps({
  type: {
    type: String,
    default: 'button',
  },
  variant: {
    type: String,
    default: 'primary',
  },
  loading: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
})
</script>

<template>
  <button
    :type="type"
    class="btn"
    :class="[`btn--${props.variant}`, { 'btn--loading': loading }]"
    :disabled="disabled || loading"
  >
    <span v-if="loading" class="btn__spinner" />
    <slot />
  </button>
</template>

<style scoped>
.btn {
  width: 100%;
  display: inline-flex;
  justify-content: center;
  align-items: center;
  gap: var(--space-2);
  border: none;
  border-radius: var(--radius-md);
  padding: 12px 18px;
  font-weight: 600;
  cursor: pointer;
  transition: transform var(--transition-fast), box-shadow var(--transition-fast),
    background var(--transition-fast);
}

.btn:hover:not(:disabled) {
  transform: translateY(-1px);
}

.btn:disabled {
  opacity: 0.72;
  cursor: not-allowed;
}

.btn--primary {
  color: #eef2ff;
  background: linear-gradient(120deg, var(--color-accent), #5574ff);
  box-shadow: var(--shadow-sm);
}

.btn--ghost {
  color: var(--color-text);
  border: 1px solid var(--color-border);
  background: rgba(255, 255, 255, 0.04);
}

.btn__spinner {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  border: 2px solid rgba(255, 255, 255, 0.45);
  border-top-color: #ffffff;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
