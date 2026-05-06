import '../css/app.css'
import { createApp } from 'vue'
import App from './components/App.vue'
import type { RadarConfig } from './types/radar'

declare global {
  interface Window {
    radar?: RadarConfig
  }
}

const radarConfig = window.radar

if (!radarConfig) {
  throw new Error('Radar config is missing.')
}

createApp(App, {
  radarConfig,
}).mount('#radar')
