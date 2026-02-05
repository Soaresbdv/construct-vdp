<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
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
const isScrolled = ref(false); 

const statusConfig: Record<string, { label: string, class: string }> = {
  planning: { label: 'Breve Lançamento', class: 'bg-stone-100 text-stone-600 border-stone-200' },
  in_progress: { label: 'Em Obras', class: 'bg-amber-50 text-amber-700 border-amber-200' },
  finished: { label: 'Entregue', class: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
};

const goToProject = (id: number) => {
  router.push(`/projects/${id}`);
};

const handleLogout = async () => {
  try { await api.post('/logout'); } catch (e) {}
  localStorage.removeItem('is_logged_in');
  currentUser.value = null; 
  window.location.reload();
};

const handleScroll = () => {
  isScrolled.value = window.scrollY > 50;
};

onMounted(async () => {
  window.addEventListener('scroll', handleScroll);

  try {
    const resProjects = await api.get('/api/projects');
    projects.value = resProjects.data;

    try { 
        const resUser = await api.get('/api/user'); 
        currentUser.value = resUser.data; 
        localStorage.setItem('is_logged_in', 'true');
    } catch (e) {
        currentUser.value = null;
        localStorage.removeItem('is_logged_in');
    }

  } catch (error) {
    console.error("Erro geral na dashboard:", error);
  } finally {
    isLoading.value = false;
  }
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
  <div class="bg-white font-sans text-stone-800 selection:bg-amber-100">
    
    <nav 
      class="fixed w-full z-50 top-0 left-0 px-6 transition-all duration-300 ease-in-out border-b"
      :class="isScrolled ? 'bg-white/95 backdrop-blur-md py-4 border-stone-100 text-stone-900 shadow-sm' : 'bg-transparent py-6 border-transparent text-stone-900'"
    >
      <div class="max-w-7xl mx-auto flex justify-between items-center">
        <div class="text-2xl font-serif font-bold tracking-tighter cursor-pointer hover:opacity-80 transition" @click="router.push('/')">
          VDP<span class="text-amber-600">.</span>
        </div>
        
        <div class="hidden md:flex items-center gap-8 text-xs font-bold uppercase tracking-widest">
           <RouterLink to="/portfolio" class="hover:text-amber-600 transition">Obras</RouterLink>
           <RouterLink to="/about" class="hover:text-amber-600 transition">Sobre</RouterLink>
           
           <div v-if="currentUser" class="flex items-center gap-6 ml-4 pl-6 border-l border-stone-300 animate-fade-in">
             
             <RouterLink 
                v-if="currentUser.is_admin" 
                to="/admin" 
                class="flex items-center gap-2 bg-stone-900 text-white px-3 py-1.5 rounded text-[10px] font-bold tracking-widest hover:bg-amber-600 transition shadow-md"
             >
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Painel
             </RouterLink>

             <div class="text-right leading-tight">
                <p class="text-[9px] text-stone-400 font-medium normal-case">Olá,</p>
                <p class="font-serif font-bold text-sm text-stone-900">{{ currentUser.name.split(' ')[0] }}</p>
             </div>

             <button @click="handleLogout" class="text-stone-400 hover:text-red-500 transition p-1" title="Sair da Conta">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
             </button>
           </div>

           <div v-else class="flex items-center gap-4 ml-8 pl-8 border-l border-stone-300">
              <RouterLink 
                to="/login" 
                class="group flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-stone-900 hover:text-amber-600 transition"
              >
                <span>Entrar</span>
                <svg class="w-4 h-4 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
              </RouterLink>
           </div>

        </div>
      </div>
    </nav>

    <div v-if="isLoading" class="h-screen w-full flex items-center justify-center bg-white fixed z-[60]">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-amber-700"></div>
    </div>

    <div v-else>
        <header class="pt-32 pb-20 px-6 max-w-7xl mx-auto min-h-[85vh] flex flex-col justify-center">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="order-2 lg:order-1 space-y-8 animate-fade-in-up">
                    <div class="inline-flex items-center gap-3">
                        <div class="h-px w-8 bg-amber-600"></div>
                        <span class="text-amber-600 text-xs font-bold uppercase tracking-[0.3em]">Desde 2010</span>
                    </div>
                    
                    <h1 class="text-5xl md:text-7xl font-serif text-stone-900 leading-[1.1]">
                        Arquitetura que <br> <span class="italic text-stone-400 font-light">inspira a vida.</span>
                    </h1>
                    
                    <p class="text-lg text-stone-500 font-light leading-relaxed max-w-md">
                        Projetamos marcos urbanos que unem a solidez da engenharia à arte de viver bem. Conheça nossos empreendimentos exclusivos.
                    </p>

                    <div class="pt-4 flex gap-4">
                        <button 
                            @click="router.push('/portfolio')"
                            class="px-8 py-4 bg-stone-900 text-white text-xs font-bold uppercase tracking-widest hover:bg-amber-700 transition duration-300"
                        >
                            Ver Projetos
                        </button>
                    </div>
                </div>

                <div class="order-1 lg:order-2 relative h-[500px] lg:h-[600px] w-full">
                    <div class="absolute right-0 top-0 w-4/5 h-full overflow-hidden rounded-sm shadow-2xl z-10">
                        <img 
                            src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?q=80&w=1920&auto=format&fit=crop" 
                            class="w-full h-full object-cover hover:scale-105 transition duration-1000"
                        />
                    </div>
                    <div class="absolute left-0 bottom-12 w-2/5 h-3/5 overflow-hidden rounded-sm shadow-xl z-20 border-4 border-white hidden md:block">
                         <img 
                            src="https://images.unsplash.com/photo-1497366216548-37526070297c?q=80&w=800&auto=format&fit=crop" 
                            class="w-full h-full object-cover"
                        />
                    </div>
                </div>
            </div>
        </header>

        <section class="bg-stone-50 py-24 px-6">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row justify-between items-end mb-16 gap-4">
                    <div>
                        <h2 class="text-4xl font-serif text-stone-900 mb-2">Portfolio Selection</h2>
                        <div class="h-1 w-20 bg-amber-600"></div>
                    </div>
                    <p class="text-xs font-bold uppercase tracking-widest text-stone-400">
                        Mostrando {{ projects.length }} Projetos
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div 
                        v-for="project in projects" 
                        :key="project.id"
                        class="group bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 cursor-pointer border border-stone-100"
                        @click="goToProject(project.id)"
                    >
                        <div class="relative h-[300px] overflow-hidden">
                            <div class="absolute top-4 left-4 z-10">
                                <span 
                                    class="px-3 py-1 text-[10px] font-bold uppercase tracking-widest border rounded bg-white/90 backdrop-blur-sm shadow-sm"
                                    :class="statusConfig[project.status]?.class"
                                >
                                    {{ statusConfig[project.status]?.label }}
                                </span>
                            </div>
                            <img 
                                :src="project.image_url" 
                                class="w-full h-full object-cover transition duration-700 group-hover:scale-110"
                            />
                            <div class="absolute inset-0 bg-stone-900/0 group-hover:bg-stone-900/20 transition duration-300 flex items-center justify-center">
                                <span class="opacity-0 group-hover:opacity-100 bg-white text-stone-900 px-6 py-3 text-xs font-bold uppercase tracking-widest transform translate-y-4 group-hover:translate-y-0 transition duration-300">
                                    Visualizar
                                </span>
                            </div>
                        </div>

                        <div class="p-8">
                            <h3 class="text-2xl font-serif text-stone-900 mb-2 group-hover:text-amber-700 transition">
                                {{ project.title }}
                            </h3>
                            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-stone-400">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                {{ project.location }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-20 text-center">
                    <button 
                        @click="router.push('/portfolio')"
                        class="inline-block px-10 py-4 border border-stone-300 text-stone-500 hover:border-stone-900 hover:text-stone-900 transition uppercase text-xs font-bold tracking-[0.2em]"
                    >
                        Carregar Mais Obras
                    </button>
                </div>
            </div>
        </section>

        <footer class="bg-stone-900 text-stone-400 py-16 px-6">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-8 text-center md:text-left">
                <div>
                    <h2 class="text-2xl font-serif font-bold text-white mb-2">VDP<span class="text-amber-600">.</span></h2>
                    <p class="text-xs uppercase tracking-widest opacity-50">Excelência em cada detalhe.</p>
                </div>
                <div class="flex gap-8 text-xs font-bold uppercase tracking-widest">
                    <a href="#" class="hover:text-white transition">Instagram</a>
                    <a href="#" class="hover:text-white transition">LinkedIn</a>
                    <a href="#" class="hover:text-white transition">Contato</a>
                </div>
                <p class="text-[10px] uppercase tracking-widest opacity-30">&copy; 2026 VDP Construct.</p>
            </div>
        </footer>

    </div>
  </div>
</template>