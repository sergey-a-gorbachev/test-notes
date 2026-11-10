import {Button, Icon, Spin} from '@gravity-ui/uikit';
import {Plus} from '@gravity-ui/icons';
import {NoteCard} from './NoteCard.jsx';

export function NotesList({notes, loading, search, onCreate, onEdit, onDelete}) {
  if (loading) return <div className="empty-panel"><Spin size="l"/><span>Загружаем заметки...</span></div>;
  if (!notes.length) return <div className="empty-panel">
    <div className="empty-art"><span>✦</span>
      <div className="empty-sheet"><i/><i/><i/></div>
    </div>
    <h2>{search ? 'Ничего не найдено' : 'Здесь пока пусто'}</h2>
    <p>{search ? 'Попробуйте изменить поисковый запрос.' : 'Начните с первой заметки — пусть идеи не теряются.'}</p>{!search &&
    <Button size="l" view="outlined" onClick={onCreate}><Icon data={Plus} size={16}/> Создать заметку</Button>}
  </div>;
  return <div className="notes-grid">{notes.map((note, index) => <NoteCard key={note.id} note={note}
                                                                           colorIndex={index} onEdit={onEdit}
                                                                           onDelete={onDelete}/>)}</div>;
}
