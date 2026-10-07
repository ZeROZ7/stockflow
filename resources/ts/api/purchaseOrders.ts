import type { Paginated, PurchaseOrder, PurchaseOrderStatus } from '@/types';
import { http } from './http';

export interface PurchaseOrderInput {
    branch_id: number | null;
    supplier: string;
    notes: string;
    lines: { product_id: number | null; quantity: number; unit_cost: number }[];
}

export interface PurchaseOrderFilters {
    status?: PurchaseOrderStatus;
    page?: number;
}

export const purchaseOrdersApi = {
    list: async (filters: PurchaseOrderFilters = {}): Promise<Paginated<PurchaseOrder>> =>
        (await http.get('/api/purchase-orders', { params: filters })).data,

    find: async (id: number): Promise<PurchaseOrder> => (await http.get(`/api/purchase-orders/${id}`)).data.data,

    create: async (input: PurchaseOrderInput): Promise<PurchaseOrder> => (await http.post('/api/purchase-orders', input)).data.data,

    approve: async (id: number): Promise<PurchaseOrder> => (await http.post(`/api/purchase-orders/${id}/approve`)).data.data,

    receive: async (id: number): Promise<PurchaseOrder> => (await http.post(`/api/purchase-orders/${id}/receive`)).data.data,
};
