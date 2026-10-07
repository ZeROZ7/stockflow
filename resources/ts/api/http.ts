import axios, { isAxiosError } from 'axios';
import type { ValidationErrors } from '@/types';

/**
 * Single Axios instance for the whole SPA.
 *
 * Auth is cookie based (Laravel Sanctum SPA mode): the browser sends the
 * HttpOnly session cookie automatically and Axios copies the XSRF-TOKEN
 * cookie into the X-XSRF-TOKEN header. No token is ever stored in JS.
 */
export const http = axios.create({
    withCredentials: true,
    withXSRFToken: true,
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

let onSessionExpired: () => void = () => {};

/** Registered once in app.ts so this module does not depend on the router or stores. */
export function setSessionExpiredHandler(handler: () => void): void {
    onSessionExpired = handler;
}

http.interceptors.response.use(
    (response) => response,
    (error: unknown) => {
        const status = isAxiosError(error) ? error.response?.status : undefined;

        // 401 = not authenticated, 419 = CSRF token / session expired.
        if (status === 401 || status === 419) {
            onSessionExpired();
        }

        return Promise.reject(error);
    },
);

export function validationErrors(error: unknown): ValidationErrors {
    if (isAxiosError(error) && error.response?.status === 422) {
        return error.response.data?.errors ?? {};
    }

    return {};
}

export function errorMessage(error: unknown, fallback = 'Something went wrong. Please try again.'): string {
    if (isAxiosError(error)) {
        return error.response?.data?.message ?? fallback;
    }

    return fallback;
}
