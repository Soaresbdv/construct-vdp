<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/lib/axios';

const router = useRouter();

interface Project {
  id: number;
  title: string;
  location: string;
  image_url: string;
  status: 'planning' | 'in_progress' | 'finished';
  description?: string;
}

const projects = ref<Project[]>([]);
const isLoading = ref(true);
const currentUser = ref<any>(null);
const activeProjectId = ref<number>(0); 

const statusConfig: Record<string, { label: string, class: string }> = {
  planning: { label: 'BREVE LANÇAMENTO', class: 'border-stone-400 text-stone-500' },
  in_progress: { label: 'EM OBRAS', class: 'border-amber-600 text-amber-600' },
  finished: { label: 'ENTREGUE', class: 'border-emerald-700 text-emerald-700' },
};

let observer: IntersectionObserver | null = null;

const setupObserver = () => {
  const options = {
    root: null,
    rootMargin: '-45% 0px -45% 0px', 
    threshold: 0
  };

  observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const id = Number(entry.target.getAttribute('data-id'));
        if (id) activeProjectId.value = id;
      }
    });
  }, options);

  nextTick(() => {
    const items = document.querySelectorAll('.project-scroll-item');
    items.forEach((el) => observer?.observe(el));
  });
};

const activeProjectImage = computed(() => {
  const active = projects.value.find(p => p.id === activeProjectId.value);

  return active ? active.image_url : (projects.value[0]?.image_url || '');
});

const goToProject = (id: number) => router.push(`/projects/${id}`);
const handleLogout = async () => {
  try { await api.post('/logout'); } catch (e) {}
  localStorage.removeItem('is_logged_in');
  window.location.reload();
};

onMounted(async () => {
  try {
    const res = await api.get('/api/projects');
    projects.value = res.data;
    
    if (projects.value.length > 0) {
        activeProjectId.value = projects.value[0]?.id ?? 0;
    }

    if (localStorage.getItem('is_logged_in') === 'true') {
        try { const u = await api.get('/api/user'); currentUser.value = u.data; } catch (e) {}
    }

    setupObserver();

  } catch (error) {
    console.error(error);
  } finally {
    isLoading.value = false;
  }
});

onUnmounted(() => {
  if (observer) observer.disconnect();
});
</script>

