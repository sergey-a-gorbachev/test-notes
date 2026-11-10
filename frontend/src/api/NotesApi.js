export class NotesApi {
  constructor(client) {
    this.client = client;
    this.endpoint = '/notes';
  }

  async list() {
    return (await this.client.request(this.endpoint)).items;
  }

  get(id) {
    return this.client.request(`${this.endpoint}/${id}`);
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
