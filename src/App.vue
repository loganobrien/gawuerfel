<script setup>
import { useGlobalState } from '@/store';
import Notify from "@/components/global/Notify.vue";

const store = useGlobalState();
</script>

<template>
  <header>
    <h1>GA-Würfel</h1>
    <aside>
      <p>version: {{ store.app.version }}</p>
      <p>buildTime: {{ new Date(store.app.buildTime).toLocaleString("de-CH", { dateStyle: 'medium', timeStyle: 'short' }) }}</p>
    </aside>
  </header>
  <main>
    <router-view />
  </main>
  <footer>
    by <a href="https://github.com/loganobrien/gawuerfel" target="_blank" rel="noopener noreferrer">dodo</a>
  </footer>
  <notify />
</template>

<style scoped>
h1 {
  font-size: 22px;
  color: var(--text-primary);
  font-weight: bold;
}

aside {
  position: absolute;
  top: 0.5em;
  right: 0.5em;
  text-align: right;
  color: var(--text-secondary);
}

aside p {
  margin: 0;
  font-size: 0.8em;
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
</style>