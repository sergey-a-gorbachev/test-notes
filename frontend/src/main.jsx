import React from 'react';
import {createRoot} from 'react-dom/client';
import {Provider} from '@gravity-ui/uikit';
import '@gravity-ui/uikit/styles/fonts.css';
import '@gravity-ui/uikit/styles/styles.css';
import App from './App.jsx';
import './styles.css';

createRoot(document.getElementById('root')).render(
  <React.StrictMode>
    <Provider theme="light" lang="ru">
      <App/>
    </Provider>
  </React.StrictMode>,
);
