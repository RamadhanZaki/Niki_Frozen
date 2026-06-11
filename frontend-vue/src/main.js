import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import api from './plugins/axios'

const app = createApp(App)

// Daftarkan axios sebagai global property
app.config.globalProperties.$api = api

app.use(router)
app.mount('#app')