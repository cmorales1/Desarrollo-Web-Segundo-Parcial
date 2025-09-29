<script setup>
import { ref, onMounted } from 'vue'
import http from '../services/http'
const tasks=ref([]), title=ref(''), error=ref('')
const load=async()=>{ try{ tasks.value=(await http.get('/tasks')).data }catch(e){ error.value='401: inicia sesión'} }
const addTask=async()=>{ if(!title.value) return; await http.post('/tasks',{title:title.value}); title.value=''; await load() }
const removeTask=async(id)=>{ await http.delete(`/tasks/${id}`); await load() }
onMounted(load)
</script>
<template>
  <div style="max-width:600px;margin:40px auto">
    <h2>Tareas</h2>
    <div><input v-model="title" placeholder="Nueva tarea"/><button @click="addTask">Agregar</button></div>
    <p v-if="error" style="color:red">{{ error }}</p>
    <ul><li v-for="t in tasks" :key="t.id">{{ t.title }} <button @click="removeTask(t.id)">X</button></li></ul>
  </div>
</template>