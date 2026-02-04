<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/lib/axios';

const route = useRoute();
const router = useRouter();

const project = ref<any>(null);
const isLoading = ref(true);

// Mapeamento de Status (Mesmo da Dashboard)
const statusConfig: Record<string, { label: string, class: string, textClass: string }> = {
  planning: { label: 'Planejamento', class: 'bg-blue-50 border-blue-200', textClass: 'text-blue-700' },
  in_progress: { label: 'Em Andamento', class: 'bg-amber-50 border-amber-200', textClass: 'text-amber-700' },
  finished: { label: 'Entregue', class: 'bg-emerald-50 border-emerald-200', textClass: 'text-emerald-700' },
};

// Formatar data para PT-BR
const formatDate = (dateString: string) => {
  if (!dateString) return '-';
  return new Date(dateString).toLocaleDateString('pt-BR');
};

onMounted(async () => {
  try {
    // Pega o ID da URL (ex: 1) e chama o Laravel
    const id = route.params.id;
    const response = await api.get(`/api/projects/${id}`);
    project.value = response.data;
  } catch (error) {
    console.error("Obra não encontrada", error);
    alert("Obra não encontrada!");
    router.push('/'); // Volta pra home se der erro
  } finally {
    isLoading.value = false;
  }
});
</script>

<template>
  <div class="min-h-screen bg-stone-50 font-sans text-stone-800 pb-20">
    
    <div v-if="isLoading" class="flex h-screen items-center justify-center">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-amber-700"></div>
    </div>

    <div v-else-if="project">
      
      <header class="relative h-[60vh] w-full">
        <img 
          :src="project.image_url" 
          class="w-full h-full object-cover brightness-[0.6]"
          alt="Capa do Projeto"
        />
        
        <RouterLink 
          to="/" 
          class="absolute top-8 left-8 bg-white/20 backdrop-blur-md text-white px-4 py-2 rounded-full hover:bg-white/40 transition flex items-center gap-2 border border-white/30"
        >
          &larr; Voltar
        </RouterLink>

        <div class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-stone-900/90 to-transparent p-8 md:p-16">
          <div class="max-w-7xl mx-auto">
            <span 
              class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border mb-4 inline-block bg-white/10 text-white border-white/20"
            >
              {{ statusConfig[project.status]?.label }}
            </span>
            <h1 class="text-4xl md:text-6xl font-serif text-white font-bold">{{ project.title }}</h1>
            <p class="text-stone-300 text-lg mt-2 flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
              {{ project.location }}
            </p>
          </div>
        </div>
      </header>

      <main class="max-w-7xl mx-auto px-6 -mt-10 relative z-10">
        <div class="bg-white rounded-xl shadow-xl border border-stone-100 p-8 grid grid-cols-1 md:grid-cols-3 gap-8">
          
          <div class="space-y-4 border-b md:border-b-0 md:border-r border-stone-100 pb-6 md:pb-0">
            <h3 class="text-sm font-bold text-stone-400 uppercase tracking-widest">Cronograma</h3>
            <div>
              <p class="text-xs text-stone-500">Data de Início</p>
              <p class="text-xl font-serif text-stone-800">{{ formatDate(project.start_date) }}</p>
            </div>
            <div>
              <p class="text-xs text-stone-500">Previsão de Entrega</p>
              <p class="text-xl font-serif text-stone-800">{{ formatDate(project.end_date) }}</p>
            </div>
          </div>

          <div class="space-y-4 border-b md:border-b-0 md:border-r border-stone-100 pb-6 md:pb-0">
             <h3 class="text-sm font-bold text-stone-400 uppercase tracking-widest">Situação Atual</h3>
             <div class="flex items-center gap-3">
               <div :class="`p-4 rounded-full border ${statusConfig[project.status]?.class}`">
                 <svg class="w-6 h-6" :class="statusConfig[project.status]?.textClass" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
               </div>
               <div>
                 <p class="font-bold text-lg">{{ statusConfig[project.status]?.label }}</p>
                 <p class="text-sm text-stone-500">Atualizado recentemente</p>
               </div>
             </div>
          </div>

          <div class="flex flex-col justify-center space-y-3">
            <button class="w-full py-3 bg-stone-900 text-white rounded-lg hover:bg-amber-700 transition font-medium shadow-lg shadow-stone-900/10 cursor-pointer">
              Baixar Documentação
            </button>
            <button class="w-full py-3 border border-stone-300 text-stone-600 rounded-lg hover:bg-stone-50 transition font-medium cursor-pointer">
              Contatar Engenheiro
            </button>
          </div>

        </div>

        <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-12">
          <div class="md:col-span-2">
            <h2 class="text-2xl font-serif font-bold mb-4">Sobre o Empreendimento</h2>
            <p class="text-stone-600 leading-relaxed mb-4">
              Este projeto representa o auge da engenharia moderna na região de {{ project.location }}. 
              Com acabamentos de alto padrão e sustentabilidade certificada, o {{ project.title }} foi projetado para oferecer conforto térmico e acústico superior.
            </p>
            <p class="text-stone-600 leading-relaxed">
              A estrutura utiliza concreto protendido, permitindo grandes vãos livres e flexibilidade no layout das unidades.
            </p>
          </div>
          <div>
            <h2 class="text-xl font-serif font-bold mb-4">Ficha Técnica</h2>
            <ul class="space-y-2 text-stone-600 text-sm border-t border-stone-200 pt-4">
              <li class="flex justify-between"><span>Área Total:</span> <span class="font-bold">12.500 m²</span></li>
              <li class="flex justify-between"><span>Torres:</span> <span class="font-bold">2</span></li>
              <li class="flex justify-between"><span>Unidades:</span> <span class="font-bold">84</span></li>
              <li class="flex justify-between"><span>Engenheiro:</span> <span class="font-bold">Dr. VDP Construct</span></li>
            </ul>
          </div>
        </div>
      </main>

    </div>
  </div>
</template>