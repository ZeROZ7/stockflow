import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

declare module 'vue-router' {
    interface RouteMeta {
        title?: string;
        guest?: boolean;
    }
}

export const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/login', name: 'login', component: () => import('@/views/LoginView.vue'), meta: { guest: true, title: 'Sign in' } },
        { path: '/', name: 'dashboard', component: () => import('@/views/DashboardView.vue'), meta: { title: 'Dashboard' } },
        { path: '/products', name: 'products', component: () => import('@/views/ProductsView.vue'), meta: { title: 'Products' } },
        { path: '/branches', name: 'branches', component: () => import('@/views/BranchesView.vue'), meta: { title: 'Branches & stock' } },
        { path: '/purchase-orders', name: 'purchase-orders', component: () => import('@/views/PurchaseOrdersView.vue'), meta: { title: 'Purchase orders' } },
        { path: '/purchase-orders/new', name: 'purchase-orders.create', component: () => import('@/views/PurchaseOrderCreateView.vue'), meta: { title: 'New purchase order' } },
        { path: '/purchase-orders/:id(\\d+)', name: 'purchase-orders.show', component: () => import('@/views/PurchaseOrderDetailView.vue'), props: (route) => ({ id: Number(route.params.id) }), meta: { title: 'Purchase order' } },
        { path: '/:pathMatch(.*)*', name: 'not-found', component: () => import('@/views/NotFoundView.vue'), meta: { title: 'Not found' } },
    ],
});

// Authentication guard: the session cookie is the source of truth, we only ask Laravel once.
router.beforeEach(async (to) => {
    const auth = useAuthStore();
    await auth.ensureLoaded();

    if (to.meta.guest && auth.isAuthenticated) {
        return { name: 'dashboard' };
    }

    if (!to.meta.guest && !auth.isAuthenticated) {
        return { name: 'login', query: { redirect: to.fullPath } };
    }
});

router.afterEach((to) => {
    document.title = to.meta.title ? `${to.meta.title} · StockFlow` : 'StockFlow';
});
