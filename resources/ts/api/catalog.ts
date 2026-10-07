import type { Branch, Dashboard, Product, StockRow } from '@/types';
import { http } from './http';

export type ProductInput = Omit<Product, 'id'>;
export type BranchInput = Omit<Branch, 'id'>;

export const dashboardApi = {
    get: async (): Promise<Dashboard> => (await http.get('/api/dashboard')).data.data,
};

export const productsApi = {
    list: async (search = ''): Promise<Product[]> => (await http.get('/api/products', { params: { search } })).data.data,
    create: async (input: ProductInput): Promise<Product> => (await http.post('/api/products', input)).data.data,
    update: async (id: number, input: ProductInput): Promise<Product> => (await http.put(`/api/products/${id}`, input)).data.data,
    remove: (id: number) => http.delete(`/api/products/${id}`),
};

export const branchesApi = {
    list: async (): Promise<Branch[]> => (await http.get('/api/branches')).data.data,
    create: async (input: BranchInput): Promise<Branch> => (await http.post('/api/branches', input)).data.data,
    stock: async (id: number): Promise<StockRow[]> => (await http.get(`/api/branches/${id}/stock`)).data.data,
};
