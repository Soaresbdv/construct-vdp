<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/lib/axios';

const router = useRouter();
const currentUser = ref<any>(null);

const handleLogout = async () => {
  try { await api.post('/logout'); } catch (e) {} 
  finally {
    localStorage.removeItem('is_logged_in');
    window.location.reload();
  }
};

onMounted(async () => {
  if (localStorage.getItem('is_logged_in') === 'true') {
      try { const res = await api.get('/api/user'); currentUser.value = res.data; } catch (e) {}
  }
});

const values = [
    { title: 'Design Autoral', desc: 'Cada projeto é único, desenhado para se integrar organicamente ao ambiente urbano.', icon: 'M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z' },
    { title: 'Engenharia de Precisão', desc: 'Utilizamos BIM e tecnologias construtivas que garantem durabilidade secular.', icon: 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z' },
    { title: 'Sustentabilidade Real', desc: 'Certificação LEED e reaproveitamento de recursos em 100% dos empreendimentos.', icon: 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
];
</script>

<template>
  <div class="min-h-screen bg-white font-sans text-stone-800">
    
    <nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-stone-100">
      <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <div class="text-xl font-serif font-bold cursor-pointer" @click="router.push('/')">
          VDP<span class="text-amber-700">.</span>Construct
        </div>
        
        <div class="hidden md:flex gap-6 items-center text-sm font-medium text-stone-600">
          <RouterLink to="/portfolio" class="hover:text-amber-700 transition">Portfólio</RouterLink>
          <RouterLink to="/about" class="text-stone-900 font-bold border-b-2 border-amber-600">Sobre</RouterLink>
          
          <div class="h-6 w-px bg-stone-200 mx-2"></div>
          
          <div v-if="currentUser">
             <RouterLink v-if="currentUser.is_admin" to="/admin" class="hover:text-amber-700">Admin</RouterLink>
             <button @click="handleLogout" class="ml-4 text-stone-400 hover:text-red-500">Sair</button>
          </div>
          <RouterLink v-else to="/login" class="hover:text-amber-700">Login</RouterLink>
        </div>
      </div>
    </nav>

    <header class="relative h-[60vh] flex items-center justify-center">
        <div class="absolute inset-0">
            <img src="https://images.unsplash.com/photo-1541888946425-d81bb19240f5?q=80&w=1920&auto=format&fit=crop" class="w-full h-full object-cover brightness-[0.4] grayscale-[30%]" />
        </div>
        <div class="relative z-10 text-center max-w-3xl px-6 animate-fade-in-up">
            <p class="text-amber-500 font-bold tracking-[0.3em] uppercase text-xs mb-4">Desde 2010</p>
            <h1 class="text-5xl md:text-7xl font-serif text-white leading-tight">
                Edificando o futuro <br/><span class="italic font-light opacity-80">com solidez.</span>
            </h1>
        </div>
    </header>

    <section class="py-24 px-6 max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            
            <div class="space-y-6">
                <h2 class="text-4xl font-serif text-stone-900">Não construímos apenas prédios. <span class="italic text-stone-400">Criamos marcos.</span></h2>
                <div class="h-1 w-20 bg-amber-700"></div>
                
                <p class="text-stone-600 leading-relaxed text-lg font-light">
                    A VDP Construct nasceu da visão de unir a precisão da construção com a elegância da arquitetura contemporânea brasileira.
                </p>
                <p class="text-stone-600 leading-relaxed">
                    Em mais de uma década de atuação, nos especializamos no segmento de alto padrão, entregando não apenas metros quadrados, mas experiências de vida. Nossa filosofia é simples: cada detalhe importa, desde a fundação até o acabamento final.
                </p>
                
                <div class="pt-4">
                   <!-- <img src="https://upload.wikimedia.org/wikipedia/commons/f/fa/Signature_sample.svg" class="h-12 opacity-60" alt="Assinatura CEO"> adicionar assinatura futuramente -->
                   <p class="text-xs text-stone-400 uppercase tracking-widest mt-2">Douglas Avelino & VDP Building</p>
                </div>
            </div>

            <div class="relative h-[600px] bg-stone-100 rounded-sm overflow-hidden shadow-2xl">
                <img src="https://images.unsplash.com/photo-1503387762-592deb58ef4e?q=80&w=1920&auto=format&fit=crop" class="w-full h-full object-cover hover:scale-105 transition duration-[1.5s]" />
                <div class="absolute bottom-8 left-8 bg-white/90 backdrop-blur p-6 max-w-xs shadow-lg border-l-4 border-amber-600">
                    <p class="text-2xl font-serif text-stone-900">"A excelência é um hábito, não um ato."</p>
                </div>
            </div>

        </div>
    </section>

    <section class="bg-stone-900 text-white py-20">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-2 md:grid-cols-4 gap-8 text-center divide-x divide-stone-800/50">
            <div>
                <p class="text-4xl md:text-5xl font-serif text-amber-500 mb-2">15</p>
                <p class="text-xs uppercase tracking-widest text-stone-400">Anos de História</p>
            </div>
            <div>
                <p class="text-4xl md:text-5xl font-serif text-amber-500 mb-2">32</p>
                <p class="text-xs uppercase tracking-widest text-stone-400">Obras Entregues</p>
            </div>
            <div>
                <p class="text-4xl md:text-5xl font-serif text-amber-500 mb-2">120k</p>
                <p class="text-xs uppercase tracking-widest text-stone-400">m² Construídos</p>
            </div>
            <div>
                <p class="text-4xl md:text-5xl font-serif text-amber-500 mb-2">100%</p>
                <p class="text-xs uppercase tracking-widest text-stone-400">No Prazo</p>
            </div>
        </div>
    </section>

    <section class="py-24 px-6 max-w-7xl mx-auto bg-stone-50">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <h2 class="text-3xl font-serif text-stone-900 mb-4">Nossos Pilares</h2>
            <p class="text-stone-500">O que sustenta nossa reputação no mercado.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div v-for="(item, index) in values" :key="index" class="bg-white p-10 shadow-sm border border-stone-100 hover:shadow-xl hover:-translate-y-2 transition duration-300 group">
                <div class="w-14 h-14 bg-stone-50 rounded-full flex items-center justify-center mb-6 group-hover:bg-amber-50 transition">
                    <svg class="w-6 h-6 text-stone-400 group-hover:text-amber-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="item.icon"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-serif font-bold text-stone-900 mb-3">{{ item.title }}</h3>
                <p class="text-stone-500 leading-relaxed text-sm">{{ item.desc }}</p>
            </div>
        </div>
    </section>

    <section class="py-24 px-6 text-center">
        <h2 class="text-4xl font-serif text-stone-900 mb-6">Pronto para investir no extraordinário?</h2>
        <div class="flex justify-center gap-4">
             <RouterLink to="/portfolio" class="px-8 py-3 bg-stone-900 text-white font-medium hover:bg-amber-700 transition shadow-lg">
                Ver Portfólio
             </RouterLink>
             <a href="https://wa.me/5541995222729" target="_blank" class="px-8 py-3 border border-stone-300 text-stone-600 font-medium hover:bg-stone-50 transition">
                Fale Conosco
             </a>
        </div>
    </section>

    <footer class="bg-white border-t border-stone-200 py-12 text-center text-stone-400 text-xs uppercase tracking-wider">
      <p>&copy; 2026 VDP Construct.</p>
    </footer>

  </div>
</template>