import {Button, Icon, TextInput} from '@gravity-ui/uikit';
import {Magnifier, Xmark} from '@gravity-ui/icons';

export function NotesToolbar({count, search, onSearchChange}) {
  return <div className="toolbar">
    <div className="tab"><span className="tab-indicator"/> Все заметки <span className="count">{count}</span></div>
    <div className="search-box"><TextInput size="l" placeholder="Поиск по заметкам..."
                                           aria-label="Поиск по заметкам" value={search} onUpdate={onSearchChange}
                                           startContent={<Icon data={Magnifier} size={18}/>} endContent={search &&
      <Button view="flat" size="s" onClick={() => onSearchChange('')} title="Очистить поиск"><Icon data={Xmark}
                                                                                                   size={14}/></Button>}/>
    </div>
  </div>;
}
