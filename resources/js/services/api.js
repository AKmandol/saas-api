const API_URL = '/api/v1';

async function request(endpoint, options = {}) {
    const token = localStorage.getItem('token');

    const headers = {
        Accept: 'application/json',
        ...options.headers,
    };

    if (token) {
        headers.Authorization = `Bearer ${token}`;
    }

    if (
        options.body &&
        !(options.body instanceof FormData)
    ) {
        headers['Content-Type'] = 'application/json';
    }

    const response = await fetch(
        `${API_URL}${endpoint}`,
        {
            ...options,
            headers,
        }
    );

    let data = null;

    try {
        data = await response.json();
    } catch {
        data = null;
    }

    if (response.status === 401) {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
    }

    if (!response.ok) {
        throw {
            status: response.status,
            data,
        };
    }

    return data;
}

export default {

    get(endpoint) {
        return request(endpoint);
    },

    post(endpoint, body = null) {
        return request(endpoint, {
            method: 'POST',
            body: body
                ? JSON.stringify(body)
                : undefined,
        });
    },

    put(endpoint, body = null) {
        return request(endpoint, {
            method: 'PUT',
            body: body
                ? JSON.stringify(body)
                : undefined,
        });
    },

    patch(endpoint, body = null) {
        return request(endpoint, {
            method: 'PATCH',
            body: body
                ? JSON.stringify(body)
                : undefined,
        });
    },

    delete(endpoint) {
        return request(endpoint, {
            method: 'DELETE',
        });
    },
};
