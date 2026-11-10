import {useMemo, useState} from 'react';
import {AppHeader} from './components/AppHeader.jsx';
import {DeleteNoteModal} from './components/DeleteNoteModal.jsx';
import {ErrorBanner} from './components/ErrorBanner.jsx';
import {NoteEditorModal} from './components/NoteEditorModal.jsx';
import {NotesList} from './components/NotesList.jsx';
import {NotesToolbar} from './components/NotesToolbar.jsx';
import {PageHeader} from './components/PageHeader.jsx';
import {Toast} from './components/Toast.jsx';
import {useNotes} from './hooks/useNotes.js';

export default function App() {
  const {
    notes,
    initialLoading,
    refreshing,
    error,
    setError,
    refresh,
    getNote,
    createNote,
    updateNote,
    deleteNote
  } = useNotes();
  const [search, setSearch] = useState('');
  const [editing, setEditing] = useState(null);
  const [deleting, setDeleting] = useState(null);
  const [deleteError, setDeleteError] = useState('');
  const [pending, setPending] = useState(false);
  const [toast, setToast] = useState('');

  const shownNotes = useMemo(() => {
    const query = search.trim().toLocaleLowerCase('ru-RU');
    return query ? notes.filter(({
                                   title,
                                   content
                                 }) => `${title}\n${content}`.toLocaleLowerCase('ru-RU').includes(query)) : notes;
  }, [notes, search]);

  const saveNote = async (payload) => {
    setPending(true);
    try {
      let result;
      if (editing.id) {
        result = await updateNote(editing.id, payload);
        setToast(result.syncError ? 'Изменения сохранены; обновить список с сервера не удалось' : 'Изменения сохранены');
      } else {
        result = await createNote(payload);
        setToast(result.syncError ? 'Заметка создана; обновить список с сервера не удалось' : 'Заметка создана');
      }
      setEditing(null);
    } finally {
      setPending(false);
    }
  };

  const removeNote = async () => {
    setPending(true);
    try {
      const result = await deleteNote(deleting.id, deleting.version);
      setDeleting(null);
      setDeleteError('');
      setToast(result.syncError ? 'Заметка удалена; обновить список с сервера не удалось' : 'Заметка удалена');
    } catch (caught) {
      if (caught.status === 409) {
        setDeleteError('Заметка изменилась после открытия и не была удалена. Проверьте актуальные данные и повторите удаление.');
        try {
          setDeleting(await getNote(deleting.id));
        } catch (reloadError) {
          setDeleteError(`${caught.message} Не удалось получить актуальную версию: ${reloadError.message}`);
        }
      } else {
        setDeleting(null);
        setError(caught.message);
      }
    } finally {
      setPending(false);
    }
  };

  const openCreate = () => setEditing({id: null});

  return (
    <div className="app">
      <AppHeader/>
      <main className="container">
        <PageHeader onCreate={openCreate}/>
        <NotesToolbar count={notes.length} search={search} onSearchChange={setSearch}/>
        {!initialLoading && (
          <ErrorBanner message={error} onClose={() => setError('')}
                       onRetry={() => void refresh().catch(() => {
                       })}/>
        )}
        <NotesList notes={shownNotes} initialLoading={initialLoading} refreshing={refreshing} loadError={error}
                   search={search} onCreate={openCreate}
                   onEdit={setEditing} onDelete={(note) => { setDeleteError(''); setDeleting(note); }}/>
      </main>
      <NoteEditorModal note={editing} saving={pending} onClose={() => setEditing(null)} onSave={saveNote}
                       onGetLatest={getNote}/>
      <DeleteNoteModal note={deleting} deleting={pending} error={deleteError}
                       onClose={() => { setDeleting(null); setDeleteError(''); }}
                       onConfirm={removeNote}/>
      <Toast message={toast} onClose={() => setToast('')}/>
    </div>
  );
}
