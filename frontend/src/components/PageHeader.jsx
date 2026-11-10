import {Button, Icon} from '@gravity-ui/uikit';
import {Plus} from '@gravity-ui/icons';

export function PageHeader({onCreate}) {
  return <div className="page-heading">
    <div>
      <div className="eyebrow"><span className="eyebrow-dot"/> ЛИЧНОЕ ПРОСТРАНСТВО</div>
      <h1>Мои заметки<span className="heading-dot">.</span></h1><p className="subtitle">Сохраняйте мысли, идеи и
      всё важное в одном месте.</p></div>
    <Button view="action" size="xl" onClick={onCreate} className="create-button"><Icon data={Plus} size={18}/> Новая
      заметка</Button></div>;
}
