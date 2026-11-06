import { reactive } from "vue";
import { createGlobalState } from '@vueuse/core';

export const useGlobalState = createGlobalState(() => {
  const _buildURLs = () => {
    const protocol = location.protocol;
    let app_api_url = `${protocol}//gawuerfel.birch.li/api`;

    return {
      app_api: app_api_url,
    }
  };
  const app = reactive({
    buildTime: APP_BUILD_TIME,
    version: APP_VERSION,
  });
  const urls = _buildURLs();
  return { urls, app };
});