import { createPinia } from 'pinia';
import { createApp } from 'vue';
import { setSessionExpiredHandler } from '@/api/http';
import App from '@/App.vue';
import { router } from '@/router';
import { useAuthStore } from '@/stores/auth';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia).use(router);

// When the session expires mid-use (401/419), drop the user and go to the login screen.
setSessionExpiredHandler(() => {
    const auth = useAuthStore(pinia);

    if (auth.isAuthenticated) {
        auth.clear();
        router.push({ name: 'login', query: { redirect: router.currentRoute.value.fullPath } });
    }
});

router.isReady().then(() => app.mount('#app'));
