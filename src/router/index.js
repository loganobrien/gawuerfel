import { createRouter, createWebHistory } from 'vue-router'
import Dice from '@/components/Dice.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', component: Dice },
  ],
})

export default router
