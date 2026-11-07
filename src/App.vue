<script setup>
import { useGlobalState } from '@/store';
import Notify from "@/components/global/Notify.vue";

const store = useGlobalState();
</script>

<template>
  <div class="main-wrapper">
    <header>
      <h1>GA-Würfel</h1>
    </header>

    <main>
      <router-view />
    </main>
    
    <footer>
      by <a href="https://github.com/loganobrien/gawuerfel" target="_blank" rel="noopener noreferrer">dodo</a>
      <aside>
        <p>version: {{ store.app.version }}</p>
        <p>buildTime: {{ new Date(store.app.buildTime).toLocaleString("de-CH", { dateStyle: 'medium', timeStyle: 'short' }) }}</p>
      </aside>
    </footer>
  </div>
  <notify />
</template>

<style scoped>
.main-wrapper {
  margin: 0 auto;
  width: 40em;
}

@media (max-width: 600px) {
  .main-wrapper {
    margin: 0;
    width: 100%;
  }
}

h1 {
  font-size: 22px;
  color: var(--text-primary);
  font-weight: bold;
}

footer {
  margin-top: 2em;
  font-size: 0.8em;
  color: var(--text-secondary);
}

a {
  text-decoration: none;
  color: var(--accent-copper);
}

a:hover {
  color: var(--accent-copper-light);
}

aside {
  color: var(--text-secondary);
}

aside p {
  margin: 0;
}
</style>