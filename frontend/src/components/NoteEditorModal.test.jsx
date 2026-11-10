import {fireEvent, render, screen, waitFor} from '@testing-library/react';
import {describe, expect, it, vi} from 'vitest';
import {ApiError} from '../api/HttpClient.js';
import {NoteEditorModal} from './NoteEditorModal.jsx';

vi.mock('@gravity-ui/uikit', () => ({
  Modal: ({open, children}) => open ? <div>{children}</div> : null,
  Button: ({children, loading: _loading, ...props}) => <button {...props}>{children}</button>,
  TextInput: ({onUpdate, ...props}) => <input {...props} onChange={(event) => onUpdate(event.target.value)}/>,
  TextArea: ({onUpdate, ...props}) => <textarea {...props} onChange={(event) => onUpdate(event.target.value)}/>,
}));

describe('Редактор заметки', () => {
  it('сохраняет введённый текст и получает актуальную версию после конфликта', async () => {
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

    fireEvent.click(screen.getByRole('button', {name: 'Сохранить изменения'}));
    await waitFor(() => expect(onSave).toHaveBeenLastCalledWith({
      title: 'my draft',
      content: 'draft body',
      version: 2
    }));
  });
});
