<template>
  <div
    class="animate-pulse"
    :class="[hasRadiusOverride ? '' : 'rounded-md', hasBgOverride ? '' : 'bg-gray-200', $attrs.class]"
    :style="$attrs.style"
  ></div>
</template>

<script setup>
import { computed, useAttrs } from "vue";

defineOptions({ inheritAttrs: false });

const attrs = useAttrs();
// Plain string concatenation (Vue's default class merging) can't resolve
// conflicting Tailwind utilities like "rounded-md" vs "rounded-full" or
// "bg-gray-200" vs "bg-white/10" — both end up in the class list and
// whichever wins is arbitrary. Drop the default whenever the caller passes
// its own override (e.g. a lighter tone for a dark-background chip).
const hasRadiusOverride = computed(() => /(^|\s)rounded(-\S+)?(\s|$)/.test(attrs.class || ""));
const hasBgOverride = computed(() => /(^|\s)bg-(?!gradient)\S+/.test(attrs.class || ""));
</script>
