import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import Home from './pages/Home'
import Events from './pages/Events'
import EventDetail from './pages/EventDetail'
import Login from './pages/Login'
import Register from './pages/Register'
import FindTickets from './pages/FindTickets'
import Checkout from './pages/Checkout'
import OrderConfirmation from './pages/OrderConfirmation'
import PaymentSuccess from './pages/PaymentSuccess'
import AdminDashboard from './pages/admin/Dashboard'
import AdminActivity from './pages/admin/Activity'
import AdminEvents from './pages/admin/Events'
import AdminOrganizers from './pages/admin/Organizers'
import AdminTransactions from './pages/admin/Transactions'
import AdminWithdrawals from './pages/admin/Withdrawals'
import AdminAnalytics from './pages/admin/Analytics'
import AdminHealth from './pages/admin/Health'
import AdminLogin from './pages/admin/Login'
import AdminLayout from './components/admin/AdminLayout'
import OrganizerDashboard from './pages/organizer/Dashboard'
import OrganizerLayout from './pages/organizer/OrganizerLayout'
import OrganizerEvents from './pages/organizer/Events'
import OrganizerOrders from './pages/organizer/Orders'
import OrganizerScanner from './pages/organizer/Scanner'
import OrganizerScanned from './pages/organizer/Scanned'
import OrganizerWallet from './pages/organizer/Wallet'
import OrganizerSettings from './pages/organizer/Settings'
import CreateEvent from './pages/organizer/CreateEvent'
import EventDetails from './pages/organizer/EventDetails'
import EditEvent from './pages/organizer/EditEvent'
import HostPanel from './pages/HostPanel'
import Onboarding from './pages/Onboarding'
import Debug from './pages/Debug'

import { useEffect } from 'react'
import { useLocation, useNavigate } from 'react-router-dom'

function NavigationPersist() {
  const location = useLocation()
  const navigate = useNavigate()

  useEffect(() => {
    localStorage.setItem('lastPath', location.pathname)
  }, [location])

  useEffect(() => {
    if (location.pathname === '/' && localStorage.getItem('lastPath')) {
      const path = localStorage.getItem('lastPath')
      if (path && path !== '/') navigate(path, { replace: true })
    }
  }, [])

  return null
}

function App() {
  return (
    <BrowserRouter future={{ v7_startTransition: true, v7_relativeSplatPath: true }}>
      <NavigationPersist />
      <Routes>
        <Route path="/" element={<Home />} />
        <Route path="/events" element={<Events />} />
        <Route path="/events/recent" element={<Events recent={true} />} />
        <Route path="/find-tickets" element={<FindTickets />} />
        <Route path="/event/:slug" element={<EventDetail />} />
        <Route path="/events/:id" element={<EventDetail />} />
        <Route path="/events/:id/checkout" element={<Checkout />} />
        <Route path="/orders/:reference" element={<OrderConfirmation />} />
        <Route path="/payment-success" element={<PaymentSuccess />} />
        <Route path="/login" element={<Login />} />
        <Route path="/organizer/login" element={<Login />} />
        <Route path="/register" element={<Register />} />
        <Route path="/onboarding" element={<Onboarding />} />
        <Route path="/ucc/login" element={<AdminLogin />} />
        <Route path="/ucc" element={<AdminLayout />}>
          <Route index element={<Navigate to="/ucc/dashboard" replace />} />
          <Route path="dashboard" element={<AdminDashboard />} />
          <Route path="activity" element={<AdminActivity />} />
          <Route path="events" element={<AdminEvents />} />
          <Route path="organizers" element={<AdminOrganizers />} />
          <Route path="transactions" element={<AdminTransactions />} />
          <Route path="withdrawals" element={<AdminWithdrawals />} />
          <Route path="analytics" element={<AdminAnalytics />} />
          <Route path="health" element={<AdminHealth />} />
        </Route>
        <Route path="/organizer" element={<OrganizerLayout />}>
          <Route path="dashboard" element={<OrganizerDashboard />} />
          <Route path="events/create" element={<CreateEvent />} />
          <Route path="events/:id/edit" element={<EditEvent />} />
          <Route path="events/:id" element={<EventDetails />} />
          <Route path="events" element={<OrganizerEvents />} />
          <Route path="orders" element={<OrganizerOrders />} />
          <Route path="scanner" element={<OrganizerScanner />} />
          <Route path="scanned" element={<OrganizerScanned />} />
          <Route path="wallet" element={<OrganizerWallet />} />
          <Route path="settings" element={<OrganizerSettings />} />
        </Route>
        <Route path="/h/:token" element={<HostPanel />} />
        {import.meta.env.DEV && <Route path="/debug" element={<Debug />} />}
      </Routes>
    </BrowserRouter>
  )
}

export default App
