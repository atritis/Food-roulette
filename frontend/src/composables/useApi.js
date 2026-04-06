const BASE_URL = import.meta.env.VITE_API_BASE_URL || `${import.meta.env.BASE_URL}api`

export function useApi() {
  async function request(url, options = {}) {
    const res = await fetch(`${BASE_URL}${url}`, {
      ...options,
      headers: {
        ...(!options.isFormData && { 'Content-Type': 'application/json' }),
        ...options.headers,
      },
    })

    if (!res.ok) {
      const error = await res.json().catch(() => ({ error: 'Request failed' }))
      throw new Error(error.error || `HTTP ${res.status}`)
    }

    return res.json()
  }

  return {
    get: (url, options = {}) => request(url, { method: 'GET', ...options }),

    post: (url, data, options = {}) =>
      request(url, { method: 'POST', body: JSON.stringify(data), ...options }),

    put: (url, data, options = {}) =>
      request(url, { method: 'PUT', body: JSON.stringify(data), ...options }),

    del: (url, options = {}) => request(url, { method: 'DELETE', ...options }),

    postForm: (url, formData, options = {}) =>
      request(url, { method: 'POST', body: formData, isFormData: true, ...options }),
  }
}
