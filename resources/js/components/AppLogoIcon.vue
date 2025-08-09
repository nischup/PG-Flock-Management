<script setup lang="ts">
import type { HTMLAttributes } from 'vue'

defineOptions({
  inheritAttrs: false,
})

interface Props {
  className?: HTMLAttributes['class']
}

defineProps<Props>()
</script>

<template>
    <img src="/logo.png" :class="className" alt="App Logo" v-bind="$attrs" />
</template>
