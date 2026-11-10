import {Alert, Button} from '@gravity-ui/uikit';

export function ErrorBanner({message, onClose, onRetry}) {
  if (!message) return null;
  return <div className="error-banner"><Alert theme="danger" title="Ошибка" message={message}
                                              onClose={onClose}/><Button size="m" view="outlined"
                                                                         onClick={onRetry}>Повторить</Button></div>;
}
