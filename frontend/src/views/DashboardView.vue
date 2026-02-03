<script setup lang="ts">
import { ref, onMounted } from 'vue';
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

const statusConfig: Record<string, { label: string, class: string }> = {
  planning: { label: 'Planejamento', class: 'bg-blue-100 text-blue-800 border-blue-200' },
  in_progress: { label: 'Em Andamento', class: 'bg-amber-100 text-amber-800 border-amber-200' },
  finished: { label: 'Entregue', class: 'bg-emerald-100 text-emerald-800 border-emerald-200' },
};

onMounted(async () => {
  try {
    const response = await api.get('/api/projects'); 
    
    projects.value = response.data;
  } catch (error) {
    console.error('Erro ao buscar obras:', error);
  } finally {
    isLoading.value = false;
  }
});
</script>

<template>
  <div class="min-h-screen bg-stone-50 font-sans text-stone-800">
    
    <nav class="fixed w-full z-50 bg-white/90 backdrop-blur-md border-b border-stone-200">
      <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <div class="text-2xl font-serif font-bold text-stone-900 tracking-tighter">
          VDP<span class="text-amber-700">.</span>Construct
        </div>
        
        <div class="hidden md:flex gap-8 items-center text-sm font-medium text-stone-600">
          <a href="#" class="hover:text-amber-700 transition">Projetos</a>
          <a href="#" class="hover:text-amber-700 transition">Sustentabilidade</a>
          
          <RouterLink 
            to="/login" 
            class="px-5 py-2 bg-stone-900 text-white rounded-full hover:bg-amber-700 transition cursor-pointer flex items-center gap-2"
          >
            <span>Área do Cliente</span>
            <span class="text-amber-500">&rarr;</span>
          </RouterLink>
        </div>
      </div>
    </nav>

    <header class="relative h-[80vh] flex items-center justify-center overflow-hidden">
      <div class="absolute inset-0 z-0">
        <img 
          src="https://images.unsplash.com/photo-1631679706909-1844bbd07221?q=80&w=1920&auto=format&fit=crop" 
          class="w-full h-full object-cover brightness-50"
          alt="Luxury Building"
        />
      </div>

      <div class="relative z-10 text-center px-6 max-w-4xl mt-16 animate-fade-in-up">
        <span class="text-amber-400 tracking-[0.2em] text-sm uppercase font-semibold">Excelência em Construção</span>
        <h1 class="text-5xl md:text-7xl font-serif text-white mt-4 mb-6 leading-tight">
          O futuro da engenharia <br/><span class="italic text-stone-300">começa aqui.</span>
        </h1>
        <p class="text-stone-300 text-lg md:text-xl max-w-2xl mx-auto font-light">
          Gerenciamento inteligente de obras, cronogramas precisos e design de alto padrão.
        </p>
      </div>
    </header>

    <section class="py-20 px-6 max-w-7xl mx-auto">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center md:text-left">
        <div class="p-8 bg-white border border-stone-100 shadow-sm rounded-2xl hover:shadow-lg transition duration-500">
          <p class="text-5xl font-light text-amber-700 mb-2">{{ projects.length }}</p>
          <p class="text-stone-500 uppercase tracking-wide text-xs font-bold">Obras no Portfólio</p>
        </div>
        <div class="p-8 bg-white border border-stone-100 shadow-sm rounded-2xl hover:shadow-lg transition duration-500">
          <p class="text-5xl font-light text-stone-800 mb-2">100%</p>
          <p class="text-stone-500 uppercase tracking-wide text-xs font-bold">Compromisso com Prazo</p>
        </div>
        <div class="p-8 bg-stone-900 shadow-sm rounded-2xl text-white hover:bg-stone-800 transition duration-500 cursor-pointer group">
          <p class="text-3xl font-serif mb-2 group-hover:text-amber-400 transition">Fale Conosco +</p>
          <p class="text-stone-400 text-sm">Inicie seu projeto hoje</p>
        </div>
      </div>
    </section>

    <section class="py-10 px-6 max-w-7xl mx-auto pb-32">
      <div class="flex justify-between items-end mb-12">
        <div>
          <h2 class="text-3xl font-serif text-stone-900">Nossas Obras</h2>
          <div class="h-1 w-20 bg-amber-700 mt-4"></div>
        </div>
      </div>

      <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 gap-10">
        <div v-for="i in 4" :key="i" class="animate-pulse">
           <div class="bg-gray-200 h-[400px] rounded-xl mb-4"></div>
           <div class="h-6 bg-gray-200 w-2/3 rounded mb-2"></div>
           <div class="h-4 bg-gray-200 w-1/3 rounded"></div>
        </div>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-10">
        <div 
          v-for="project in projects" 
          :key="project.id" 
          class="group cursor-pointer"
        >
          <div class="relative overflow-hidden rounded-xl h-[400px] mb-6 shadow-md">
            <img 
              :src="project.image_url" 
              class="w-full h-full object-cover transform group-hover:scale-105 transition duration-700 ease-in-out grayscale-[10%] group-hover:grayscale-0"
            />
            
            <div class="absolute top-4 right-4">
              <span 
                class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border shadow-sm backdrop-blur-sm"
                :class="statusConfig[project.status]?.class"
              >
                {{ statusConfig[project.status]?.label }}
              </span>
            </div>
          </div>
          
          <h3 class="text-2xl font-serif text-stone-800 group-hover:text-amber-700 transition">
            {{ project.title }}
          </h3>
          <p class="text-stone-500 mt-1 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            {{ project.location }}
          </p>
        </div>
      </div>
    </section>

    <footer class="bg-white border-t border-stone-200 py-12 text-center text-stone-400 text-sm">
      <p>&copy; 2026 VDP Construct. Construindo sonhos com solidez.</p>
    </footer>

  </div>
</template>