import './assets/tailwind.css'
// import './assets/main.css'
import '@fortawesome/fontawesome-free/css/all.min.css'

import VueApexCharts from "vue3-apexcharts"

import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import i18n from './i18n'

// ✅ import and create pinia
import { createPinia } from 'pinia'

const app = createApp(App)

// ✅ use pinia
const pinia = createPinia()
app.use(pinia)

// ✅ use router
app.use(router)

app.use(VueApexCharts)
app.use(i18n)

// ✅ register global click-outside directive
app.directive('click-outside', {
  beforeMount(el, binding) {
    el.clickOutsideEvent = function (event) {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value(event)
      }
    }
    document.addEventListener('click', el.clickOutsideEvent)
  },
  unmounted(el) {
    document.removeEventListener('click', el.clickOutsideEvent)
  }
})

app.mount('#app')
