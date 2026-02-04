<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import api from '@/lib/axios';

interface Project {
  id: number;
  title: string;
  location: string;
  status: 'planning' | 'in_progress' | 'finished';
}

const projects = ref<Project[]>([]);
const searchQuery = ref('');
const isLoading = ref(true);

// Status com cores visuais
const statusConfig: Record<string, { label: string, class: string, dot: string }> = {
  planning: { label: 'Planejamento', class: 'bg-blue-50 text-blue-700 border-blue-200', dot: 'bg-blue-500' },
  in_progress: { label: 'Em Andamento', class: 'bg-amber-50 text-amber-700 border-amber-200', dot: 'bg-amber-500' },
  finished: { label: 'Entregue', class: 'bg-emerald-50 text-emerald-700 border-emerald-200', dot: 'bg-emerald-500' },
};

onMounted(async () => {
  try {
    const response = await api.get('/api/projects');
    projects.value = response.data;
  } catch (error) {
    alert("Erro ao carregar dados.");
  } finally {
    isLoading.value = false;
  }
});

// Filtro de Busca (Search)
const filteredProjects = computed(() => {
  if (!searchQuery.value) return projects.value;
  return projects.value.filter(p => 
    p.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    p.location.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

const handleDelete = async (id: number) => {
  if(!confirm("⚠️ Tem certeza? Essa ação não pode ser desfeita.")) return;
  try {
    await api.delete(`/api/projects/${id}`);
    projects.value = projects.value.filter(p => p.id !== id);
  } catch (error) {
    alert("Erro ao excluir.");
  }
};
</script>

<template>
  <div class="min-h-screen bg-stone-50 text-stone-800 font-sans pb-20">
    
    <header class="bg-white border-b border-stone-200 px-8 py-4 flex justify-between items-center sticky top-0 z-20">
      <div class="flex items-center gap-3">
        <RouterLink to="/" class="text-stone-400 hover:text-stone-800 transition">
          &larr; Voltar ao Site
        </RouterLink>
        <div class="h-6 w-px bg-stone-200"></div>
        <h1 class="font-bold text-xl tracking-tight">VDP<span class="text-amber-700">.</span>Admin</h1>
      </div>
      <div class="flex items-center gap-4">
        <div class="text-right hidden md:block">
          <p class="text-xs text-stone-400 uppercase tracking-wider">Logado como</p>
          <p class="font-bold text-sm">Administrador</p>
        </div>
        <div class="h-10 w-10 rounded-full bg-stone-900 text-amber-500 flex items-center justify-center font-serif font-bold">
          A
        </div>
      </div>
    </header>

    <main class="max-w-6xl mx-auto px-6 py-10">
      
      <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
        <div>
          <h2 class="text-3xl font-serif font-bold text-stone-900">Gestão de Obras</h2>
          <p class="text-stone-500 mt-1">Gerencie o portfólio, atualize status e adicione novos empreendimentos.</p>
        </div>
        <button class="bg-stone-900 hover:bg-amber-700 text-white px-6 py-3 rounded-lg shadow-lg shadow-stone-900/20 transition flex items-center gap-2 font-medium">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
          Nova Obra
        </button>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
        
        <div class="p-6 border-b border-stone-100 flex flex-col md:flex-row gap-4 justify-between items-center bg-stone-50/50">
          <div class="relative w-full md:w-96">
            <span class="absolute left-3 top-3 text-stone-400">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </span>
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Buscar por nome ou local..." 
              class="w-full pl-10 pr-4 py-2 bg-white border border-stone-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition"
            />
          </div>
          <div class="text-sm text-stone-500">
            Total de <strong>{{ filteredProjects.length }}</strong> obras encontradas
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-stone-50 text-stone-500 text-xs uppercase tracking-wider font-semibold border-b border-stone-200">
                <th class="p-6 w-20">ID</th>
                <th class="p-6">Empreendimento</th>
                <th class="p-6">Status</th>
                <th class="p-6 text-right">Ações</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
              <tr 
                v-for="project in filteredProjects" 
                :key="project.id" 
                class="hover:bg-stone-50/80 transition group"
              >
                <td class="p-6 font-mono text-stone-400 text-sm">#{{ project.id }}</td>
                
                <td class="p-6">
                  <div class="font-bold text-stone-800 text-lg">{{ project.title }}</div>
                  <div class="text-sm text-stone-500 flex items-center gap-1 mt-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    {{ project.location }}
                  </div>
                </td>

                <td class="p-6">
                  <span 
                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold uppercase border"
                    :class="statusConfig[project.status]?.class"
                  >
                    <span class="w-2 h-2 rounded-full" :class="statusConfig[project.status]?.dot"></span>
                    {{ statusConfig[project.status]?.label }}
                  </span>
                </td>

                <td class="p-6 text-right">
                  <div class="flex items-center justify-end gap-3 opacity-60 group-hover:opacity-100 transition">
                    <button class="p-2 hover:bg-blue-50 text-stone-400 hover:text-blue-600 rounded-lg transition" title="Editar">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </button>
                    <button 
                      @click="handleDelete(project.id)"
                      class="p-2 hover:bg-red-50 text-stone-400 hover:text-red-600 rounded-lg transition" 
                      title="Excluir"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          
          <div v-if="filteredProjects.length === 0 && !isLoading" class="p-12 text-center text-stone-400">
            <p>Nenhuma obra encontrada para esta busca.</p>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>