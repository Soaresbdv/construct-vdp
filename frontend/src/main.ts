import './assets/main.css' // Importante para o Tailwind carregar

import { createApp } from 'vue'
import { createPinia } from 'pinia' // Já que instalamos, vamos ativar
import App from './App.vue'
import router from './router' // <--- 1. Importar o roteador

const app = createApp(App)

app.use(createPinia())
app.use(router) // <--- 2. "Ligar" o roteador no Vue (ESSENCIAL)

app.mount('#app')