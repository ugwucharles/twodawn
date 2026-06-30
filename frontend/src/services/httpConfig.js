import axios from 'axios'

export const API_BASE_URL =
  import.meta.env.VITE_API_URL ||
  import.meta.env.VITE_BACKEND_URL ||
  'https://api.twodawn.com.ng'

axios.defaults.baseURL = API_BASE_URL
axios.defaults.timeout = 15000
axios.defaults.headers.common.Accept = 'application/json'
