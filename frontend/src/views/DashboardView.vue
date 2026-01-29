<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/lib/axios';

const router = useRouter();
const isMenuOpen = ref(false);

const projects = [
  { id: 1, title: 'Residencial Aurora', location: 'Curitiba, PR', img: 'https://images.unsplash.com/photo-1487958449943-2429e8be8625?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D' },
  { id: 2, title: 'Edifício Horizon', location: 'São Paulo, SP', img: 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?auto=format&fit=crop&q=80&w=800' },
  { id: 3, title: 'Complexo Industrial Norte', location: 'Joinville, SC', img: 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&q=80&w=800' },
  { id: 4, title: 'Loft Conceito Madeira', location: 'Gramado, RS', img: 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&q=80&w=800' },
];

async function handleLogout() {
  try {
    await api.post('/logout');
    router.push('/login');
  } catch (error) {
    console.error('Erro ao sair', error);
  }
}
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
          <a href="#" class="hover:text-amber-700 transition">Financeiro</a>
          <a href="#" class="hover:text-amber-700 transition">Equipe</a>
          <button 
            @click="handleLogout" 
            class="px-5 py-2 bg-stone-900 text-white rounded-full hover:bg-stone-800 transition cursor-pointer"
          >
            Sair
          </button>
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

      <div class="relative z-10 text-center px-6 max-w-4xl mt-16">
        <span class="text-amber-400 tracking-[0.2em] text-sm uppercase font-semibold">Bem-vindo ao Sistema</span>
        <h1 class="text-5xl md:text-7xl font-serif text-white mt-4 mb-6 leading-tight">
          Construindo o futuro com <br/><span class="italic text-stone-300">excelência e solidez.</span>
        </h1>
        <p class="text-stone-300 text-lg md:text-xl max-w-2xl mx-auto font-light">
          Gerencie suas obras, cronogramas e orçamentos em uma interface projetada para a clareza.
        </p>
      </div>
    </header>

    <section class="py-20 px-6 max-w-7xl mx-auto">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center md:text-left">
        <div class="p-8 bg-white border border-stone-100 shadow-sm rounded-2xl hover:shadow-lg transition duration-500">
          <p class="text-5xl font-light text-amber-700 mb-2">12</p>
          <p class="text-stone-500 uppercase tracking-wide text-xs font-bold">Obras em Andamento</p>
        </div>
        <div class="p-8 bg-white border border-stone-100 shadow-sm rounded-2xl hover:shadow-lg transition duration-500">
          <p class="text-5xl font-light text-stone-800 mb-2">84%</p>
          <p class="text-stone-500 uppercase tracking-wide text-xs font-bold">Eficiência Média</p>
        </div>
        <div class="p-8 bg-stone-900 shadow-sm rounded-2xl text-white hover:bg-stone-800 transition duration-500 cursor-pointer">
          <p class="text-3xl font-serif mb-2">Nova Obra +</p>
          <p class="text-stone-400 text-sm">Iniciar novo gerenciamento</p>
        </div>
      </div>
    </section>

    <section class="py-10 px-6 max-w-7xl mx-auto pb-32">
      <div class="flex justify-between items-end mb-12">
        <div>
          <h2 class="text-3xl font-serif text-stone-900">Projetos Recentes</h2>
          <div class="h-1 w-20 bg-amber-700 mt-4"></div>
        </div>
        <button class="text-stone-500 hover:text-stone-900 text-sm font-medium transition">Ver todos &rarr;</button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
        <div 
          v-for="project in projects" 
          :key="project.id" 
          class="group cursor-pointer"
        >
          <div class="overflow-hidden rounded-xl h-[400px] mb-6">
            <img 
              :src="project.img" 
              class="w-full h-full object-cover transform group-hover:scale-105 transition duration-700 ease-in-out grayscale-[20%] group-hover:grayscale-0"
            />
          </div>
          <h3 class="text-2xl font-serif text-stone-800 group-hover:text-amber-700 transition">{{ project.title }}</h3>
          <p class="text-stone-500 mt-1">{{ project.location }}</p>
        </div>
      </div>
    </section>

    <footer class="bg-white border-t border-stone-200 py-12 text-center text-stone-400 text-sm">
      <p>&copy; 2026 VDP Construct. Todos os direitos reservados.</p>
    </footer>

  </div>
</template>