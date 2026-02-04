<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/lib/axios';

const router = useRouter();

interface Project {
  id: number;
  title: string;
  location: string;
  image_url: string;
  status: 'planning' | 'in_progress' | 'finished';
}

const projects = ref<Project[]>([]);
const isLoading = ref(true);
const currentUser = ref<any>(null);

const visibleCount = ref(6);
const visibleProjects = computed(() => {
  return projects.value.slice(0, visibleCount.value);
});

const statusConfig: Record<string, { label: string, class: string }> = {
  planning: { label: 'Planejamento', class: 'bg-stone-100 text-stone-500 border-stone-200' },
  in_progress: { label: 'Em Andamento', class: 'bg-white/90 text-amber-700 border-white/50' },
  finished: { label: 'Entregue', class: 'bg-emerald-50 text-emerald-700 border-emerald-100' },
};

const goToProject = (id: number) => {
  router.push(`/projects/${id}`);
};

const goToPortfolio = () => {
  router.push('/portfolio');
};

const handleLogout = async () => {
  try {
    await api.post('/logout');
  } catch (error) {
    console.warn("Sessão já expirada.");
  } finally {
    localStorage.removeItem('is_logged_in');
    currentUser.value = null;
    alert("Você saiu do sistema.");
    window.location.reload();
  }
};

onMounted(async () => {
  try {
    const responseProjects = await api.get('/api/projects');
    projects.value = responseProjects.data;

    try {
        const responseUser = await api.get('/api/user');
        currentUser.value = responseUser.data;
        localStorage.setItem('is_logged_in', 'true');
    } catch (e) {
        currentUser.value = null;
        localStorage.removeItem('is_logged_in');
    }

  } catch (error) {
    console.error('Erro geral:', error);
  } finally {
    isLoading.value = false;
  }
});
</script>

<template>
  <div class="min-h-screen bg-stone-50 font-sans text-stone-800">
    
    <nav class="fixed w-full z-50 bg-white/95 backdrop-blur-md border-b border-stone-200 shadow-sm transition-all duration-300">
      <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <div class="text-2xl font-serif font-bold text-stone-900 tracking-tighter cursor-pointer" @click="router.push('/')">
          VDP<span class="text-amber-700">.</span>Construct
        </div>
        
        <div class="hidden md:flex gap-6 items-center text-sm font-medium text-stone-600">
          <RouterLink to="/portfolio" class="hover:text-amber-700 transition">Portfólio</RouterLink>
          <a href="#" class="hover:text-amber-700 transition">Sobre</a>
          
          <div class="h-6 w-px bg-stone-300 mx-2"></div>

          <div v-if="currentUser" class="flex items-center gap-6">
            
            <RouterLink 
              v-if="currentUser.is_admin"
              to="/admin"
              class="flex items-center gap-2 text-stone-500 hover:text-stone-900 transition group"
              title="Painel Administrativo"
            >
              <span class="p-1.5 bg-stone-100 rounded group-hover:bg-amber-100 group-hover:text-amber-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
              </span>
              <span class="text-xs font-bold uppercase tracking-wide">Admin</span>
            </RouterLink>

            <div class="h-6 w-px bg-stone-200"></div>

            <div class="flex items-center gap-3">
              <div class="text-right leading-tight hidden lg:block">
                <p class="text-[10px] text-stone-400 font-bold uppercase tracking-widest">Olá,</p>
                <p class="font-serif text-stone-800">{{ currentUser.name.split(' ')[0] }}</p>
              </div>

              <button @click="handleLogout" class="text-stone-400 hover:text-red-600 transition p-2 hover:bg-red-50 rounded-full group" title="Sair do sistema">
                <svg class="w-5 h-5 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
              </button>
            </div>
          </div>

          <div v-else class="flex items-center gap-3 animate-fade-in">
             <span class="text-stone-400 text-xs italic">Você não está logado</span>
             <RouterLink 
              to="/login" 
              class="px-5 py-2 bg-stone-900 text-white rounded-lg hover:bg-amber-700 transition shadow-lg shadow-stone-900/20 flex items-center gap-2"
            >
              <span>Área do Cliente</span>
              <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </RouterLink>
          </div>

        </div>
      </div>
    </nav>

    <header class="relative h-[75vh] flex items-center justify-center overflow-hidden">
      <div class="absolute inset-0 z-0">
        <img src="https://images.unsplash.com/photo-1631679706909-1844bbd07221?q=80&w=1920&auto=format&fit=crop" class="w-full h-full object-cover brightness-[0.4]" alt="Luxury Building" />
      </div>
      <div class="relative z-10 text-center px-6 max-w-4xl mt-16 animate-fade-in-up">
        <span class="text-amber-400 tracking-[0.2em] text-sm uppercase font-semibold">Alto Padrão em Curitiba</span>
        <h1 class="text-5xl md:text-7xl font-serif text-white mt-4 mb-6 leading-tight">Construindo o <br/><span class="italic font-light">extraordinário.</span></h1>
      </div>
    </header>

    <section class="py-24 px-6 max-w-7xl mx-auto pb-32">
      <div class="flex items-end justify-between mb-16">
        <div>
          <h2 class="text-4xl font-serif text-stone-900">Empreendimentos</h2>
          <div class="h-0.5 w-12 bg-amber-700 mt-4"></div>
        </div>
        <p class="text-stone-400 text-xs uppercase tracking-widest hidden md:block">Portfolio Selection</p>
      </div>

      <div v-if="isLoading" class="text-center py-20">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-stone-900 mx-auto"></div>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-12">
        <div 
          v-for="project in visibleProjects" 
          :key="project.id" 
          class="group cursor-pointer"
          @click="goToProject(project.id)"
        >
          <div class="relative h-[400px] bg-stone-200 overflow-hidden mb-5 rounded-sm">
            <img 
              :src="project.image_url" 
              class="w-full h-full object-cover transition duration-1000 ease-in-out group-hover:scale-105" 
            />
            
            <div class="absolute top-4 right-4 z-20">
              <span 
                class="px-2 py-1 text-[10px] font-bold uppercase tracking-widest backdrop-blur-md shadow-sm rounded-sm"
                :class="statusConfig[project.status]?.class"
              >
                {{ statusConfig[project.status]?.label }}
              </span>
            </div>

            <div class="absolute inset-0 bg-stone-900/20 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex items-center justify-center z-10">
              <span class="bg-white text-stone-900 px-6 py-3 uppercase text-xs font-bold tracking-[0.2em] shadow-xl hover:bg-stone-900 hover:text-white transition duration-300 transform translate-y-4 group-hover:translate-y-0">
                Ver Detalhes
              </span>
            </div>
          </div>
          
          <div>
            <h3 class="text-xl font-serif text-stone-900 group-hover:text-amber-800 transition duration-300 mb-1">
              {{ project.title }}
            </h3>
            <p class="text-stone-400 text-xs uppercase tracking-wider font-medium">
              {{ project.location }}
            </p>
          </div>
        </div>
      </div>

      <div class="mt-20 text-center">
        <button 
          @click="goToPortfolio"
          class="inline-block px-10 py-4 border border-stone-200 text-stone-500 hover:border-stone-900 hover:text-stone-900 transition duration-500 uppercase text-xs tracking-[0.2em]"
        >
          Explorar Portfolio Completo
        </button>
      </div>

    </section>

    <footer class="bg-white border-t border-stone-100 py-12 text-center text-stone-400 text-xs uppercase tracking-wider">
      <p>&copy; 2026 VDP Construct.</p>
    </footer>

  </div>
</template>