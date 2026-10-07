<script setup lang="ts">
import { useRoute } from 'vue-router';
import AppLayout from '@/layouts/AppLayout.vue';

const route = useRoute();
</script>

<template>
    <RouterView v-if="route.meta.guest" />
    <AppLayout v-else>
        <RouterView />
    </AppLayout>
</template>
