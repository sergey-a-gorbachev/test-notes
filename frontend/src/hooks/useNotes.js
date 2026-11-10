import {useCallback, useEffect, useState} from 'react';
import {notesApi} from '../api/index.js';

export function useNotes() {
  const [notes, setNotes] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const refresh = useCallback(async () => {
    try {
      setError('');
      setNotes(await notesApi.list());
    } catch (caught) {
      setError(caught.message);
    } finally {
      setLoading(false);
    }
  }, []);
  useEffect(() => {
    void refresh();
  }, [refresh]);
  const createNote = async (note) => {
    await notesApi.create(note);
    await refresh();
  };
  const updateNote = async (id, note) => {
    await notesApi.update(id, note);
    await refresh();
  };
  const deleteNote = async (id) => {
    await notesApi.remove(id);
    await refresh();
  };
  return {notes, loading, error, setError, refresh, createNote, updateNote, deleteNote};
}
