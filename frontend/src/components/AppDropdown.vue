<template>
  <div
    class="aq-dd-wrap"
    :class="{ open, light }"
    @click.stop="toggle"
  >
    <button type="button" class="aq-dd" :class="{ small, icon: current?.icon || leadIcon }">
      <component :is="leadIcon" v-if="leadIcon" class="aq-dd-lead-icon" :size="14" />
      <span
        v-if="current?.icon"
        class="aq-dd-icon"
        v-html="current.icon"
      ></span>
      <span class="aq-dd-label">{{ displayLabel }}</span>
      <svg class="aq-dd-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M6 9l6 6 6-6" />
      </svg>
    </button>

    <div class="aq-dd-menu" :class="{ 'right-0': align === 'right' }" @click.stop>
      <template v-for="(o, i) in options" :key="String(o.value)">
        <div
          class="aq-dd-item"
          :class="{ selected: isSelected(o.value) }"
          @click.stop="select(o)"
        >
          <span class="aq-dd-bar"></span>
          <span
            v-if="o.icon"
            class="aq-dd-item-icon"
            v-html="o.icon"
          ></span>
          <span>{{ o.label }}</span>
        </div>
        <div v-if="i < options.length - 1" class="aq-dd-divider"></div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  modelValue: { type: [String, Number, Array], default: null },
  options: { type: Array, default: () => [] },
  placeholder: { type: String, default: 'Select…' },
  align: { type: String, default: 'left' },
  small: { type: Boolean, default: false },
  light: { type: Boolean, default: false },
  multiple: { type: Boolean, default: false },
  // Static leading icon component shown regardless of the current selection
  // (e.g. IconCalendar for a date-sort dropdown, IconShield for a role filter).
  leadIcon: { type: [Object, Function], default: null },
})

const emit = defineEmits(['update:modelValue'])

const open = ref(false)

const current = computed(() => {
  if (props.multiple) {
    const arr = Array.isArray(props.modelValue) ? props.modelValue : []
    return props.options.filter((o) => arr.includes(o.value))
  }
  return props.options.find((o) => props.modelValue === o.value) || null
})

const displayLabel = computed(() => {
  if (props.multiple) {
    const arr = Array.isArray(current.value) ? current.value : []
    return arr.length ? arr.map((o) => o.label).join(", ") : props.placeholder
  }
  return current.value ? current.value.label : props.placeholder
})

const isSelected = (v) =>
  props.multiple
    ? Array.isArray(props.modelValue) && props.modelValue.includes(v)
    : props.modelValue === v

const toggle = () => {
  open.value = !open.value
}

const select = (o) => {
  if (props.multiple) {
    const arr = Array.isArray(props.modelValue) ? [...props.modelValue] : []
    const idx = arr.indexOf(o.value)
    if (idx >= 0) arr.splice(idx, 1)
    else arr.push(o.value)
    emit('update:modelValue', arr)
  } else {
    emit('update:modelValue', o.value)
    open.value = false
  }
}

const close = () => {
  open.value = false
}

const onKey = (e) => {
  if (e.key === 'Escape') close()
}

const onDocClick = () => {
  close()
}

document.addEventListener('click', onDocClick)
document.addEventListener('keydown', onKey)
</script>

<style scoped>
.aq-dd-wrap {
  position: relative;
}

.aq-dd {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #141922;
  border: 1px solid #242b36;
  border-radius: 8px;
  padding: 9px 14px;
  font-family: 'Nunito Sans', 'Kantumruy Pro', sans-serif;
  font-size: 14px;
  font-weight: 600;
  color: #e8ecf2;
  cursor: pointer;
  user-select: none;
  white-space: nowrap;
}

.aq-dd.small {
  padding: 6px 10px;
  font-size: 12.5px;
  border-radius: 6px;
}

.aq-dd:hover {
  border-color: #3a4354;
}

.aq-dd-icon,
.aq-dd-item-icon,
.aq-dd-lead-icon {
  display: inline-flex;
  align-items: center;
  flex-shrink: 0;
}

.aq-dd-lead-icon {
  color: #8992a3;
}

.aq-dd-wrap.light .aq-dd-lead-icon {
  color: #9ca3af;
}

.aq-dd-icon svg,
.aq-dd-item-icon svg {
  width: 16px;
  height: 16px;
}

