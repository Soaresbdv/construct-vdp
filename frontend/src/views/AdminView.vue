<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue';
import api from '@/lib/axios';

import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  ArcElement
} from 'chart.js'
import { Line, Doughnut } from 'vue-chartjs'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, ArcElement)

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
  project: { title: string; };
}

const activeTab = ref<'projects' | 'leads'>('projects');
const isLoading = ref(false);
const searchQuery = ref('');

const projects = ref<Project[]>([]);
const leads = ref<Lead[]>([]);
const stats = ref<any>({ cards: {}, charts: { growth: { labels: [], data: [] }, popularity: { labels: [], data: [] } } }); // Estado inicial seguro
const chartPeriod = ref('6_months'); 

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'bottom' as const, labels: { usePointStyle: true, boxWidth: 8 } }
  }
}

const lineChartData = computed(() => ({
  labels: stats.value.charts.growth.labels || ['Jan', 'Fev', 'Mar'], // Fallback visual
  datasets: [{
    label: 'Novos Leads',
    backgroundColor: '#d97706', // Amber-600
    borderColor: '#d97706',
    data: stats.value.charts.growth.data || [0, 0, 0],
    tension: 0.4
  }]
}));

const donutChartData = computed(() => ({
  labels: stats.value.charts.popularity.labels || ['Sem dados'],
  datasets: [{
    backgroundColor: ['#1c1917', '#d97706', '#57534e', '#78716c', '#a8a29e'], 
    data: stats.value.charts.popularity.data || [1],
  }]
}));

const isSaving = ref(false);
const showModal = ref(false);
const editingId = ref<number | null>(null);
const form = reactive({ title: '', location: '', status: 'planning', start_date: '', end_date: '', imageFile: null as File | null });

