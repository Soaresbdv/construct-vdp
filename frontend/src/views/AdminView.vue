<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue';
import api from '@/lib/axios';

interface Project {
  id: number;
  title: string;
  location: string;
  status: 'planning' | 'in_progress' | 'finished';
  start_date: string | null;
  end_date: string | null;
  image_url: string;
}

const projects = ref<Project[]>([]);
const searchQuery = ref('');
const isLoading = ref(true);
const isSaving = ref(false);

const showModal = ref(false);
const editingId = ref<number | null>(null);

const form = reactive({
  title: '',
  location: '',
  status: 'planning',
  start_date: '',
  end_date: '',
  imageFile: null as File | null,
});

const statusConfig: Record<string, { label: string, class: string, icon: string }> = {
  planning: { label: 'Planejamento', class: 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
  in_progress: { label: 'Em Obra', class: 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' },
  finished: { label: 'Entregue', class: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
};

const stats = computed(() => {
    const total = projects.value.length;
    const active = projects.value.filter(p => p.status === 'in_progress').length;
    const planning = projects.value.filter(p => p.status === 'planning').length;
    return { total, active, planning };
});

const loadProjects = async () => {
  isLoading.value = true;
  try {
    const response = await api.get('/api/projects');
    projects.value = response.data;
  } catch (error) {
    console.error(error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(loadProjects);

const filteredProjects = computed(() => {
  if (!searchQuery.value) return projects.value;
  return projects.value.filter(p => 
    p.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    p.location.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

const formatDate = (dateStr: string | null) => {
    if(!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('pt-BR', { month: 'short', year: 'numeric' });
}

const openCreateModal = () => {
  editingId.value = null;
  resetForm();
  showModal.value = true;
};

const openEditModal = (project: Project) => {
  editingId.value = project.id;
  form.title = project.title;
  form.location = project.location;
  form.status = project.status;
  form.start_date = project.start_date ? (project.start_date.split('T')[0] ?? '') : '';
  form.end_date = project.end_date ? (project.end_date.split('T')[0] ?? '') : '';
  form.imageFile = null;
  showModal.value = true;
};

const resetForm = () => {
  Object.assign(form, { title: '', location: '', status: 'planning', start_date: '', end_date: '', imageFile: null });
  const fileInput = document.getElementById('fileInput') as HTMLInputElement;
  if (fileInput) fileInput.value = '';
};

const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) form.imageFile = target.files[0];
};

const handleSave = async () => {
  isSaving.value = true;
  const formData = new FormData();
  formData.append('title', form.title);
  formData.append('location', form.location);
  formData.append('status', form.status);
  formData.append('start_date', form.start_date); 
  if(form.end_date) formData.append('end_date', form.end_date); 
  if (form.imageFile) formData.append('image', form.imageFile);

  try {
    const url = editingId.value ? `/api/projects/${editingId.value}` : '/api/projects';
    if(editingId.value) formData.append('_method', 'PUT');
    
    await api.post(url, formData, { headers: { 'Content-Type': 'multipart/form-data' } });
    
    alert(editingId.value ? "Projeto atualizado!" : "Projeto criado com sucesso!");
    showModal.value = false;
    loadProjects();
  } catch (error) {
    alert("Erro ao salvar. Verifique os dados.");
  } finally {
    isSaving.value = false;
  }
};

const handleDelete = async (id: number) => {
  if(!confirm("⚠️ Tem certeza que deseja remover este projeto?")) return;
  try {
    await api.delete(`/api/projects/${id}`);
    projects.value = projects.value.filter(p => p.id !== id); 
  } catch (error) {
    alert("Erro ao excluir.");
  }
};
</script>

<template>
  <div class="min-h-screen bg-stone-100 font-sans text-stone-800 flex">
    
    <aside class="w-20 lg:w-64 bg-stone-900 text-stone-400 flex-shrink-0 hidden md:flex flex-col">
      <div class="h-20 flex items-center justify-center lg:justify-start lg:px-8 border-b border-stone-800">
        <div class="font-serif font-bold text-white text-xl tracking-tighter">
            VDP<span class="text-amber-600">.</span>
        </div>
      </div>
      <nav class="p-4 space-y-2">
        <a href="#" class="flex items-center gap-3 px-4 py-3 bg-stone-800 text-white rounded-lg shadow-inner">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            <span class="hidden lg:block font-medium">Obras</span>
        </a>
        <a href="#" class="flex items-center gap-3 px-4 py-3 hover:bg-stone-800 hover:text-white rounded-lg transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            <span class="hidden lg:block font-medium">Leads / Clientes</span>
        </a>
        <a href="#" class="flex items-center gap-3 px-4 py-3 hover:bg-stone-800 hover:text-white rounded-lg transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            <span class="hidden lg:block font-medium">Relatórios</span>
        </a>
      </nav>
      <div class="mt-auto p-6 border-t border-stone-800">
        <RouterLink to="/" class="flex items-center gap-2 text-xs uppercase tracking-wider hover:text-white transition">
            &larr; Voltar ao Site
        </RouterLink>
      </div>
    </aside>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        
        <header class="h-20 bg-white border-b border-stone-200 flex items-center justify-between px-8 shadow-sm z-20">
            <h2 class="text-xl font-bold text-stone-800 flex items-center gap-2">
                <span class="text-stone-400 font-normal">Painel /</span> Projetos
            </h2>
            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold text-stone-900">Administrador</p>
                    <p class="text-xs text-stone-500">admin@obra.com</p>
                </div>
                <div class="h-10 w-10 bg-stone-900 rounded-full flex items-center justify-center text-amber-500 font-serif font-bold text-lg shadow-lg">
                    A
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-8 bg-stone-100">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 animate-fade-in-up">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-stone-200 flex items-center justify-between">
                    <div>
                        <p class="text-stone-500 text-xs font-bold uppercase tracking-wider mb-1">Total de Obras</p>
                        <p class="text-3xl font-bold text-stone-900">{{ stats.total }}</p>
                    </div>
                    <div class="h-12 w-12 bg-stone-100 rounded-lg flex items-center justify-center text-stone-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-stone-200 flex items-center justify-between">
                    <div>
                        <p class="text-stone-500 text-xs font-bold uppercase tracking-wider mb-1">Em Andamento</p>
                        <p class="text-3xl font-bold text-amber-600">{{ stats.active }}</p>
                    </div>
                    <div class="h-12 w-12 bg-amber-50 rounded-lg flex items-center justify-center text-amber-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-stone-200 flex items-center justify-between">
                    <div>
                        <p class="text-stone-500 text-xs font-bold uppercase tracking-wider mb-1">Em Planejamento</p>
                        <p class="text-3xl font-bold text-blue-600">{{ stats.planning }}</p>
                    </div>
                    <div class="h-12 w-12 bg-blue-50 rounded-lg flex items-center justify-center text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                </div>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <div class="relative w-full md:w-96">
                    <span class="absolute left-3 top-3 text-stone-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input v-model="searchQuery" type="text" placeholder="Buscar empreendimento..." class="w-full pl-10 pr-4 py-3 bg-white border border-stone-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-stone-900 focus:border-transparent shadow-sm transition" />
                </div>
                <button @click="openCreateModal" class="w-full md:w-auto bg-stone-900 hover:bg-amber-600 text-white px-6 py-3 rounded-xl shadow-lg shadow-stone-900/10 transition flex items-center justify-center gap-2 font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Novo Projeto
                </button>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-stone-50 border-b border-stone-200 text-stone-500 text-xs uppercase tracking-wider font-semibold">
                            <th class="p-5">Empreendimento</th>
                            <th class="p-5">Cronograma</th>
                            <th class="p-5">Status</th>
                            <th class="p-5 text-right">Gerenciar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <tr v-if="isLoading">
                            <td colspan="4" class="p-10 text-center text-stone-400">Carregando projetos...</td>
                        </tr>
                        <tr v-else-if="filteredProjects.length === 0">
                            <td colspan="4" class="p-10 text-center text-stone-400">Nenhum projeto encontrado.</td>
                        </tr>
                        <tr v-for="project in filteredProjects" :key="project.id" class="group hover:bg-stone-50 transition duration-150">
                            <td class="p-5">
                                <div class="flex items-center gap-4">
                                    <div class="h-16 w-24 rounded-lg overflow-hidden bg-stone-200 border border-stone-200 shadow-sm flex-shrink-0">
                                        <img :src="project.image_url" class="h-full w-full object-cover" alt="">
                                    </div>
                                    <div>
                                        <div class="font-bold text-stone-800 text-base">{{ project.title }}</div>
                                        <div class="text-stone-500 text-xs mt-1 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            {{ project.location }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="p-5">
                                <div class="flex flex-col gap-1 text-xs">
                                    <span class="text-stone-500">Início: <strong class="text-stone-700">{{ formatDate(project.start_date) }}</strong></span>
                                    <span class="text-stone-500">Entrega: <strong class="text-stone-700">{{ formatDate(project.end_date) }}</strong></span>
                                </div>
                            </td>

                            <td class="p-5">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide" :class="statusConfig[project.status]?.class">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" v-html="statusConfig[project.status]?.icon"></svg>
                                    {{ statusConfig[project.status]?.label }}
                                </span>
                            </td>

                            <td class="p-5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openEditModal(project)" class="p-2 text-stone-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button @click="handleDelete(project.id)" class="p-2 text-stone-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Excluir">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 text-center text-xs text-stone-400">
                &copy; 2026 VDP Construtora
            </div>
        </main>
    </div>

    <div v-if="showModal" class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 transition-opacity duration-300">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate-fade-in-up">
        
        <div class="bg-stone-50 px-8 py-5 border-b border-stone-100 flex justify-between items-center">
          <h3 class="text-lg font-bold text-stone-800">{{ editingId ? 'Editar Propriedade' : 'Cadastrar Nova Obra' }}</h3>
          <button @click="showModal = false" class="text-stone-400 hover:text-red-500 transition text-2xl leading-none">&times;</button>
        </div>

        <form @submit.prevent="handleSave" class="p-8 space-y-5">
            <div>
                <label class="block text-xs font-bold text-stone-500 uppercase tracking-wider mb-2">Nome do Empreendimento</label>
                <input v-model="form.title" type="text" required class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-stone-900 focus:border-transparent transition" placeholder="Ex: Edifício Horizon" />
            </div>
            
            <div>
                <label class="block text-xs font-bold text-stone-500 uppercase tracking-wider mb-2">Localização</label>
                <input v-model="form.location" type="text" required class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-stone-900 focus:border-transparent transition" placeholder="Cidade - UF" />
            </div>

            <div class="grid grid-cols-2 gap-5">
              <div>
                  <label class="block text-xs font-bold text-stone-500 uppercase tracking-wider mb-2">Início</label>
                  <input v-model="form.start_date" type="date" required class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-stone-900 focus:border-transparent transition text-sm" />
              </div>
              <div>
                  <label class="block text-xs font-bold text-stone-500 uppercase tracking-wider mb-2">Previsão Entrega</label>
                  <input v-model="form.end_date" type="date" class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-stone-900 focus:border-transparent transition text-sm" />
              </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-500 uppercase tracking-wider mb-2">Status Atual</label>
                <div class="relative">
                    <select v-model="form.status" class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-stone-900 focus:border-transparent transition appearance-none cursor-pointer">
                        <option value="planning">Planejamento</option>
                        <option value="in_progress">Em Construção</option>
                        <option value="finished">Obra Entregue</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-stone-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-500 uppercase tracking-wider mb-2">
                    Imagem de Capa
                    <span v-if="!editingId" class="text-red-500">*</span>
                </label>
                <div class="border-2 border-dashed border-stone-300 rounded-lg p-6 text-center hover:bg-stone-50 transition cursor-pointer relative">
                    <input type="file" id="fileInput" @change="handleFileUpload" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" :required="!editingId" />
                    <div v-if="form.imageFile" class="text-green-600 font-medium text-sm flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Arquivo selecionado: {{ form.imageFile.name }}
                    </div>
                    <div v-else class="text-stone-400 text-sm">
                        <p class="font-medium text-stone-600">Clique para upload</p>
                        <p class="text-xs mt-1">PNG, JPG até 2MB</p>
                    </div>
                </div>
            </div>
            
            <div class="pt-4 flex gap-3">
                <button type="button" @click="showModal = false" class="flex-1 py-3 border border-stone-300 text-stone-600 font-bold rounded-lg hover:bg-stone-50 transition">Cancelar</button>
                <button type="submit" :disabled="isSaving" class="flex-1 py-3 bg-stone-900 text-white font-bold rounded-lg hover:bg-amber-600 transition shadow-lg disabled:opacity-70">
                  <span v-if="isSaving">Salvando...</span>
                  <span v-else>{{ editingId ? 'Salvar Alterações' : 'Criar Projeto' }}</span>
                </button>
            </div>
        </form>
      </div>
    </div>

  </div>
</template>