.aq-dd-chevron {
  flex-shrink: 0;
  color: #8992a3;
  transition: transform 0.15s ease;
}

.aq-dd-wrap.open .aq-dd-chevron {
  transform: rotate(180deg);
}

.aq-dd-menu {
  position: absolute;
  top: calc(100% + 20px);
  left: 0;
  min-width: 170px;
  background: #1c222d;
  border: 1px solid #242b36;
  border-radius: 8px;
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.45);
  padding: 6px;
  z-index: 50;
  display: none;
  max-height: 240px;
  overflow-y: auto;
  overflow-x: hidden;
  /* Scroll works, scrollbar stays invisible (Firefox + IE/Edge legacy). */
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.aq-dd-menu::-webkit-scrollbar {
  display: none;
}

.aq-dd-wrap.open .aq-dd-menu {
  display: block;
}

.aq-dd-menu.right-0 {
  left: auto;
  right: 0;
}

.aq-dd-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 11px 12px;
  font-family: 'Nunito Sans', 'Kantumruy Pro', sans-serif;
  font-size: 14px;
  font-weight: 600;
  color: #8992a3;
  border-radius: 6px;
  cursor: pointer;
  position: relative;
  white-space: nowrap;
}

.aq-dd-item:hover {
  background: #242c39;
  color: #e8ecf2;
}

.aq-dd-item.selected {
  color: #e8ecf2;
}

.aq-dd-bar {
  width: 3px;
  height: 16px;
  border-radius: 2px;
  background: #5aa9f7;
  opacity: 0;
  flex-shrink: 0;
}

.aq-dd-item.selected .aq-dd-bar {
  opacity: 1;
}

.aq-dd-divider {
  height: 1px;
  background: #242b36;
  margin: 2px 4px;
}

/* Light (clean gray) variant for admin surfaces */
.aq-dd-wrap.light .aq-dd {
  background: #f9fafb;
  color: #4b5563;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  height: 36px;
  padding: 0 12px;
  font-size: 13px;
  font-weight: 500;
  box-shadow: none;
  transition: background-color 0.15s ease, border-color 0.15s ease;
}

.aq-dd-wrap.light .aq-dd.small {
  height: 32px;
  padding: 0 10px;
  font-size: 12.5px;
  border-radius: 8px;
}

.aq-dd-wrap.light .aq-dd:hover {
  background: #f3f4f6;
  border-color: #d1d5db;
}

.aq-dd-wrap.light.open .aq-dd {
  background: #f3f4f6;
  border-color: #9ca3af;
}

.aq-dd-wrap.light .aq-dd-label {
  color: #4b5563;
  font-weight: 500;
  overflow: hidden;
  text-overflow: ellipsis;
}

.aq-dd-wrap.light .aq-dd .aq-dd-chevron {
  color: #9ca3af;
  width: 14px;
  height: 14px;
}

.aq-dd-wrap.light .aq-dd-menu {
  background: #ffffff;
  border: 1px solid #f3f4f6;
  border-radius: 8px;
  box-shadow: 0 12px 28px rgba(15, 23, 42, 0.1);
  padding: 4px;
  left: 0;
  min-width: 100%;
  width: max-content;
  max-width: 260px;
}

/* align="right" needs to win over the light variant's own left:0 above,
   so a trigger sitting near a card's right edge doesn't spill outside it. */
.aq-dd-wrap.light .aq-dd-menu.right-0 {
  left: auto;
  right: 0;
}

.aq-dd-wrap.light .aq-dd-item {
  color: #4b5563;
  font-size: 12px;
  font-weight: 500;
  padding: 9px 10px;
  margin-bottom: 4px;
  border-radius: 6px;
  background: transparent;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.aq-dd-wrap.light .aq-dd-item:last-child {
  margin-bottom: 0;
}

.aq-dd-wrap.light .aq-dd-item:hover {
  background: #f9fafb;
  color: #111827;
}

.aq-dd-wrap.light .aq-dd-item.selected {
  background: #f0fdfa;
  color: #0d9488;
  font-weight: 600;
}

.aq-dd-wrap.light .aq-dd-bar {
  display: none;
}

.aq-dd-wrap.light .aq-dd-divider {
  display: none;
}
</style>
