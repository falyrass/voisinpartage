import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '@/views/HomeView.vue'
import ItemDetailView from '@/views/ItemDetailView.vue'

const routes = [
  { path: '/', name: 'home', component: HomeView },
  { path: '/items/:id', name: 'item-detail', component: ItemDetailView, props: true },
]

export const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
