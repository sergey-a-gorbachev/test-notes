import {Button, Card, Icon} from '@gravity-ui/uikit';
import {Pencil, TrashBin} from '@gravity-ui/icons';
import {formatDateTime} from '../utils/date.js';

export function NoteCard({note, colorIndex, onEdit, onDelete}) {
  return <Card view="outlined" className="note-card">
    <div className="note-card-header"><span className={`note-category color-${colorIndex % 4}`}>ЗАМЕТКА</span>
      <div className="card-actions"><Button view="flat-secondary" size="m" title="Редактировать"
                                            aria-label={`Редактировать ${note.title}`}
                                            onClick={() => onEdit(note)}><Icon data={Pencil}
                                                                               size={16}/></Button><Button
        view="flat-secondary" size="m" title="Удалить" aria-label={`Удалить ${note.title}`}
        onClick={() => onDelete(note)}><Icon data={TrashBin} size={16}/></Button></div>
    </div>
    <button className="note-preview" type="button" onClick={() => onEdit(note)}><span
      className="note-title">{note.title}</span><span
      className="note-excerpt">{note.content || 'Нет дополнительного текста'}</span></button>
    <div className="note-meta"><span className="meta-dot"/> Изменено {formatDateTime(note.updated_at)}</div>
  </Card>;
}
