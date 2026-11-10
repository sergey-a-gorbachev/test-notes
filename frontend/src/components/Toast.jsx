import {Button, Icon} from '@gravity-ui/uikit';
import {Xmark} from '@gravity-ui/icons';

export function Toast({message, onClose}) {
  if (!message) return null;
  return <div className="toast" role="status"><span className="toast-check">✓</span>{message}<Button view="flat"
                                                                                                     size="s"
                                                                                                     aria-label="Закрыть уведомление"
                                                                                                     onClick={onClose}><Icon
    data={Xmark} size={14}/></Button></div>;
}
