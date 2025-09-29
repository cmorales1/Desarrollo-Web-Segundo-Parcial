<script setup>
import { ref } from 'vue'
import http from '../services/http'
const email=ref(''); const password=ref(''); const error=ref('')
const doLogin=async()=>{ error.value=''
  try{ const {data}=await http.post('/login',{email:email.value,password:password.value})
    localStorage.setItem('token', data.token); window.location.href='/' }
  catch(e){ error.value = e?.response?.data?.message || 'Error de autenticación' }
}
</script>
<template>
  <div style="max-width:360px;margin:40px auto">
    <h2>Iniciar sesión</h2>
    <input v-model="email" type="email" placeholder="Email" style="width:100%;padding:8px;margin:6px 0" />
    <input v-model="password" type="password" placeholder="Password" style="width:100%;padding:8px;margin:6px 0" />
    <button @click="doLogin" style="width:100%;padding:10px">Entrar</button>
    <p v-if="error" style="color:red">{{ error }}</p>
  </div>
</template>