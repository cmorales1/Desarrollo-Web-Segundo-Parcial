import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue'
import Tasks from '../views/Tasks.vue'
export default createRouter({ history: createWebHistory(), routes: [
  { path: '/login', name: 'login', component: Login },
  { path: '/', name: 'tasks', component: Tasks },
]})