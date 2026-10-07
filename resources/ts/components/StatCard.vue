<script setup lang="ts">
import AppIcon, { type IconName } from './AppIcon.vue';

withDefaults(defineProps<{ label: string; value: number | string; icon: IconName; tone?: 'indigo' | 'rose' | 'amber' | 'emerald' }>(), {
    tone: 'indigo',
});

const tones = {
    indigo: 'bg-indigo-50 text-indigo-600',
    rose: 'bg-rose-50 text-rose-600',
    amber: 'bg-amber-50 text-amber-600',
    emerald: 'bg-emerald-50 text-emerald-600',
};
</script>

<template>
    <div class="card flex items-center gap-4 p-5">
        <div class="flex size-11 items-center justify-center rounded-lg" :class="tones[tone]">
            <AppIcon :name="icon" class="size-6" />
        </div>
        <div>
            <p class="text-sm text-slate-500">{{ label }}</p>
            <p class="text-2xl font-semibold text-slate-900">{{ value }}</p>
        </div>
    </div>
</template>
