import { createVuetify } from 'vuetify'
import { aliases, mdi } from 'vuetify/iconsets/mdi'
import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'

const light = {
  dark: false,
  colors: {
    primary:            '#1a73e8',
    'primary-darken-1': '#1557b0',
    secondary:          '#34a853',
    error:              '#ea4335',
    warning:            '#fbbc04',
    info:               '#4285f4',
    success:            '#34a853',
    background:         '#f4f6f9',
    surface:            '#ffffff',
    'on-primary':       '#ffffff',
    'on-surface':       '#1e2a3a',
    'on-background':    '#1e2a3a',
  },
  variables: {
    'border-color':           '#1e2a3a',
    'border-opacity':         0.1,
    'table-header-color':     '#f8f9fa',
    'hover-opacity':          0.04,
    'high-emphasis-opacity':  0.9,
    'medium-emphasis-opacity': 0.65,
  },
}

const dark = {
  dark: true,
  colors: {
    primary:            '#4a90d9',
    'primary-darken-1': '#3a78c0',
    secondary:          '#34a853',
    error:              '#ff5252',
    warning:            '#fbbc04',
    info:               '#64b5f6',
    success:            '#69f0ae',
    background:         '#111827',
    surface:            '#1f2937',
    'on-primary':       '#ffffff',
    'on-surface':       '#f3f4f6',
    'on-background':    '#f3f4f6',
  },
  variables: {
    'border-color':           '#f3f4f6',
    'border-opacity':         0.1,
    'table-header-color':     '#1a2535',
    'hover-opacity':          0.06,
    'high-emphasis-opacity':  0.9,
    'medium-emphasis-opacity': 0.65,
  },
}

export default createVuetify({
  theme: {
    defaultTheme: 'light',
    themes: { light, dark },
  },
  icons: {
    defaultSet: 'mdi',
    aliases,
    sets: { mdi },
  },
  defaults: {
    VBtn: {
      color: 'primary',
      style: 'text-transform: none; font-weight: 500;',
    },
    VTextField: {
      variant: 'outlined',
      density: 'comfortable',
      color: 'primary',
      hideDetails: 'auto',
    },
    VSelect: {
      variant: 'outlined',
      density: 'comfortable',
      color: 'primary',
      hideDetails: 'auto',
    },
    VTextarea: {
      variant: 'outlined',
      density: 'comfortable',
      color: 'primary',
      hideDetails: 'auto',
    },
    VAutocomplete: {
      variant: 'outlined',
      density: 'comfortable',
      color: 'primary',
      hideDetails: 'auto',
    },
    VCheckbox: {
      color: 'primary',
      density: 'comfortable',
      hideDetails: 'auto',
    },
    VSwitch: {
      color: 'primary',
      inset: true,
      hideDetails: 'auto',
    },
    VChip: { elevation: 0 },
    VCard: { rounded: 'lg' },
    VDialog: { scrollable: true },
    VTooltip: { location: 'top' },
    VPagination: {
      density: 'comfortable',
      showFirstLastPage: true,
      variant: 'tonal',
      color: 'primary',
    },
    VSnackbar: { color: 'primary', timeout: 3500 },
  },
})
