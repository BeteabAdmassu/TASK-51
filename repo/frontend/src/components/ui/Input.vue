<script setup>
const model = defineModel({
  type: String,
  default: '',
})

defineProps({
  label: {
    type: String,
    required: true,
  },
  type: {
    type: String,
    default: 'text',
  },
  placeholder: {
    type: String,
    default: '',
  },
  icon: {
    type: String,
    default: '',
  },
})
</script>

<template>
  <label class="field">
    <span class="field__label">{{ label }}</span>
    <div class="field__control">
      <span v-if="icon" class="field__icon">{{ icon }}</span>
      <input
        v-model="model"
        :type="type"
        :placeholder="placeholder"
      >
    </div>
  </label>
</template>

<style scoped>
.field {
  display: grid;
  gap: var(--space-2);
}

.field__label {
  font-size: 0.86rem;
  color: var(--color-text-muted);
}

.field__control {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  border-radius: var(--radius-md);
  border: 1px solid var(--color-border);
  padding: 0 12px;
  background: rgba(20, 26, 47, 0.45);
  transition: border-color var(--transition-fast), box-shadow var(--transition-fast);
}

.field__control:focus-within {
  border-color: var(--color-accent);
  box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.2);
}

.field__icon {
  color: var(--color-text-muted);
  font-size: 0.9rem;
}

input {
  flex: 1;
  border: none;
  outline: none;
  background: transparent;
  color: var(--color-text);
  padding: 12px 0;
}
</style>
