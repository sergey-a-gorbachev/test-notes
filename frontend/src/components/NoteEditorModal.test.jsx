import {cleanup, fireEvent, render, screen, waitFor} from '@testing-library/react';
import {afterEach, describe, expect, it, vi} from 'vitest';
import {ApiError} from '../api/index.js';
import {NoteEditorModal} from './NoteEditorModal.jsx';

vi.mock('@gravity-ui/uikit', () => ({
  Modal: ({open, children}) => open ? <div>{children}</div> : null,
  Button: ({children, loading: _loading, ...props}) => <button {...props}>{children}</button>,
  TextInput: ({onUpdate, ...props}) => <input {...props} onChange={(event) => onUpdate(event.target.value)}/>,
  TextArea: ({onUpdate, ...props}) => <textarea {...props} onChange={(event) => onUpdate(event.target.value)}/>,
}));

afterEach(cleanup);

describe('Редактор заметки', () => {
  it('не использует новую версию без явного решения пользователя', async () => {
    const onSave = vi.fn()
      .mockRejectedValueOnce(new ApiError('conflict', {status: 409}))
      .mockResolvedValueOnce(undefined);
    const onGetLatest = vi.fn().mockResolvedValue({id: 1, title: 'server', content: 'server', version: 2});
    render(<NoteEditorModal note={{id: 1, title: 'old', content: 'old', version: 1}} saving={false}
                            onClose={() => {
                            }} onSave={onSave} onGetLatest={onGetLatest}/>);
    fireEvent.change(screen.getByLabelText(/Название/), {target: {value: 'my draft'}});
    fireEvent.change(screen.getByLabelText(/Текст заметки/), {target: {value: 'draft body'}});
    fireEvent.click(screen.getByRole('button', {name: 'Сохранить изменения'}));
    await waitFor(() => expect(onGetLatest).toHaveBeenCalledWith(1));
    expect(screen.getByLabelText(/Название/).value).toBe('my draft');
    expect(screen.getByLabelText(/Текст заметки/).value).toBe('draft body');
    expect(screen.getAllByText('server')).toHaveLength(2);
    expect(screen.getByRole('button', {name: 'Сохранить изменения'}).disabled).toBe(true);
    expect(onSave).toHaveBeenCalledTimes(1);

    fireEvent.click(screen.getByRole('button', {name: 'Объединить вручную'}));
    fireEvent.click(screen.getByRole('button', {name: 'Сохранить изменения'}));
    await waitFor(() => expect(onSave).toHaveBeenLastCalledWith({
      title: 'my draft',
      content: 'draft body',
      version: 2
    }));
  });

  it('загружает серверную редакцию только по явному выбору пользователя', async () => {
    const onSave = vi.fn().mockRejectedValueOnce(new ApiError('conflict', {status: 409}));
    const onGetLatest = vi.fn().mockResolvedValue({id: 1, title: 'server title', content: 'server body', version: 2});
    render(<NoteEditorModal note={{id: 1, title: 'old', content: 'old', version: 1}} saving={false}
                            onClose={() => {}} onSave={onSave} onGetLatest={onGetLatest}/>);

    fireEvent.click(screen.getByRole('button', {name: 'Сохранить изменения'}));
    await screen.findByText('server title');
    fireEvent.click(screen.getByRole('button', {name: 'Загрузить версию с сервера'}));

    expect(screen.getByLabelText(/Название/).value).toBe('server title');
    expect(screen.getByLabelText(/Текст заметки/).value).toBe('server body');
  });
});