<template>
  <div class="bg-stone-50 font-sans text-stone-800 selection:bg-amber-200">
    
    <nav class="fixed w-full z-50 top-0 left-0 px-6 py-6 transition-all duration-300 mix-blend-difference text-white">
      <div class="max-w-[1920px] mx-auto flex justify-between items-center">
        <div class="text-2xl font-serif font-bold tracking-tighter cursor-pointer hover:opacity-80 transition" @click="router.push('/')">
          VDP<span class="text-amber-500">.</span>
        </div>
        
        <div class="hidden md:flex gap-8 items-center text-xs font-bold uppercase tracking-widest">
           <RouterLink to="/portfolio" class="hover:text-amber-400 transition">Obras</RouterLink>
           <RouterLink to="/about" class="hover:text-amber-400 transition">Sobre</RouterLink>
           
           <div v-if="currentUser" class="flex items-center gap-4">
             <RouterLink v-if="currentUser.is_admin" to="/admin" class="hover:text-amber-400">Admin</RouterLink>
             <button @click="handleLogout" class="hover:text-red-400">Sair</button>
           </div>
           <RouterLink v-else to="/login" class="px-5 py-2 border border-white/30 rounded-full hover:bg-white hover:text-black transition">Área do Cliente</RouterLink>
        </div>
      </div>
    </nav>

    <div v-if="isLoading" class="h-screen w-full flex items-center justify-center bg-stone-50 fixed z-[60]">
        <div class="flex flex-col items-center animate-pulse">
            <span class="text-3xl font-serif font-bold mb-2">VDP.</span>
            <div class="h-px w-12 bg-stone-900"></div>
        </div>
    </div>

    <div v-else>
        <header class="h-screen relative flex items-center justify-center overflow-hidden">
            <div class="absolute inset-0 z-0">
               <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?q=80&w=1920&auto=format&fit=crop" class="w-full h-full object-cover brightness-[0.7]" />
            </div>
            
            <div class="relative z-10 text-center text-white px-4 animate-fade-in-up">
                <p class="text-amber-400 text-xs font-bold uppercase tracking-[0.4em] mb-4">Desde 2010</p>
                <h1 class="text-6xl md:text-8xl font-serif leading-tight mb-8">
                    Viva o <br/><span class="italic font-light opacity-80">extraordinário.</span>
                </h1>
                <p class="max-w-md mx-auto text-sm font-light tracking-wide opacity-90 leading-relaxed border-l border-amber-500 pl-4 text-left">
                    Design autoral e engenharia de precisão para criar marcos urbanos que atravessam gerações.
                </p>
            </div>

            <div class="absolute bottom-10 left-1/2 -translate-x-1/2 text-white animate-bounce">
                <svg class="w-6 h-6 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
            </div>
        </header>

        <section class="relative bg-stone-50">
            
            <div class="hidden lg:block fixed top-0 right-0 w-1/2 h-screen z-0">
                <div class="absolute inset-0 bg-stone-900/10 z-10"></div>
                <transition name="fade" mode="in-out">
                    <img 
                        :key="activeProjectImage" 
                        :src="activeProjectImage" 
                        class="absolute inset-0 w-full h-full object-cover"
                        alt="Project Cover"
                    />
                </transition>
            </div>

            <div class="relative z-10 w-full lg:w-1/2 bg-stone-50 lg:bg-transparent">
                
                <div class="pt-32 pb-20 px-8 md:px-20">
                    <h2 class="text-4xl font-serif text-stone-900 mb-2">Coleção Exclusiva</h2>
                    <div class="w-16 h-1 bg-amber-600"></div>
                </div>

                <div class="flex flex-col pb-32">
                    <div 
                        v-for="project in projects" 
                        :key="project.id"
                        :data-id="project.id"
                        class="project-scroll-item min-h-[90vh] flex flex-col justify-center px-8 md:px-20 border-l border-stone-200 lg:border-none transition-opacity duration-700"
                        :class="activeProjectId === project.id ? 'opacity-100' : 'opacity-40 lg:blur-[1px]'"
                    >
                        <div class="lg:hidden w-full h-[400px] mb-8 overflow-hidden rounded-sm shadow-md">
                            <img :src="project.image_url" class="w-full h-full object-cover" />
                        </div>

                        <div class="max-w-md">
                            <span 
                                class="inline-block px-3 py-1 mb-6 text-[10px] font-bold uppercase tracking-widest border rounded-full"
                                :class="statusConfig[project.status]?.class"
                            >
                                {{ statusConfig[project.status]?.label }}
                            </span>

                            <h3 
                                class="text-5xl md:text-7xl font-serif text-stone-900 mb-6 cursor-pointer hover:text-amber-800 transition leading-[0.9]"
                                @click="goToProject(project.id)"
                            >
                                {{ project.title }}
                            </h3>

                            <p class="text-stone-400 text-sm uppercase tracking-widest mb-8 flex items-center gap-2">
                                <span class="w-4 h-px bg-stone-400"></span> {{ project.location }}
                            </p>

                            <p class="text-stone-600 text-lg font-light leading-relaxed mb-10">
                                {{ project.description || "Um empreendimento desenhado para redefinir o skyline da região, unindo conforto absoluto e design atemporal." }}
                            </p>

                            <button 
                                @click="goToProject(project.id)"
                                class="group flex items-center gap-3 text-xs font-bold uppercase tracking-[0.2em] text-stone-900 hover:text-amber-700 transition"
                            >
                                <span class="w-8 h-px bg-stone-900 group-hover:w-16 group-hover:bg-amber-700 transition-all duration-300"></span>
                                Ver Detalhes
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <footer class="relative z-20 bg-stone-900 text-stone-500 py-24 px-8">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-8">
                <div class="text-center md:text-left">
                    <div class="text-3xl font-serif font-bold text-white tracking-tighter mb-2">
                        VDP<span class="text-amber-600">.</span>
                    </div>
                    <p class="text-xs uppercase tracking-widest opacity-50">Construindo Legados.</p>
                </div>
                
                <div class="flex gap-8 text-sm font-medium">
                    <a href="#" class="hover:text-white transition">Instagram</a>
                    <a href="#" class="hover:text-white transition">LinkedIn</a>
                    <a href="#" class="hover:text-white transition">Contato</a>
                </div>

                <p class="text-xs uppercase tracking-widest opacity-30">&copy; 2026 VDP Construct.</p>
            </div>
        </footer>
    </div>
  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 1s ease-in-out, transform 1.2s ease-out;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: scale(1.05); 
}

.fade-leave-active {
  position: absolute;
}
</style>