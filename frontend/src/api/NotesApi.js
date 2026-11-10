export class NotesApi {
  constructor(client) {
    this.client = client;
    this.endpoint = '/notes';
  }

  async list(options = {}) {
    return (await this.client.request(this.endpoint, options)).items;
  }

  get(id, options = {}) {
    return this.client.request(`${this.endpoint}/${id}`, options);
  }

  create(note) {
    return this.client.request(this.endpoint, {method: 'POST', body: note});
  }

  update(id, note) {
    return this.client.request(`${this.endpoint}/${id}`, {method: 'PUT', body: note});
  }

  remove(id) {
    return this.client.request(`${this.endpoint}/${id}`, {method: 'DELETE'});
  }
}
