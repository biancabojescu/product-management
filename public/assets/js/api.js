import { ApiError } from './apiError.js';

export async function apiFetch(endpoint, { query, ...options } = {}) {
    const url = new URL(`/api/${endpoint.replace(/^\/+/, '')}`, window.location.origin);

    if (query) {
        url.search = new URLSearchParams(query).toString();
    }

    const headers = new Headers(options.headers);
    headers.set('Accept', 'application/json');
    headers.set('X-Requested-With', 'XMLHttpRequest');

    let response;

    try {
        response = await fetch(url, { ...options, headers, credentials: 'same-origin' });
    } catch (error) {
        if (error.name === 'AbortError') throw error;
        throw new ApiError('Could not connect to the server. Check your connection and try again.');
    }

    let result;

    try {
        result = await response.json();
    } catch (error) {
        if (error.name === 'AbortError') throw error;
        throw new ApiError('The server returned an invalid response. Please try again.', { status: response.status });
    }

    if (!response.ok || result?.success !== true) {
        throw new ApiError(result?.error?.message || 'The request failed. Please try again.', {
            status: response.status,
            code: result?.error?.code,
            details: result?.error?.details,
        });
    }

    return result.data;
}
