<template>
  <button
    class="base-button"
    :class="[variant, { 'icon-right': icon && iconPosition === 'right' }]"
    :disabled="disabled"
  >
    <img v-if="icon && iconPosition === 'left'" :src="icon" alt="" class="base-button-icon" />
    <span class="base-button-label"><slot /></span>
    <img v-if="icon && iconPosition === 'right'" :src="icon" alt="" class="base-button-icon" />
  </button>
</template>

<script setup>
defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary'].includes(value),
  },
  icon: {
    type: String,
    default: null,
  },
  iconPosition: {
    type: String,
    default: 'left',
    validator: (value) => ['left', 'right'].includes(value),
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});
</script>
