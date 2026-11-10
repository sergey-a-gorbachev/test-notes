import {HttpClient} from './HttpClient.js';
import {NotesApi} from './NotesApi.js';

export const notesApi = new NotesApi(new HttpClient({baseUrl: '/api'}));
export {ApiError, HttpClient} from './HttpClient.js';
export {NotesApi} from './NotesApi.js';
