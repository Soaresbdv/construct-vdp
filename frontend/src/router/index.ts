import { createRouter, createWebHistory } from 'vue-router'
import api from '@/lib/axios';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('../views/DashboardView.vue')
    },
    {
      path: '/about',
      name: 'about',
      component: () => import('../views/AboutView.vue')
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
    
    {
      path: '/admin',
      name: 'admin',
      component: () => import('../views/AdminView.vue'),
      
      beforeEnter: async (to, from, next) => {
        console.log("Verificando credenciais...");
        
        try {
          const response = await api.get('/api/user');
          const user = response.data;
          
          console.log("Usuário encontrado:", user.email, "| Admin:", user.is_admin);

          if (user.is_admin) {
            console.log("Acesso liberado.");
            next();
          } else {
            console.log("Acesso negado (Não é admin).");
            alert("Acesso restrito a administradores.");
            next('/');
          }
        } catch (error) {
          console.log("Erro de autenticação (Não logado). Redirecionando...");
          next('/login');
        }
      }
    },
    {
      path: '/portfolio',
      name: 'portfolio',
      component: () => import('../views/PortfolioView.vue') // Vamos criar este arquivo já já
    },
  ]
})

export default router