const statusConfig: Record<string, { label: string, class: string, icon: string }> = {
  planning: { label: 'Planejamento', class: 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
  in_progress: { label: 'Em Obra', class: 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' },
  finished: { label: 'Entregue', class: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
};

const loadData = async () => {

  try {
    const [resProjects, resLeads, resStats] = await Promise.all([
        api.get('/api/projects'),
        api.get('/api/admin/leads'),
        api.get(`/api/admin/stats?period=${chartPeriod.value}`)
    ]);
    projects.value = resProjects.data;
    leads.value = resLeads.data;
    stats.value = resStats.data;
  } catch (error) {
    console.error("Erro ao carregar dados:", error);
  } finally {
    isLoading.value = false;
  }
};

const changeChartPeriod = async () => {
    const resStats = await api.get(`/api/admin/stats?period=${chartPeriod.value}`);
    stats.value = resStats.data;
};

onMounted(loadData);

const filteredProjects = computed(() => {
  if (!searchQuery.value) return projects.value;
  return projects.value.filter(p => p.title.toLowerCase().includes(searchQuery.value.toLowerCase()));
});
const filteredLeads = computed(() => {
  if (!searchQuery.value) return leads.value;
  return leads.value.filter(l => l.name.toLowerCase().includes(searchQuery.value.toLowerCase()) || l.project?.title.toLowerCase().includes(searchQuery.value.toLowerCase()));
});

const formatDate = (dateStr: string | null) => dateStr ? new Date(dateStr).toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';
const formatTime = (dateStr: string) => new Date(dateStr).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
const openCreateModal = () => { editingId.value = null; resetForm(); showModal.value = true; };
const openEditModal = (project: Project) => {
  editingId.value = project.id;
  Object.assign(form, { ...project, start_date: project.start_date?.split('T')[0] ?? '', end_date: project.end_date?.split('T')[0] ?? '', imageFile: null });
  showModal.value = true;
};

const resetForm = () => { Object.assign(form, { title: '', location: '', status: 'planning', start_date: '', end_date: '', imageFile: null }); };
const handleFileUpload = (e: Event) => { const t = e.target as HTMLInputElement; if(t.files?.[0]) form.imageFile = t.files[0]; };

const handleSave = async () => {
  isSaving.value = true;
  const fd = new FormData();
  fd.append('title', form.title); fd.append('location', form.location); fd.append('status', form.status);
  fd.append('start_date', form.start_date); if(form.end_date) fd.append('end_date', form.end_date); 
  if (form.imageFile) fd.append('image', form.imageFile);
  try {
    const url = editingId.value ? `/api/projects/${editingId.value}` : '/api/projects';
    if(editingId.value) fd.append('_method', 'PUT');
    await api.post(url, fd); alert("Sucesso!"); showModal.value = false; loadData();
  } catch (e) { alert("Erro ao salvar."); } finally { isSaving.value = false; }
};

const handleDelete = async (id: number) => { if(confirm("Remover?")) { await api.delete(`/api/projects/${id}`); loadData(); } };
const openWhatsApp = (phone: string, name: string) => { window.open(`https://wa.me/55${phone.replace(/\D/g, '')}?text=Olá ${name}, falamos da VDP!`, '_blank'); };

</script>
<template>
  <div class="min-h-screen bg-stone-100 font-sans text-stone-800 flex">
    
    <aside class="w-20 lg:w-64 bg-stone-900 text-stone-400 flex-shrink-0 hidden md:flex flex-col">
      <div class="h-20 flex items-center justify-center lg:justify-start lg:px-8 border-b border-stone-800 cursor-pointer" @click="activeTab = 'projects'">
        <div class="font-serif font-bold text-white text-xl tracking-tighter">VDP<span class="text-amber-600">.</span></div>
      </div>
      <nav class="p-4 space-y-2">
        <a href="#" @click.prevent="activeTab = 'projects'" class="flex items-center gap-3 px-4 py-3 rounded-lg transition" :class="activeTab === 'projects' ? 'bg-amber-600 text-white shadow-lg' : 'hover:bg-stone-800 hover:text-white'">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            <span class="hidden lg:block font-medium">Dashboard & Obras</span>
        </a>
        <a href="#" @click.prevent="activeTab = 'leads'" class="flex items-center gap-3 px-4 py-3 rounded-lg transition" :class="activeTab === 'leads' ? 'bg-amber-600 text-white shadow-lg' : 'hover:bg-stone-800 hover:text-white'">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            <span class="hidden lg:block font-medium">Leads / Clientes</span>
            <span v-if="stats.cards.total_leads > 0" class="hidden lg:flex ml-auto bg-stone-800 text-amber-500 text-xs font-bold px-2 py-0.5 rounded-full">{{ stats.cards.total_leads }}</span>
        </a>
      </nav>
      <div class="mt-auto p-6 border-t border-stone-800">
        <RouterLink to="/" class="flex items-center gap-2 text-xs uppercase tracking-wider hover:text-white transition">&larr; Voltar ao Site</RouterLink>
      </div>
    </aside>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <header class="h-20 bg-white border-b border-stone-200 flex items-center justify-between px-8 shadow-sm z-20">
            <h2 class="text-xl font-bold text-stone-800 flex items-center gap-2">
                <span class="text-stone-400 font-normal">Painel /</span> {{ activeTab === 'projects' ? 'Visão Geral' : 'Gestão de Leads' }}
            </h2>
            <div class="h-10 w-10 bg-stone-900 rounded-full flex items-center justify-center text-amber-500 font-serif font-bold text-lg shadow-lg">A</div>
        </header>

        <main class="flex-1 overflow-y-auto p-8 bg-stone-100">
            
            <div v-if="!isLoading && activeTab === 'projects'" class="mb-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-stone-200">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="font-bold text-stone-700">Evolução de Leads</h3>
                                <p class="text-xs text-stone-400 mt-0.5">Acompanhe o volume de interessados</p>
                            </div>
                            
                            <div class="relative">
                                <select 
                                    v-model="chartPeriod" 
                                    @change="changeChartPeriod"
                                    class="appearance-none bg-stone-50 border border-stone-200 text-stone-600 text-xs font-bold py-2 pl-3 pr-8 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500/20 cursor-pointer hover:bg-stone-100 transition"
                                >
                                    <option value="30_days">Últimos 30 Dias</option>
                                    <option value="6_months">Últimos 6 Meses</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-stone-500">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>

                        </div>
                        <div class="h-64">
                            <Line :data="lineChartData" :options="chartOptions" :key="chartPeriod" />
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-sm border border-stone-200">
                        <h3 class="font-bold text-stone-700 mb-4">Obras Mais Procuradas</h3>
                        <div class="h-48 relative">
                             <Doughnut :data="donutChartData" :options="chartOptions" />
                        </div>
                        <div class="text-center mt-4">
                            <p class="text-xs text-stone-400">Baseado em cliques em "Tenho Interesse"</p>
                        </div>
                    </div>

                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8 animate-fade-in-up">
                <div class="bg-white p-5 rounded-xl shadow-sm border border-stone-200">
                    <p class="text-stone-500 text-[10px] font-bold uppercase tracking-wider">Total Obras</p>
                    <p class="text-2xl font-bold text-stone-900">{{ stats.cards.total_projects || 0 }}</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-stone-200">
                    <p class="text-stone-500 text-[10px] font-bold uppercase tracking-wider">Em Andamento</p>
                    <p class="text-2xl font-bold text-amber-600">{{ stats.cards.active_projects || 0 }}</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-stone-200">
                    <p class="text-stone-500 text-[10px] font-bold uppercase tracking-wider">Total Leads</p>
                    <p class="text-2xl font-bold text-green-600">{{ stats.cards.total_leads || 0 }}</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-stone-200">
                     <p class="text-stone-500 text-[10px] font-bold uppercase tracking-wider">Visualizações</p>
                    <p class="text-2xl font-bold text-blue-600">{{ stats.cards.total_views || 0 }}</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
                
                <div class="p-4 border-b border-stone-100 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="relative w-full md:w-80">
                         <span class="absolute left-3 top-3 text-stone-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg></span>
                         <input v-model="searchQuery" type="text" :placeholder="activeTab === 'projects' ? 'Buscar obra...' : 'Buscar cliente...'" class="w-full pl-9 pr-4 py-2 bg-stone-50 border border-stone-200 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-stone-900 transition" />
                    </div>
                    <button v-if="activeTab === 'projects'" @click="openCreateModal" class="bg-stone-900 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-amber-600 transition flex items-center gap-2">
                        <span>+ Nova Obra</span>
                    </button>
                </div>

                <table v-if="activeTab === 'projects'" class="w-full text-left">
                    <thead class="bg-stone-50 text-stone-500 text-xs uppercase font-semibold border-b border-stone-200">
                        <tr><th class="p-4">Obra</th><th class="p-4">Cronograma</th><th class="p-4">Status</th><th class="p-4 text-right">Ações</th></tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <tr v-for="p in filteredProjects" :key="p.id" class="hover:bg-stone-50">
                            <td class="p-4 flex items-center gap-3">
                                <img :src="p.image_url" class="h-10 w-16 object-cover rounded shadow-sm bg-stone-200">
                                <div><div class="font-bold text-sm">{{ p.title }}</div><div class="text-xs text-stone-500">{{ p.location }}</div></div>
                            </td>
                            <td class="p-4 text-xs text-stone-500">{{ formatDate(p.start_date) }} - {{ formatDate(p.end_date) }}</td>
                            <td class="p-4"><span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase border" :class="statusConfig[p.status]?.class">{{ statusConfig[p.status]?.label }}</span></td>
                            <td class="p-4 text-right"><button @click="openEditModal(p)" class="text-stone-400 hover:text-blue-600 mr-2">Editar</button><button @click="handleDelete(p.id)" class="text-stone-400 hover:text-red-600">Excluir</button></td>
                        </tr>
                    </tbody>
                </table>

                <table v-if="activeTab === 'leads'" class="w-full text-left">
                    <thead class="bg-stone-50 text-stone-500 text-xs uppercase font-semibold border-b border-stone-200">
                        <tr><th class="p-4">Nome</th><th class="p-4">Interesse</th><th class="p-4">Data</th><th class="p-4 text-right">Contato</th></tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <tr v-for="l in filteredLeads" :key="l.id" class="hover:bg-stone-50">
                            <td class="p-4"><div class="font-bold text-sm">{{ l.name }}</div><div class="text-xs text-stone-500">{{ l.phone }}</div></td>
                            <td class="p-4"><span class="bg-amber-50 text-amber-700 px-2 py-1 rounded text-xs font-bold">{{ l.project?.title }}</span></td>
                            <td class="p-4 text-xs text-stone-500">{{ formatDate(l.created_at) }} <span class="text-stone-300">|</span> {{ formatTime(l.created_at) }}</td>
                            <td class="p-4 text-right"><button @click="openWhatsApp(l.phone, l.name)" class="text-green-600 hover:text-green-700 font-bold text-xs border border-green-200 bg-green-50 px-3 py-1 rounded-full">WhatsApp</button></td>
                        </tr>
                    </tbody>
                </table>
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