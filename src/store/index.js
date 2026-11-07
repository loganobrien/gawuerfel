import { reactive } from "vue";
import { createGlobalState } from '@vueuse/core';

export const useGlobalState = createGlobalState(() => {
  const app = reactive({
    buildTime: APP_BUILD_TIME,
    version: APP_VERSION,
  });
  const urls = {
    app_api: `https://gawuerfel.example.com/api`,
    opendata: `https://transport.opendata.ch/v1`,
  };
  return { urls, app };
});