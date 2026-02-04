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

interface Lead {
  id: number;
  name: string;
  phone: string;
  email: string | null;
  message: string | null;
  created_at: string;
  project: {
    title: string;
  };
}

const activeTab = ref<'projects' | 'leads'>('projects'); 
const isLoading = ref(false);
const searchQuery = ref('');

const projects = ref<Project[]>([]);
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

const leads = ref<Lead[]>([]);

const statusConfig: Record<string, { label: string, class: string, icon: string }> = {
  planning: { label: 'Planejamento', class: 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
  in_progress: { label: 'Em Obra', class: 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' },
  finished: { label: 'Entregue', class: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
};

const stats = computed(() => {
    return {
        totalProjects: projects.value.length,
        totalLeads: leads.value.length,
        activeProjects: projects.value.filter(p => p.status === 'in_progress').length
    };
});

const loadData = async () => {
  isLoading.value = true;
  try {
    const resProjects = await api.get('/api/projects');
    projects.value = resProjects.data;

    const resLeads = await api.get('/api/admin/leads'); 

  } catch (error) {
    console.error("Erro ao carregar dados:", error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(loadData);

const filteredProjects = computed(() => {
  if (!searchQuery.value) return projects.value;
  return projects.value.filter(p => p.title.toLowerCase().includes(searchQuery.value.toLowerCase()));
});

const filteredLeads = computed(() => {
  if (!searchQuery.value) return leads.value;
  return leads.value.filter(l => 
    l.name.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
    l.project?.title.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

const formatDate = (dateStr: string | null) => {
    if(!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', year: 'numeric' });
}
const formatTime = (dateStr: string) => {
    return new Date(dateStr).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
}

const openCreateModal = () => { editingId.value = null; resetForm(); showModal.value = true; };
const openEditModal = (project: Project) => {
  editingId.value = project.id;
  form.title = project.title; form.location = project.location; form.status = project.status;
  form.start_date = project.start_date ? (project.start_date.split('T')[0] ?? '') : '';
  form.end_date = project.end_date ? (project.end_date.split('T')[0] ?? '') : '';
  form.imageFile = null; showModal.value = true;
};
const resetForm = () => { Object.assign(form, { title: '', location: '', status: 'planning', start_date: '', end_date: '', imageFile: null }); };
const handleFileUpload = (event: Event) => { const target = event.target as HTMLInputElement; if (target.files && target.files[0]) form.imageFile = target.files[0]; };

const handleSave = async () => {
  isSaving.value = true;
  const formData = new FormData();
  formData.append('title', form.title); formData.append('location', form.location); formData.append('status', form.status);
  formData.append('start_date', form.start_date); if(form.end_date) formData.append('end_date', form.end_date); 
  if (form.imageFile) formData.append('image', form.imageFile);

  try {
    const url = editingId.value ? `/api/projects/${editingId.value}` : '/api/projects';
    if(editingId.value) formData.append('_method', 'PUT');
    await api.post(url, formData, { headers: { 'Content-Type': 'multipart/form-data' } });
    alert(editingId.value ? "Projeto atualizado!" : "Projeto criado!");
    showModal.value = false; loadData();
  } catch (error) { alert("Erro ao salvar."); } finally { isSaving.value = false; }
};

const handleDelete = async (id: number) => {
  if(!confirm("⚠️ Remover este projeto?")) return;
  try { await api.delete(`/api/projects/${id}`); projects.value = projects.value.filter(p => p.id !== id); } catch (e) { alert("Erro ao excluir."); }
};

const openWhatsApp = (phone: string, name: string) => {
    const cleanPhone = phone.replace(/\D/g, ''); 
    const text = `Olá ${name}, recebemos seu interesse pelo site da VDP Construct!`;
    window.open(`https://wa.me/55${cleanPhone}?text=${encodeURIComponent(text)}`, '_blank');
}
</script>

<template>
  <div class="min-h-screen bg-stone-100 font-sans text-stone-800 flex">
    
    <aside class="w-20 lg:w-64 bg-stone-900 text-stone-400 flex-shrink-0 hidden md:flex flex-col">
      <div class="h-20 flex items-center justify-center lg:justify-start lg:px-8 border-b border-stone-800">
        <div class="font-serif font-bold text-white text-xl tracking-tighter cursor-pointer" @click="activeTab = 'projects'">
            VDP<span class="text-amber-600">.</span>
        </div>
      </div>
      <nav class="p-4 space-y-2">
        <a 
          href="#" 
          @click.prevent="activeTab = 'projects'"
          class="flex items-center gap-3 px-4 py-3 rounded-lg transition"
          :class="activeTab === 'projects' ? 'bg-amber-600 text-white shadow-lg' : 'hover:bg-stone-800 hover:text-white'"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            <span class="hidden lg:block font-medium">Obras</span>
        </a>

        <a 
          href="#" 
          @click.prevent="activeTab = 'leads'"
          class="flex items-center gap-3 px-4 py-3 rounded-lg transition"
          :class="activeTab === 'leads' ? 'bg-amber-600 text-white shadow-lg' : 'hover:bg-stone-800 hover:text-white'"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            <span class="hidden lg:block font-medium">Leads / Clientes</span>
            <span v-if="leads.length > 0" class="hidden lg:flex ml-auto bg-stone-800 text-amber-500 text-xs font-bold px-2 py-0.5 rounded-full">
                {{ leads.length }}
            </span>
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
                <span class="text-stone-400 font-normal">Painel /</span> 
                {{ activeTab === 'projects' ? 'Gestão de Obras' : 'Gestão de Leads' }}
            </h2>
            <div class="flex items-center gap-4">
                <div class="h-10 w-10 bg-stone-900 rounded-full flex items-center justify-center text-amber-500 font-serif font-bold text-lg shadow-lg">A</div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-8 bg-stone-100">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 animate-fade-in-up">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-stone-200 flex items-center justify-between">
                    <div>
                        <p class="text-stone-500 text-xs font-bold uppercase tracking-wider mb-1">Total de Obras</p>
                        <p class="text-3xl font-bold text-stone-900">{{ stats.totalProjects }}</p>
                    </div>
                    <div class="p-3 bg-stone-100 rounded-lg text-stone-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg></div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-stone-200 flex items-center justify-between">
                    <div>
                        <p class="text-stone-500 text-xs font-bold uppercase tracking-wider mb-1">Interessados (Leads)</p>
                        <p class="text-3xl font-bold text-amber-600">{{ stats.totalLeads }}</p>
                    </div>
                    <div class="p-3 bg-amber-50 rounded-lg text-amber-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path></svg></div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-stone-200 flex items-center justify-between">
                    <div>
                        <p class="text-stone-500 text-xs font-bold uppercase tracking-wider mb-1">Obras em Atividade</p>
                        <p class="text-3xl font-bold text-blue-600">{{ stats.activeProjects }}</p>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-lg text-blue-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg></div>
                </div>
            </div>

            <div v-if="activeTab === 'projects'">
                <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                    <div class="relative w-full md:w-96">
                        <span class="absolute left-3 top-3 text-stone-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg></span>
                        <input v-model="searchQuery" type="text" placeholder="Buscar empreendimento..." class="w-full pl-10 pr-4 py-3 bg-white border border-stone-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-stone-900 focus:border-transparent shadow-sm transition" />
                    </div>
                    <button @click="openCreateModal" class="w-full md:w-auto bg-stone-900 hover:bg-amber-600 text-white px-6 py-3 rounded-xl shadow-lg transition flex items-center justify-center gap-2 font-medium">
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
                                <th class="p-5 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            <tr v-if="filteredProjects.length === 0"><td colspan="4" class="p-10 text-center text-stone-400">Nenhum projeto encontrado.</td></tr>
                            <tr v-for="project in filteredProjects" :key="project.id" class="group hover:bg-stone-50 transition">
                                <td class="p-5">
                                    <div class="flex items-center gap-4">
                                        <div class="h-16 w-24 rounded-lg overflow-hidden bg-stone-200 border border-stone-200 shadow-sm flex-shrink-0">
                                            <img :src="project.image_url" class="h-full w-full object-cover">
                                        </div>
                                        <div>
                                            <div class="font-bold text-stone-800">{{ project.title }}</div>
                                            <div class="text-stone-500 text-xs mt-1 flex items-center gap-1">{{ project.location }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-5 text-xs text-stone-500">
                                    Início: <strong>{{ formatDate(project.start_date) }}</strong><br>
                                    Entrega: <strong>{{ formatDate(project.end_date) }}</strong>
                                </td>
                                <td class="p-5">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide" :class="statusConfig[project.status]?.class">
                                        {{ statusConfig[project.status]?.label }}
                                    </span>
                                </td>
                                <td class="p-5 text-right">
                                    <button @click="openEditModal(project)" class="p-2 text-stone-400 hover:text-blue-600 transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></button>
                                    <button @click="handleDelete(project.id)" class="p-2 text-stone-400 hover:text-red-600 transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="activeTab === 'leads'">
                 <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                    <div class="relative w-full md:w-96">
                        <span class="absolute left-3 top-3 text-stone-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg></span>
                        <input v-model="searchQuery" type="text" placeholder="Buscar por nome do cliente..." class="w-full pl-10 pr-4 py-3 bg-white border border-stone-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-stone-900 transition" />
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-stone-50 border-b border-stone-200 text-stone-500 text-xs uppercase tracking-wider font-semibold">
                                <th class="p-5">Cliente</th>
                                <th class="p-5">Interesse em</th>
                                <th class="p-5">Mensagem</th>
                                <th class="p-5">Data</th>
                                <th class="p-5 text-right">Contato</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            <tr v-if="filteredLeads.length === 0"><td colspan="5" class="p-10 text-center text-stone-400">Nenhum interessado registrado ainda.</td></tr>
                            <tr v-for="lead in filteredLeads" :key="lead.id" class="group hover:bg-stone-50 transition">
                                <td class="p-5">
                                    <div class="font-bold text-stone-900">{{ lead.name }}</div>
                                    <div class="text-xs text-stone-500 mt-0.5">{{ lead.email || lead.phone }}</div>
                                </td>
                                <td class="p-5">
                                    <span class="px-2 py-1 bg-amber-50 text-amber-700 text-xs font-bold rounded-md border border-amber-100">
                                        {{ lead.project?.title }}
                                    </span>
                                </td>
                                <td class="p-5 text-sm text-stone-600 max-w-xs truncate" :title="lead.message || ''">
                                    {{ lead.message || 'Sem mensagem' }}
                                </td>
                                <td class="p-5 text-xs text-stone-500">
                                    <div class="font-bold">{{ formatDate(lead.created_at) }}</div>
                                    <div>{{ formatTime(lead.created_at) }}</div>
                                </td>
                                <td class="p-5 text-right">
                                    <button 
                                        @click="openWhatsApp(lead.phone, lead.name)"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-xs font-bold rounded-lg transition shadow-sm"
                                    >
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                        WhatsApp
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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
             <div><label class="block text-xs font-bold text-stone-500 uppercase mb-2">Nome</label><input v-model="form.title" required class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-stone-900 transition" /></div>
             <div><label class="block text-xs font-bold text-stone-500 uppercase mb-2">Local</label><input v-model="form.location" required class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-stone-900 transition" /></div>
             <div class="grid grid-cols-2 gap-5">
                <div><label class="block text-xs font-bold text-stone-500 uppercase mb-2">Início</label><input v-model="form.start_date" type="date" required class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-lg transition" /></div>
                <div><label class="block text-xs font-bold text-stone-500 uppercase mb-2">Entrega</label><input v-model="form.end_date" type="date" class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-lg transition" /></div>
             </div>
             <div>
                <label class="block text-xs font-bold text-stone-500 uppercase mb-2">Status</label>
                <select v-model="form.status" class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-stone-900 transition">
                    <option value="planning">Planejamento</option><option value="in_progress">Em Construção</option><option value="finished">Entregue</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-stone-500 uppercase mb-2">Capa</label>
                <input type="file" @change="handleFileUpload" accept="image/*" class="w-full text-sm text-stone-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-stone-100 file:text-stone-700 hover:file:bg-stone-200" :required="!editingId" />
            </div>
            <div class="pt-4 flex gap-3">
                <button type="button" @click="showModal = false" class="flex-1 py-3 border border-stone-300 text-stone-600 font-bold rounded-lg hover:bg-stone-50">Cancelar</button>
                <button type="submit" :disabled="isSaving" class="flex-1 py-3 bg-stone-900 text-white font-bold rounded-lg hover:bg-amber-600 shadow-lg">{{ isSaving ? 'Salvando...' : 'Salvar' }}</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</template>