<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/lib/axios';

const route = useRoute();
const router = useRouter();

const project = ref<any>(null);
const currentUser = ref<any>(null);
const isLoading = ref(true);
const showLeadModal = ref(false);
const isSending = ref(false);

const leadForm = reactive({
  name: '',
  phone: '',
  email: '',
  message: ''
});

const statusConfig: Record<string, { label: string, class: string, textClass: string }> = {
  planning: { label: 'Planejamento', class: 'bg-blue-50 border-blue-200', textClass: 'text-blue-700' },
  in_progress: { label: 'Em Andamento', class: 'bg-amber-50 border-amber-200', textClass: 'text-amber-700' },
  finished: { label: 'Entregue', class: 'bg-emerald-50 border-emerald-200', textClass: 'text-emerald-700' },
};

const formatDate = (dateString: string) => {
  if (!dateString) return '-';
  return new Date(dateString).toLocaleDateString('pt-BR');
};

const checkAuth = (actionName = 'continuar') => {
  if (!currentUser.value) {
    const confirmLogin = confirm(`Área Restrita\n\nPara ${actionName}, é necessário identificar-se.\nDeseja fazer login ou criar conta agora?`);
    if (confirmLogin) {
      router.push('/login');
    }
    return false; 
  }
  return true; 
};

const handleDownload = async () => {
  if (!checkAuth('baixar documentos técnicos')) return;

  try {
    alert("⬇Iniciando download seguro...");
    const response = await api.get(`/api/projects/${project.value.id}/download`);
    window.open(response.data.url, '_blank');
  } catch (error) {
    alert("Erro ao baixar arquivo.");
  }
};

const handleInterest = () => {

  if (!checkAuth('falar com nossos consultores')) return;

  if (currentUser.value) {
    leadForm.name = currentUser.value.name;
    leadForm.email = currentUser.value.email;
  }
  
  showLeadModal.value = true;
};

const submitLead = async () => {
  isSending.value = true;
  try {
    await api.post('/api/leads', {
      project_id: project.value.id,
      name: leadForm.name,
      phone: leadForm.phone,
      email: leadForm.email, 
      message: leadForm.message || `Tenho interesse no ${project.value.title}`
    });

    const phoneVendor = "5541995222729"; // Alterar par ao número real
    const text = `Olá! Me chamo *${leadForm.name}*.\nTenho interesse no empreendimento *${project.value.title}*.\n\n${leadForm.message}`;
    const url = `https://wa.me/${phoneVendor}?text=${encodeURIComponent(text)}`;
    
    window.open(url, '_blank');
    
    showLeadModal.value = false;
    alert("Solicitação recebida! Redirecionando para o atendimento...");
    
    leadForm.name = currentUser.value?.name || '';
    leadForm.phone = '';
    leadForm.message = '';

  } catch (error) {
    console.error(error);
    alert("Erro ao enviar solicitação. Tente novamente.");
  } finally {
    isSending.value = false;
  }
};

onMounted(async () => {
  try {
    const id = route.params.id;
    const responseProject = await api.get(`/api/projects/${id}`);
    project.value = responseProject.data;


    try {
      const responseUser = await api.get('/api/user');
      currentUser.value = responseUser.data;
    } catch (e) {
    }

  } catch (error) {
    router.push('/');
  } finally {
    isLoading.value = false;
  }
});
</script>

