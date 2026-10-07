import '../css/app.css';
import React from 'react'; import {createRoot} from 'react-dom/client'; import {createInertiaApp} from '@inertiajs/react'; import {resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers';
import 'antd/dist/reset.css';
import { ConfigProvider } from 'antd';
import idID from 'antd/locale/id_ID';
createInertiaApp({resolve:name=>resolvePageComponent(`./Pages/${name}.jsx`,import.meta.glob('./Pages/**/*.jsx')),setup({el,App,props}){createRoot(el).render(<ConfigProvider locale={idID} theme={{token:{colorPrimary:'#3563e9',borderRadius:9,fontFamily:'Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',colorText:'#17243b',colorBgLayout:'#f4f6fb'}}}><App {...props}/></ConfigProvider>);},progress:{color:'#3563e9'}});
