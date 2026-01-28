import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      // Agora usamos o arquivo real com Lazy Loading
      component: () => import('../views/HomeView.vue')
    },
    {
      path: '/register',
      name: 'register',
      // Agora usamos o arquivo real com Lazy Loading
      component: () => import('../views/RegisterView.vue')
    }
  ]
})

export default router