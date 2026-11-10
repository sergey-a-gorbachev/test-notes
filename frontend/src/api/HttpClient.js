export class ApiError extends Error {
  constructor(message, {status = 0, violations = [], cause} = {}) {
    super(message, {cause});
    this.name = 'ApiError';
    this.status = status;
    this.violations = violations;
  }
}

export class HttpClient {
  constructor({baseUrl = '', fetchImpl = (...args) => globalThis.fetch(...args)} = {}) {
    this.baseUrl = baseUrl.replace(/\/$/, '');
    this.fetchImpl = fetchImpl;
  }

  async request(path, {body, headers, ...options} = {}) {
    let response;
    try {
      response = await this.fetchImpl(`${this.baseUrl}${path}`, {
        ...options,
        headers: {Accept: 'application/json', ...(body === undefined ? {} : {'Content-Type': 'application/json'}), ...headers},
        body: body === undefined ? undefined : JSON.stringify(body),
      });
    } catch (cause) {
      if (cause?.name === 'AbortError') throw cause;
      throw new ApiError('Не удалось подключиться к серверу. Попробуйте ещё раз.', {cause});
    }
    if (response.status === 204) return null;
    const data = await this.parseResponse(response);
    if (!response.ok) throw new ApiError(data?.error || `Сервер вернул ошибку ${response.status}.`, {
      status: response.status,
      violations: data?.violations || []
    });
    return data;
  }

  async parseResponse(response) {
    if (!(response.headers.get('content-type') || '').includes('application/json')) {
      throw new ApiError('Сервер вернул ответ в неизвестном формате.', {status: response.status});
    }
    try {
      return await response.json();
    } catch (cause) {
      throw new ApiError('Сервер вернул некорректный JSON.', {status: response.status, cause});
    }
  }
}
