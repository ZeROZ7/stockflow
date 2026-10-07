import type { User } from '@/types';
import { http } from './http';

export interface Credentials {
    email: string;
    password: string;
}

export const authApi = {
    /** Sets the XSRF-TOKEN cookie; must be called before the first POST. */
    csrfCookie: () => http.get('/sanctum/csrf-cookie'),

    login: async (credentials: Credentials): Promise<User> => (await http.post('/auth/login', credentials)).data.data,

    logout: () => http.post('/auth/logout'),

    me: async (): Promise<User> => (await http.get('/api/user')).data.data,
};
