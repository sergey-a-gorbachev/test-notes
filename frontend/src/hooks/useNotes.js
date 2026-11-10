import {useCallback, useEffect, useRef, useState} from 'react';
import {notesApi} from '../api/index.js';

export function useNotes() {
  const [notes, setNotes] = useState([]);
  const [initialLoading, setInitialLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState('');
  const mounted = useRef(false);
  const active = useRef({id: 0, controller: null});
  const refresh = useCallback(async ({initial = false} = {}) => {
    active.current.controller?.abort();
    const controller = new AbortController();
    const id = active.current.id + 1;
    active.current = {id, controller};
    if (mounted.current) {
      setError('');
      initial ? setInitialLoading(true) : setRefreshing(true);
    }
    try {
      const result = await notesApi.list({signal: controller.signal});
      if (mounted.current && active.current.id === id) setNotes(result);
      return result;
    } catch (caught) {
      if (caught?.name === 'AbortError') return null;
      if (mounted.current && active.current.id === id) setError(caught.message);
      throw caught;
    } finally {
      if (mounted.current && active.current.id === id) {
        setInitialLoading(false);
        setRefreshing(false);
      }
    }
  }, []);
  useEffect(() => {
    mounted.current = true;
    void refresh({initial: true}).catch(() => {
    });
    return () => {
      mounted.current = false;
      active.current.controller?.abort();
    };
  }, [refresh]);
  const synchronize = async () => {
    try {
      await refresh();
      return null;
    } catch (caught) {
      return caught;
    }
  };
  const createNote = async (note) => {
    const created = await notesApi.create(note);
    if (mounted.current) setNotes((current) => [created, ...current.filter(({id}) => id !== created.id)]);
    return {note: created, syncError: await synchronize()};
  };
  const updateNote = async (id, note) => {
    const updated = await notesApi.update(id, note);
    if (mounted.current) setNotes((current) => [updated, ...current.filter((item) => item.id !== id)]);
    return {note: updated, syncError: await synchronize()};
  };
  const deleteNote = async (id) => {
    await notesApi.remove(id);
    if (mounted.current) setNotes((current) => current.filter((item) => item.id !== id));
    return {syncError: await synchronize()};
  };
  const getNote = useCallback((id, options) => notesApi.get(id, options), []);
  return {notes, initialLoading, refreshing, error, setError, refresh, getNote, createNote, updateNote, deleteNote};
}
