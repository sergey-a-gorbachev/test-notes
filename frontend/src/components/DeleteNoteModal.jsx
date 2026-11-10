import {Button, Icon, Modal} from '@gravity-ui/uikit';
import {TrashBin} from '@gravity-ui/icons';

export function DeleteNoteModal({note, deleting, error, onClose, onConfirm}) {
  return <Modal open={note !== null} onOpenChange={(open) => !open && !deleting && onClose()}
                aria-label="Подтверждение удаления">
    <div className="note-dialog delete-dialog">
      <div className="delete-icon"><Icon data={TrashBin} size={23}/></div>
      <h2>Удалить заметку?</h2><p className="dialog-subtitle">«{note?.title}» будет удалена без возможности
      восстановления.</p>
      {error && <div className="form-error" role="alert">{error}</div>}
      <div className="dialog-actions"><Button size="l" view="flat" onClick={onClose}
                                              disabled={deleting}>Отмена</Button><Button size="l"
                                                                                         view="flat-danger"
                                                                                         loading={deleting}
                                                                                         onClick={onConfirm}>Удалить</Button>
      </div>
    </div>
  </Modal>;
}
