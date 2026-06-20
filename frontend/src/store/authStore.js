import { create } from 'zustand'

const useAuthStore = create((set) => ({
  user: null,
  token: localStorage.getItem('token'),
  isAuthenticated: !!localStorage.getItem('token'),
  
  setUser: (user) => set({ user, isAuthenticated: true }),
  setToken: (token) => {
    localStorage.setItem('token', token)
    set({ token, isAuthenticated: true })
  },
  logout: () => {
    localStorage.removeItem('token')
    set({ user: null, token: null, isAuthenticated: false })
  },
}))

export default useAuthStore
