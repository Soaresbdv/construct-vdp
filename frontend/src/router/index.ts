import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home', 
      component: () => import('../views/DashboardView.vue')
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginView.vue')
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/RegisterView.vue')
    },
    {
      path: '/dashboard',
      redirect: '/' 
    },
    {
      path: '/projects/:id',
      name: 'project-details',
      component: () => import('../views/ProjectDetailsView.vue')
    },
  ]
})

export default router