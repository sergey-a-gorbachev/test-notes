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
  const {notes, loading, error, setError, refresh, createNote, updateNote, deleteNote} = useNotes();
  const [search, setSearch] = useState('');
  const [editing, setEditing] = useState(null);
  const [deleting, setDeleting] = useState(null);
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
      if (editing.id) {
        await updateNote(editing.id, {...payload, version: editing.version});
        setToast('Изменения сохранены');
      } else {
        await createNote(payload);
        setToast('Заметка создана');
      }
      setEditing(null);
    } finally {
      setPending(false);
    }
  };

  const removeNote = async () => {
    setPending(true);
    try {
      await deleteNote(deleting.id);
      setDeleting(null);
      setToast('Заметка удалена');
    } catch (caught) {
      setDeleting(null);
      setError(caught.message);
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
        <ErrorBanner message={error} onClose={() => setError('')} onRetry={refresh}/>
        <NotesList notes={shownNotes} loading={loading} search={search} onCreate={openCreate}
                   onEdit={setEditing} onDelete={setDeleting}/>
      </main>
      <NoteEditorModal note={editing} saving={pending} onClose={() => setEditing(null)} onSave={saveNote}/>
      <DeleteNoteModal note={deleting} deleting={pending} onClose={() => setDeleting(null)}
                       onConfirm={removeNote}/>
      <Toast message={toast} onClose={() => setToast('')}/>
    </div>
  );
}
