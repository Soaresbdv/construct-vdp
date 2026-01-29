<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/lib/axios';

const router = useRouter();
const isLoading = ref(false);

const form = ref({
  email: '',
  password: '',
});

const errors = ref<any>({});

async function handleLogin() {
  isLoading.value = true;
  errors.value = {};

  try {
    await api.get('/sanctum/csrf-cookie');
    await api.post('/login', form.value);
    router.push('/dashboard'); 
  } catch (error: any) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    } else {
      console.error(error);
      alert('Erro de conexão.');
    }
  } finally {
    isLoading.value = false;
  }
}
</script>

<template>
  <div class="min-h-screen flex bg-white font-sans text-stone-800">
    
    <div class="w-full lg:w-1/2 flex flex-col justify-center px-12 sm:px-24">
      <div class="max-w-md w-full mx-auto">
        
        <div class="mb-12">
          <span class="text-2xl font-serif font-bold text-stone-900 tracking-tighter">
            VDP<span class="text-amber-700">.</span>Construct
          </span>
        </div>

        <h1 class="text-4xl font-serif text-stone-900 mb-2">Bem-vindo de volta.</h1>
        <p class="text-stone-500 mb-10">Por favor, insira seus dados para acessar o painel.</p>

        <form @submit.prevent="handleLogin" class="space-y-6">
          
          <div class="group">
            <label class="block text-xs font-bold text-stone-500 uppercase tracking-wider mb-2">E-mail Corporativo</label>
            <input 
              v-model="form.email" 
              type="email" 
              class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-lg outline-none focus:ring-2 focus:ring-amber-700/20 focus:border-amber-700 transition duration-300"
              :class="{'border-red-300 bg-red-50': errors.email}"
              placeholder="exemplo@vdp.com"
            >
            <p v-if="errors.email" class="text-red-500 text-xs mt-2">{{ errors.email[0] }}</p>
          </div>

          <div class="group">
            <div class="flex justify-between items-center mb-2">
              <label class="block text-xs font-bold text-stone-500 uppercase tracking-wider">Senha</label>
              <a href="#" class="text-xs text-amber-700 hover:underline">Esqueceu a senha?</a>
            </div>
            <input 
              v-model="form.password" 
              type="password" 
              class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-lg outline-none focus:ring-2 focus:ring-amber-700/20 focus:border-amber-700 transition duration-300"
              :class="{'border-red-300 bg-red-50': errors.password}"
              placeholder="••••••••"
            >
            <p v-if="errors.password" class="text-red-500 text-xs mt-2">{{ errors.password[0] }}</p>
          </div>

          <button 
            type="submit" 
            :disabled="isLoading"
            class="w-full bg-stone-900 text-white font-medium py-4 rounded-lg hover:bg-amber-700 transition duration-300 transform hover:-translate-y-0.5 shadow-lg shadow-stone-900/10 cursor-pointer disabled:opacity-70 disabled:cursor-not-allowed flex justify-center"
          >
            <span v-if="isLoading" class="flex items-center gap-2">
              <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
              Processando...
            </span>
            <span v-else>Acessar Sistema</span>
          </button>

        </form>

        <p class="mt-8 text-center text-sm text-stone-500">
          Ainda não possui acesso? 
          <RouterLink to="/register" class="text-stone-900 font-bold hover:text-amber-700 transition">Solicitar conta</RouterLink>
        </p>
      </div>
    </div>

    <div class="hidden lg:block lg:w-1/2 relative overflow-hidden bg-stone-900">
      <img 
        src="https://images.unsplash.com/photo-1486325212027-8081e485255e?q=80&w=2070&auto=format&fit=crop" 
        class="absolute inset-0 w-full h-full object-cover opacity-60"
        alt="Architecture Detail"
      />
      <div class="absolute inset-0 bg-gradient-to-t from-stone-900 via-transparent to-transparent"></div>
      
      <div class="absolute bottom-12 left-12 max-w-lg text-white">
        <p class="text-amber-500 font-serif italic text-lg mb-2">"A arquitetura é a vontade de uma época traduzida em espaço."</p>
        <p class="text-stone-400 text-sm uppercase tracking-widest">— Mies van der Rohe</p>
      </div>
    </div>

  </div>
</template>