<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue';
import api from '@/lib/axios';

// --- Interfaces e Estado ---
interface Project {
  id: number;
  title: string;
  location: string;
  status: 'planning' | 'in_progress' | 'finished';
  start_date: string | null; // <--- Mudança aqui (aceita null)
  end_date: string | null;   // <--- Mudança aqui (aceita null)
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

const statusConfig: Record<string, { label: string, class: string, dot: string }> = {
  planning: { label: 'Planejamento', class: 'bg-blue-50 text-blue-700 border-blue-200', dot: 'bg-blue-500' },
  in_progress: { label: 'Em Andamento', class: 'bg-amber-50 text-amber-700 border-amber-200', dot: 'bg-amber-500' },
  finished: { label: 'Entregue', class: 'bg-emerald-50 text-emerald-700 border-emerald-200', dot: 'bg-emerald-500' },
};

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
  form.title = '';
  form.location = '';
  form.status = 'planning';
  form.start_date = ''; 
  form.end_date = '';   
  form.imageFile = null;
  const fileInput = document.getElementById('fileInput') as HTMLInputElement;
  if (fileInput) fileInput.value = '';
};

const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    form.imageFile = target.files[0];
  }
};

const handleSave = async () => {
  isSaving.value = true;
  
  const formData = new FormData();
  formData.append('title', form.title);
  formData.append('location', form.location);
  formData.append('status', form.status);
  formData.append('start_date', form.start_date); 
  if(form.end_date) formData.append('end_date', form.end_date); 
  
  if (form.imageFile) {
    formData.append('image', form.imageFile);
  }

  try {
    if (editingId.value) {
      formData.append('_method', 'PUT'); 
      await api.post(`/api/projects/${editingId.value}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      alert("Obra atualizada com sucesso!");
    } else {
      await api.post('/api/projects', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      alert("Obra criada com sucesso!");
    }
    showModal.value = false;
    loadProjects();
  } catch (error: any) {
    if (error.response && error.response.status === 422) {
       alert("Erro de validação! Verifique todos os campos.");
       console.error(error.response.data.errors);
    } else {
       alert("Ocorreu um erro ao salvar (Veja o console).");
       console.error(error);
    }
  } finally {
    isSaving.value = false;
  }
};

const handleDelete = async (id: number) => {
  if(!confirm("⚠️ Tem certeza? Essa ação não pode ser desfeita.")) return;
  try {
    await api.delete(`/api/projects/${id}`);
    projects.value = projects.value.filter(p => p.id !== id); 
    alert("Obra apagada com sucesso!");
  } catch (error) {
    alert("Erro ao excluir.");
  }
};

</script>

<template>
  <div class="min-h-screen bg-stone-50 text-stone-800 font-sans pb-20 relative">
    
    <header class="bg-white border-b border-stone-200 px-8 py-4 flex justify-between items-center sticky top-0 z-20">
      <div class="flex items-center gap-3">
        <RouterLink to="/" class="text-stone-400 hover:text-stone-800 transition">&larr; Voltar ao Site</RouterLink>
        <div class="h-6 w-px bg-stone-200"></div>
        <h1 class="font-bold text-xl tracking-tight">VDP<span class="text-amber-700">.</span>Admin</h1>
      </div>
      <div class="flex items-center gap-4">
        <div class="text-right hidden md:block">
          <p class="text-xs text-stone-400 uppercase tracking-wider">Logado como</p>
          <p class="font-bold text-sm">Administrador</p>
        </div>
        <div class="h-10 w-10 rounded-full bg-stone-900 text-amber-500 flex items-center justify-center font-serif font-bold">A</div>
      </div>
    </header>

    <main class="max-w-6xl mx-auto px-6 py-10 z-10 relative">
      <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
        <div>
          <h2 class="text-3xl font-serif font-bold text-stone-900">Gestão de Obras</h2>
          <p class="text-stone-500 mt-1">Gerencie o portfólio, atualize status e adicione novos empreendimentos.</p>
        </div>
        <button @click="openCreateModal" class="bg-stone-900 hover:bg-amber-700 text-white px-6 py-3 rounded-lg shadow-lg transition flex items-center gap-2 font-medium">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
          Nova Obra
        </button>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
        <div class="p-6 border-b border-stone-100 flex flex-col md:flex-row gap-4 justify-between items-center bg-stone-50/50">
          <div class="relative w-full md:w-96">
            <span class="absolute left-3 top-3 text-stone-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg></span>
            <input v-model="searchQuery" type="text" placeholder="Buscar por nome ou local..." class="w-full pl-10 pr-4 py-2 bg-white border border-stone-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition" />
          </div>
          <div class="text-sm text-stone-500">Total de <strong>{{ filteredProjects.length }}</strong> obras</div>
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
              <tr v-for="project in filteredProjects" :key="project.id" class="hover:bg-stone-50/80 transition group">
                <td class="p-6 font-mono text-stone-400 text-sm">#{{ project.id }}</td>
                <td class="p-6">
                  <div class="flex items-center gap-4">
                    <img :src="project.image_url" class="w-12 h-12 rounded-md object-cover shadow-sm border border-stone-100" alt="">
                    <div>
                        <div class="font-bold text-stone-800 text-lg">{{ project.title }}</div>
                        <div class="text-sm text-stone-500">{{ project.location }}</div>
                    </div>
                  </div>
                </td>
                <td class="p-6">
                  <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold uppercase border" :class="statusConfig[project.status]?.class">
                    <span class="w-2 h-2 rounded-full" :class="statusConfig[project.status]?.dot"></span>
                    {{ statusConfig[project.status]?.label }}
                  </span>
                </td>
                <td class="p-6 text-right">
                  <div class="flex items-center justify-end gap-3 opacity-60 group-hover:opacity-100 transition">
                    <button @click="openEditModal(project)" class="p-2 hover:bg-blue-50 text-stone-400 hover:text-blue-600 rounded-lg transition" title="Editar"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></button>
                    <button @click="handleDelete(project.id)" class="p-2 hover:bg-red-50 text-stone-400 hover:text-red-600 rounded-lg transition" title="Excluir"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>

    <div v-if="showModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 transition-opacity" @click="showModal = false"></div>
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 pointer-events-none">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-auto pointer-events-auto transform transition-all animate-fade-in-up max-h-[90vh] overflow-y-auto">
        
        <div class="px-6 py-4 border-b border-stone-100 flex justify-between items-center sticky top-0 bg-white z-10">
          <h3 class="text-xl font-serif font-bold text-stone-900">{{ editingId ? 'Editar Obra' : 'Nova Obra' }}</h3>
          <button @click="showModal = false" class="text-stone-400 hover:text-stone-800 transition">✕</button>
        </div>

        <form @submit.prevent="handleSave" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Nome do Empreendimento</label>
                <input v-model="form.title" type="text" required class="w-full p-3 border border-stone-200 rounded-lg focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition" />
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Localização</label>
                <input v-model="form.location" type="text" required class="w-full p-3 border border-stone-200 rounded-lg focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition" />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                  <label class="block text-sm font-medium text-stone-700 mb-1">Início da Obra</label>
                  <input v-model="form.start_date" type="date" required class="w-full p-3 border border-stone-200 rounded-lg focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition text-sm" />
              </div>
              <div>
                  <label class="block text-sm font-medium text-stone-700 mb-1">Entrega Prevista</label>
                  <input v-model="form.end_date" type="date" class="w-full p-3 border border-stone-200 rounded-lg focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition text-sm" />
              </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Status da Obra</label>
                <select v-model="form.status" class="w-full p-3 border border-stone-200 rounded-lg focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition bg-white">
                    <option value="planning">Planejamento</option>
                    <option value="in_progress">Em Andamento</option>
                    <option value="finished">Entregue</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">
                    Imagem da Capa 
                    <span v-if="!editingId" class="text-red-500">*</span>
                </label>
                <input type="file" id="fileInput" @change="handleFileUpload" accept="image/*" class="w-full p-2 border border-stone-200 rounded-lg text-sm" :required="!editingId" />
                 <p class="text-xs text-stone-500 mt-1">JPG ou PNG. Máximo 2MB.</p>
            </div>
            
            <div class="pt-4">
                <button type="submit" :disabled="isSaving" class="w-full bg-stone-900 hover:bg-amber-700 text-white font-medium py-3 rounded-lg transition flex justify-center items-center gap-2">
                  <span v-if="isSaving">Salvando...</span>
                  <span v-else>{{ editingId ? 'Atualizar Obra' : 'Criar Obra' }}</span>
                </button>
            </div>
        </form>
      </div>
    </div>
  </div>
</template>