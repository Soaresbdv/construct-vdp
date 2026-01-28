<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/lib/axios';

const router = useRouter();
const isLoading = ref(false);

// O formulário que será enviado
const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
});

// Onde guardaremos os erros de validação do Laravel
const errors = ref<any>({});

async function handleRegister() {
  isLoading.value = true;
  errors.value = {}; // Limpa erros antigos

  try {
    // 1. Segurança: Pede o cookie CSRF para o Laravel Sanctum
    await api.get('/sanctum/csrf-cookie');

    // 2. Envia os dados para a rota de registro do Breeze
    await api.post('/register', form.value);

    // 3. Se passou daqui, deu sucesso!
    alert('Cadastro realizado com sucesso! Bem-vindo.');
    
    // Redireciona para a Home
    router.push('/');

  } catch (error: any) {
    if (error.response?.status === 422) {
      // Erro 422 = Validação do Laravel (ex: email já existe)
      errors.value = error.response.data.errors;
    } else {
      console.error(error);
      alert('Ocorreu um erro inesperado. Verifique o console.');
    }
  } finally {
    isLoading.value = false;
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 p-4">
    <div class="w-full max-w-md bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
      
      <div class="bg-orange-600 p-6 text-center">
        <h1 class="text-2xl font-bold text-white">👷 Cadastro de Engenharia</h1>
        <p class="text-orange-100 text-sm mt-1">Sistema de Gestão de Obras</p>
      </div>
      
      <form @submit.prevent="handleRegister" class="p-8 space-y-4">
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo</label>
          <input 
            v-model="form.name" 
            type="text" 
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition"
            :class="{'border-red-500': errors.name, 'border-gray-300': !errors.name}"
            placeholder="Ex: Ana Arquiteta"
          >
          <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name[0] }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
          <input 
            v-model="form.email" 
            type="email" 
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition"
            :class="{'border-red-500': errors.email, 'border-gray-300': !errors.email}"
            placeholder="email@obra.com"
          >
          <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email[0] }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
          <input 
            v-model="form.password" 
            type="password" 
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition"
            :class="{'border-red-500': errors.password, 'border-gray-300': !errors.password}"
          >
          <p v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password[0] }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Senha</label>
          <input 
            v-model="form.password_confirmation" 
            type="password" 
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition"
          >
        </div>
        
        <button 
          type="submit" 
          :disabled="isLoading"
          class="w-full bg-slate-900 text-white font-bold py-3 rounded-lg hover:bg-slate-800 transition duration-200 cursor-pointer disabled:opacity-70 flex justify-center"
        >
          <span v-if="isLoading">Registrando...</span>
          <span v-else>Criar Conta</span>
        </button>
      </form>

      <div class="bg-gray-50 p-4 text-center border-t border-gray-100">
        <RouterLink to="/" class="text-sm text-orange-600 hover:underline">
          Voltar para Home
        </RouterLink>
      </div>

    </div>
  </div>
</template>