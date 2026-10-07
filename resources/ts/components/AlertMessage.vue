<script setup lang="ts">
defineProps<{ message: string | null }>();
</script>

<template>
    <div v-if="message" role="alert" class="mb-4 rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-700 ring-1 ring-rose-200">
        {{ message }}
    </div>
</template>
