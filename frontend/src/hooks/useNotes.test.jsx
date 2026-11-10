import {act, renderHook, waitFor} from '@testing-library/react';
import {afterEach, describe, expect, it, vi} from 'vitest';
import {notesApi} from '../api/index.js';
import {useNotes} from './useNotes.js';

const deferred = () => {
  let resolve;
  const promise = new Promise((done) => {
    resolve = done;
  });
  return {promise, resolve};
};

describe('Загрузка заметок', () => {
  afterEach(() => vi.restoreAllMocks());

  it('показывает ошибку начальной загрузки вместо пустого списка', async () => {
    vi.spyOn(notesApi, 'list').mockRejectedValue(new Error('offline'));
    const {result} = renderHook(() => useNotes());
    await waitFor(() => expect(result.current.initialLoading).toBe(false));
    expect(result.current.error).toBe('offline');
  });

  it('сохраняет созданную заметку при ошибке последующей синхронизации', async () => {
    vi.spyOn(notesApi, 'list').mockResolvedValueOnce([]).mockRejectedValueOnce(new Error('sync failed'));
    vi.spyOn(notesApi, 'create').mockResolvedValue({id: 1, title: 'new'});
    const {result} = renderHook(() => useNotes());
    await waitFor(() => expect(result.current.initialLoading).toBe(false));
    let mutation;
    await act(async () => {
      mutation = await result.current.createNote({title: 'new'});
    });
    expect(result.current.notes).toHaveLength(1);
    expect(mutation.syncError.message).toBe('sync failed');
  });

  it('не позволяет устаревшему ответу перезаписать новые данные', async () => {
    const first = deferred();
    const second = deferred();
    vi.spyOn(notesApi, 'list').mockReturnValueOnce(first.promise).mockReturnValueOnce(second.promise);
    const {result} = renderHook(() => useNotes());
    let refresh;
    await act(async () => {
      refresh = result.current.refresh();
    });
    await act(async () => {
      second.resolve([{id: 2, title: 'new'}]);
      await refresh;
    });
    await act(async () => {
      first.resolve([{id: 1, title: 'old'}]);
      await first.promise;
    });
    expect(result.current.notes[0].id).toBe(2);
  });
});
