<script setup>
import { ref, computed } from "vue";
import { useEventBus } from "@vueuse/core";

// Set variables
const notifyConfig = ref({});
const displayed = ref(false);
const visible = computed(() => notifyConfig.value && notifyConfig.value.message?.length > 0);
const cls = computed(() => {
  return [notifyConfig.value.type, { displayed: displayed.value, modal: notifyConfig.value.message?.length && notifyConfig.value.modal }];
});
let timeoutId = null;

// Define "notify" event bus
useEventBus("notify").on((notifyCfg, typeCfg) => {
  let message = notifyCfg?.message || "";
  let type = notifyCfg?.type;
  let timeout = notifyCfg?.timeout;
  let modal = notifyCfg?.modal;

  if (typeof notifyCfg === "string") {
    message = notifyCfg;
  }

  type = type || (typeCfg || "ok");
  timeout = timeout || 1000 + message.length * 100;
  modal = typeCfg?.modal || false;

  if (!message) {
    return;
  }

  notifyConfig.value = { message, type, timeout, modal };

  if (notifyConfig.value.timeout > 0) {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
      hide();
      timeoutId = setTimeout(() => (notifyConfig.value.message = ""), 1000);
    }, notifyConfig.value.timeout);
  }

  setTimeout(() => (displayed.value = true), 0);
});

const hide = () => displayed.value = false;
</script>

<template>
  <section>
    <article :class="cls">
      <div v-if="visible" class="content" @click.self="hide()">
        <div class="text">
          <span>{{ notifyConfig.message }}</span>
        </div>
      </div>
    </article>
  </section>
</template>

<style scoped>
section {
  display: flex;
  justify-content: center;
  position: fixed;
  z-index: 10;
  left: 0;
  top: 0;
  right: 0;
  bottom: 0;
  pointer-events: none;
}

article {
  display: inline-block;
  position: fixed;
  bottom: -6em;
  opacity: 0;
  transition: opacity 1s, bottom .5s;
  pointer-events: auto;
}

article.displayed {
  opacity: 0.95;
  bottom: 1em;
}

.content {
  padding: 1em;
  background-color: var(--alert-green-bg);
  color: var(--alert-green-text);
  border-radius: 0.25em;
  border: 1px solid var(--alert-green-border);
}

article.warn .content {
  background-color: var(--alert-yellow-bg);
  color: var(--alert-yellow-text);
  border: 1px solid var(--alert-yellow-border);
}

article.bad .content {
  background-color: var(--alert-red-bg);
  color: var(--alert-red-text);
  border: 1px solid var(--alert-red-border);
}

.content .text {
  display: flex;
  flex-grow: 1;
  justify-content: center;
}
</style>