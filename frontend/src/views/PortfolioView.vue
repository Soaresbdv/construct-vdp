<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/lib/axios';

const router = useRouter();
const projects = ref<any[]>([]);
const isLoading = ref(true);

const statusConfig: Record<string, { label: string, class: string }> = {
  planning: { label: 'Planejamento', class: 'bg-white/90 text-stone-600' },
  in_progress: { label: 'Em Obras', class: 'bg-white/90 text-amber-700' },
  finished: { label: 'Entregue', class: 'bg-stone-900/90 text-white' },
};

onMounted(async () => {
  try {
    const response = await api.get('/api/projects');
    projects.value = response.data;
  } catch (error) {
    console.error(error);
  } finally {
    isLoading.value = false;
  }
});
</script>

<template>
  <div class="min-h-screen bg-white font-sans text-stone-800">
    
    <nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-stone-100">
      <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <div class="text-xl font-serif font-bold cursor-pointer" @click="router.push('/')">
          VDP<span class="text-amber-700">.</span>Construct
        </div>
        <RouterLink to="/" class="text-xs font-bold uppercase tracking-wider text-stone-400 hover:text-stone-900 transition">
          Voltar para Home
        </RouterLink>
      </div>
    </nav>

    <header class="py-24 px-6 max-w-7xl mx-auto text-center">
      <p class="text-amber-600 text-xs font-bold uppercase tracking-widest mb-4">Nossa Coleção</p>
      <h1 class="text-5xl md:text-6xl font-serif text-stone-900 mb-6">Todos os Projetos</h1>
      <p class="text-stone-400 max-w-2xl mx-auto font-light leading-relaxed">
        Cada empreendimento carrega nossa assinatura de excelência. Explore abaixo nossa trajetória completa de construções e lançamentos.
      </p>
    </header>

    <main class="px-6 max-w-7xl mx-auto pb-32">
        <div v-if="isLoading" class="text-center py-20">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-stone-900 mx-auto"></div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-16">
            <div 
            v-for="project in projects" 
            :key="project.id" 
            class="group cursor-pointer"
            @click="router.push(`/projects/${project.id}`)"
            >
            <div class="relative h-[350px] overflow-hidden rounded-sm mb-6 bg-stone-100">
                <img 
                :src="project.image_url" 
                class="w-full h-full object-cover transition duration-700 group-hover:scale-105 filter grayscale-[10%] group-hover:grayscale-0" 
                />
                
                <div class="absolute top-4 left-4">
                    <span class="px-2 py-1 text-[10px] font-bold uppercase tracking-widest bg-white/90 backdrop-blur-sm shadow-sm" :class="statusConfig[project.status]?.class">
                        {{ statusConfig[project.status]?.label }}
                    </span>
                </div>

                <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition duration-500"></div>
            </div>
            
            <div class="text-center group-hover:-translate-y-1 transition duration-300">
                <h3 class="text-2xl font-serif text-stone-900">{{ project.title }}</h3>
                <p class="text-stone-400 text-xs mt-2 uppercase tracking-widest">{{ project.location }}</p>
            </div>
            </div>
        </div>
    </main>

  </div>
</template>