import { createApp } from 'vue'
import { createPinia } from 'pinia'
import Toast from 'vue-toastification'
import router from './router'
import App from './App.vue'
import vuetify from './plugins/vuetify'

import 'vue-toastification/dist/index.css'
import './assets/main.css'

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(vuetify)
app.use(Toast, {
  position: 'top-right',
  timeout: 3500,
  closeOnClick: true,
  pauseOnHover: true,
})

app.mount('#app')