<template>
  <div class="min-h-screen bg-stone-50 font-sans text-stone-800 pb-20 relative">
    
    <div v-if="isLoading" class="flex h-screen items-center justify-center">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-amber-700"></div>
    </div>

    <div v-else-if="project">
      
      <header class="relative h-[65vh] w-full">
        <div class="absolute inset-0 bg-stone-900/30 z-10"></div>
        <img 
          :src="project.image_url" 
          class="w-full h-full object-cover"
          alt="Capa do Projeto"
        />
        
        <RouterLink 
          to="/" 
          class="absolute top-8 left-8 z-20 bg-white/10 backdrop-blur-md text-white px-5 py-2.5 rounded-full hover:bg-white/20 transition flex items-center gap-2 border border-white/20 text-sm font-medium"
        >
          &larr; Voltar
        </RouterLink>

        <div class="absolute bottom-0 left-0 w-full z-20 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-8 md:p-16">
          <div class="max-w-7xl mx-auto animate-fade-in-up">
            <span 
              class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider border mb-4 inline-block bg-white/20 text-white border-white/30 backdrop-blur-sm"
            >
              {{ statusConfig[project.status]?.label }}
            </span>
            <h1 class="text-4xl md:text-7xl font-serif text-white font-bold leading-tight mb-2">{{ project.title }}</h1>
            <p class="text-stone-300 text-lg flex items-center gap-2 font-light">
              <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
              {{ project.location }}
            </p>
          </div>
        </div>
      </header>

      <main class="max-w-7xl mx-auto px-6 -mt-16 relative z-30">
        <div class="bg-white rounded-xl shadow-2xl border border-stone-100 p-8 grid grid-cols-1 md:grid-cols-3 gap-8 items-center">
          
          <div class="space-y-4 border-b md:border-b-0 md:border-r border-stone-100 pb-6 md:pb-0">
            <h3 class="text-xs font-bold text-stone-400 uppercase tracking-[0.2em] flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Cronograma
            </h3>
            <div class="grid grid-cols-2 md:block md:space-y-4">
                <div>
                    <p class="text-[10px] text-stone-500 uppercase font-bold">Início</p>
                    <p class="text-lg font-serif text-stone-800">{{ formatDate(project.start_date) }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-stone-500 uppercase font-bold">Entrega</p>
                    <p class="text-lg font-serif text-stone-800">{{ formatDate(project.end_date) }}</p>
                </div>
            </div>
          </div>

          <div class="space-y-4 border-b md:border-b-0 md:border-r border-stone-100 pb-6 md:pb-0">
             <h3 class="text-xs font-bold text-stone-400 uppercase tracking-[0.2em]">Situação</h3>
             <div class="flex items-center gap-4">
               <div :class="`p-3 rounded-full border-2 ${statusConfig[project.status]?.class}`">
                 <svg class="w-6 h-6" :class="statusConfig[project.status]?.textClass" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
               </div>
               <div>
                 <p class="font-bold text-lg text-stone-800 leading-tight">{{ statusConfig[project.status]?.label }}</p>
                 <p class="text-xs text-stone-400 mt-1">Status oficial atualizado</p>
               </div>
             </div>
          </div>

          <div class="flex flex-col justify-center space-y-3">
            
            <button 
              @click="handleDownload"
              class="w-full py-3 bg-stone-100 text-stone-600 rounded-lg hover:bg-stone-200 transition font-medium text-sm flex items-center justify-center gap-2 group"
            >
              <svg class="w-4 h-4 text-stone-400 group-hover:text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
              Baixar Book (PDF)
            </button>
            
            <button 
              @click="handleInterest"
              class="w-full py-4 bg-stone-900 text-white rounded-lg hover:bg-amber-700 transition font-bold shadow-xl shadow-stone-900/20 flex items-center justify-center gap-2 transform hover:-translate-y-1 duration-300"
            >
              <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
              Tenho Interesse
            </button>

          </div>
        </div>

        <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-12">
          <div class="md:col-span-2 space-y-6">
            <h2 class="text-3xl font-serif font-bold text-stone-900">Sobre o Empreendimento</h2>
            <div class="h-1 w-20 bg-amber-600"></div>
            <p class="text-stone-600 leading-loose text-lg font-light">
              Este projeto representa o auge da engenharia moderna na região de {{ project.location }}. 
              Com acabamentos de alto padrão e sustentabilidade certificada, o <strong class="text-stone-900">{{ project.title }}</strong> foi projetado para oferecer conforto térmico e acústico superior.
              <br><br>
              A fachada imponente utiliza materiais nobres, enquanto os interiores foram pensados para maximizar a luz natural e a ventilação cruzada, criando ambientes saudáveis e sofisticados.
            </p>
          </div>
          
          <div class="bg-stone-100 p-8 rounded-xl h-fit">
            <h2 class="text-xl font-serif font-bold mb-6 text-stone-800">Ficha Técnica</h2>
            <ul class="space-y-4 text-stone-600 text-sm">
              <li class="flex justify-between border-b border-stone-200 pb-2"><span>Área Total</span> <span class="font-bold text-stone-900">12.500 m²</span></li>
              <li class="flex justify-between border-b border-stone-200 pb-2"><span>Torres</span> <span class="font-bold text-stone-900">2</span></li>
              <li class="flex justify-between border-b border-stone-200 pb-2"><span>Unidades</span> <span class="font-bold text-stone-900">84</span></li>
              <li class="flex justify-between border-b border-stone-200 pb-2"><span>Tipologia</span> <span class="font-bold text-stone-900">2 e 3 Quartos</span></li>
              <li class="flex justify-between pt-2"><span>Incorporação</span> <span class="font-bold text-stone-900">VDP Construct</span></li>
            </ul>
          </div>
        </div>
      </main>

    </div>

    <div v-if="showLeadModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-stone-900/70 backdrop-blur-sm transition-opacity" @click="showLeadModal = false"></div>
      
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden animate-fade-in-up">
        
        <div class="h-32 relative">
          <img :src="project.image_url" class="w-full h-full object-cover brightness-50">
          <div class="absolute inset-0 flex items-center justify-center">
             <h3 class="text-white font-serif font-bold text-2xl text-center px-4">Interesse em<br>{{ project.title }}</h3>
          </div>
          <button @click="showLeadModal = false" class="absolute top-4 right-4 text-white hover:text-amber-400 text-2xl leading-none">&times;</button>
        </div>

        <div class="p-8">
            <p class="text-stone-500 text-sm mb-6 text-center">Preencha seus dados. Nosso consultor receberá sua mensagem e iniciará o atendimento via WhatsApp.</p>
            
            <form @submit.prevent="submitLead" class="space-y-4">
                <div>
                  <label class="block text-xs font-bold uppercase tracking-wider text-stone-500 mb-1">Nome</label>
                  <input v-model="leadForm.name" type="text" required class="w-full p-3 bg-stone-50 border border-stone-200 rounded-lg focus:ring-2 focus:ring-stone-900 transition" placeholder="Seu nome completo">
                </div>
                <div>
                  <label class="block text-xs font-bold uppercase tracking-wider text-stone-500 mb-1">WhatsApp / Telefone</label>
                  <input v-model="leadForm.phone" type="text" required class="w-full p-3 bg-stone-50 border border-stone-200 rounded-lg focus:ring-2 focus:ring-stone-900 transition" placeholder="(DD) 99999-9999">
                </div>
                <div>
                  <label class="block text-xs font-bold uppercase tracking-wider text-stone-500 mb-1">Mensagem (Opcional)</label>
                  <textarea v-model="leadForm.message" rows="2" class="w-full p-3 bg-stone-50 border border-stone-200 rounded-lg focus:ring-2 focus:ring-stone-900 transition" placeholder="Gostaria de saber mais sobre valores..."></textarea>
                </div>

                <button 
                  type="submit" 
                  :disabled="isSending"
                  class="w-full bg-stone-900 hover:bg-amber-600 text-white font-bold py-4 rounded-lg transition mt-4 flex justify-center items-center gap-2 shadow-lg"
                >
                  <span v-if="isSending" class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full"></span>
                  <span v-else class="flex items-center gap-2">
                    Iniciar Conversa
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                  </span>
                </button>
            </form>
            <p class="text-[10px] text-stone-400 text-center mt-4">Seus dados estão seguros conosco.</p>
        </div>
      </div>
    </div>

  </div>
</template>