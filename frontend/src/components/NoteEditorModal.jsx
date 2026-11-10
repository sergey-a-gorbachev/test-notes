import {useEffect, useState} from 'react';
import {Button, Modal, TextArea, TextInput} from '@gravity-ui/uikit';

const EMPTY_FORM = {title: '', content: ''};

export function NoteEditorModal({note, saving, onClose, onSave, onGetLatest}) {
  const [form, setForm] = useState(EMPTY_FORM);
  const [error, setError] = useState('');
  const [fieldErrors, setFieldErrors] = useState({});
  const [version, setVersion] = useState(null);
  useEffect(() => {
    setForm(note?.id ? {title: note.title, content: note.content} : EMPTY_FORM);
    setVersion(note?.version ?? null);
    setError('');
    setFieldErrors({});
  }, [note]);

  const submit = async (event) => {
    event.preventDefault();
    const title = form.title.trim();
    if (!title) return setError('Укажите название заметки.');
    if (title.length > 160 || form.content.length > 10000) return setError('Название — до 160 символов, текст — до 10 000.');
    try {
      setError('');
      await onSave({title, content: form.content, ...(note?.id ? {version} : {})});
    } catch (caught) {
      setFieldErrors(Object.fromEntries((caught.violations || []).map(({field, message}) => [field, message])));
      if (caught.status === 409 && note?.id) {
        setError('Заметка уже изменена. Ваш текст сохранён в форме; загружаем актуальную версию для повторной попытки.');
        try {
          const latest = await onGetLatest(note.id);
          setVersion(latest.version);
        } catch {
          setError('Заметка уже изменена. Ваш текст сохранён, но получить актуальную версию не удалось.');
        }
      } else {
        setError(caught.message);
      }
    }
  };

  const isEditing = Boolean(note?.id);
  return <Modal open={note !== null} onOpenChange={(open) => !open && !saving && onClose()}
                aria-label={isEditing ? 'Редактировать заметку' : 'Новая заметка'}>
    <form className="note-dialog" onSubmit={submit}>
      <div className="dialog-kicker">{isEditing ? 'РЕДАКТИРОВАНИЕ' : 'НОВАЯ ЗАМЕТКА'}</div>
      <h2>{isEditing ? 'Редактировать заметку' : 'Запишите свою мысль'}</h2><p
      className="dialog-subtitle">{isEditing ? 'Обновите содержание вашей заметки.' : 'Большие идеи начинаются с маленьких заметок.'}</p>
      <label htmlFor="note-title">Название <span className="required">*</span></label><TextInput id="note-title"
                                                                                                 size="l"
                                                                                                 placeholder="О чём эта заметка?"
                                                                                                 value={form.title}
                                                                                                 onUpdate={(title) => setForm((current) => ({
                                                                                                   ...current,
                                                                                                   title
                                                                                                 }))}
                                                                                                 disabled={saving}
                                                                                                 maxLength={160}/>
      {fieldErrors.title && <div className="field-error" role="alert">{fieldErrors.title}</div>}
      <div className="field-heading"><label htmlFor="note-content">Текст
        заметки</label><span>{form.content.length} / 10000</span></div>
      <TextArea id="note-content" size="l" rows={7} placeholder="Начните писать здесь..." value={form.content}
                onUpdate={(content) => setForm((current) => ({...current, content}))} disabled={saving}
                maxLength={10000}/>{fieldErrors.content && <div className="field-error" role="alert">{fieldErrors.content}</div>}{fieldErrors.version && <div className="field-error" role="alert">{fieldErrors.version}</div>}{error && <div className="form-error" role="alert">{error}</div>}
      <div className="dialog-actions"><Button type="button" size="l" view="flat" onClick={onClose}
                                              disabled={saving}>Отмена</Button><Button type="submit" size="l"
                                                                                       view="action"
                                                                                       loading={saving} disabled={saving}>{isEditing ? 'Сохранить изменения' : 'Создать заметку'}</Button>
      </div>
    </form>
  </Modal>;
}
