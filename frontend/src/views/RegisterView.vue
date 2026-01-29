<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/lib/axios';

const router = useRouter();
const isLoading = ref(false);

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
});

const errors = ref<any>({});

async function handleRegister() {
  isLoading.value = true;
  errors.value = {};

  try {
    await api.get('/sanctum/csrf-cookie');
    await api.post('/register', form.value);
    router.push('/');
  } catch (error: any) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    } else {
      alert('Erro inesperado.');
    }
  } finally {
    isLoading.value = false;
  }
}
</script>

<template>
  <div class="min-h-screen flex bg-white font-sans text-stone-800">
    
    <div class="hidden lg:block lg:w-1/2 relative overflow-hidden bg-stone-900">
      <img 
        src="https://images.unsplash.com/photo-1503387762-592deb58ef4e?q=80&w=1931&auto=format&fit=crop" 
        class="absolute inset-0 w-full h-full object-cover opacity-50 grayscale"
        alt="Construction Site Artistic"
      />
      <div class="absolute inset-0 bg-gradient-to-b from-transparent to-stone-900/90"></div>
      
      <div class="absolute bottom-12 left-12 max-w-lg text-white p-6">
        <h2 class="text-3xl font-serif font-bold mb-4">Junte-se à excelência.</h2>
        <p class="text-stone-300 font-light">
          Faça parte da equipe que está redefinindo o skyline da cidade com tecnologia e design sustentável.
        </p>
      </div>
    </div>

    <div class="w-full lg:w-1/2 flex flex-col justify-center px-12 sm:px-24 bg-white relative">
      
      <RouterLink to="/login" class="absolute top-8 right-12 text-sm font-medium text-stone-500 hover:text-amber-700 transition flex items-center gap-2">
        &larr; Voltar ao Login
      </RouterLink>

      <div class="max-w-md w-full mx-auto">
        <div class="mb-10">
          <span class="text-2xl font-serif font-bold text-stone-900 tracking-tighter">
            VDP<span class="text-amber-700">.</span>Construct
          </span>
        </div>

        <h1 class="text-3xl font-serif text-stone-900 mb-2">Criar nova conta</h1>
        <p class="text-stone-500 mb-8">Preencha os dados abaixo para iniciar seu cadastro.</p>

        <form @submit.prevent="handleRegister" class="space-y-5">
          
          <div class="group">
            <label class="block text-xs font-bold text-stone-500 uppercase tracking-wider mb-2">Nome Completo</label>
            <input 
              v-model="form.name" 
              type="text" 
              class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-lg outline-none focus:ring-2 focus:ring-amber-700/20 focus:border-amber-700 transition duration-300"
              :class="{'border-red-300': errors.name}"
              placeholder="Ex: Arq. João Silva"
            >
            <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name[0] }}</p>
          </div>

          <div class="group">
            <label class="block text-xs font-bold text-stone-500 uppercase tracking-wider mb-2">E-mail</label>
            <input 
              v-model="form.email" 
              type="email" 
              class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-lg outline-none focus:ring-2 focus:ring-amber-700/20 focus:border-amber-700 transition duration-300"
              :class="{'border-red-300': errors.email}"
              placeholder="seu@email.com"
            >
            <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email[0] }}</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-stone-500 uppercase tracking-wider mb-2">Senha</label>
              <input 
                v-model="form.password" 
                type="password" 
                class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-lg outline-none focus:ring-2 focus:ring-amber-700/20 focus:border-amber-700 transition duration-300"
                :class="{'border-red-300': errors.password}"
              >
            </div>
            <div>
              <label class="block text-xs font-bold text-stone-500 uppercase tracking-wider mb-2">Confirmar</label>
              <input 
                v-model="form.password_confirmation" 
                type="password" 
                class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-lg outline-none focus:ring-2 focus:ring-amber-700/20 focus:border-amber-700 transition duration-300"
              >
            </div>
          </div>
          <p v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password[0] }}</p>

          <button 
            type="submit" 
            :disabled="isLoading"
            class="w-full mt-4 bg-amber-700 text-white font-medium py-4 rounded-lg hover:bg-amber-800 transition duration-300 shadow-lg shadow-amber-900/20 cursor-pointer disabled:opacity-70 flex justify-center"
          >
             <span v-if="isLoading">Criando conta...</span>
             <span v-else>Finalizar Cadastro</span>
          </button>

        </form>
      </div>
    </div>

  </div>
</template>