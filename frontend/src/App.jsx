import { BrowserRouter, Routes, Route } from 'react-router-dom'
import Home from './pages/Home'
import Events from './pages/Events'
import EventDetail from './pages/EventDetail'
import Login from './pages/Login'
import Register from './pages/Register'
import FindTickets from './pages/FindTickets'
import Checkout from './pages/Checkout'
import OrderConfirmation from './pages/OrderConfirmation'
import AdminDashboard from './pages/admin/Dashboard'
import OrganizerDashboard from './pages/organizer/Dashboard'
import OrganizerLayout from './pages/organizer/OrganizerLayout'
import OrganizerEvents from './pages/organizer/Events'
import OrganizerOrders from './pages/organizer/Orders'
import OrganizerScanner from './pages/organizer/Scanner'
import OrganizerWallet from './pages/organizer/Wallet'
import OrganizerSettings from './pages/organizer/Settings'
import CreateEvent from './pages/organizer/CreateEvent'
import EventDetails from './pages/organizer/EventDetails'
import EditEvent from './pages/organizer/EditEvent'
import HostPanel from './pages/HostPanel'
import Onboarding from './pages/Onboarding'

import { useEffect } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';

function NavigationPersist(){
  const location = useLocation();
  const navigate = useNavigate();
  useEffect(()=>{
    // Store last visited path
    localStorage.setItem('lastPath', location.pathname);
  },[location]);
  useEffect(()=>{
    // On first load (no route match), redirect to stored path if exists
    if(location.pathname==='/' && localStorage.getItem('lastPath')){
      const path = localStorage.getItem('lastPath');
      if(path && path!=="/") navigate(path, {replace:true});
    }
  },[]);
  return null;
}

  return (
    <BrowserRouter future={{ v7_startTransition: true, v7_relativeSplatPath: true }}>
      <NavigationPersist />
        <Route path="/" element={<Home />} />
        <Route path="/events" element={<Events />} />
        <Route path="/events/recent" element={<Events recent={true} />} />
        <Route path="/find-tickets" element={<FindTickets />} />
        <Route path="/events/:id" element={<EventDetail />} />
        <Route path="/events/:id/checkout" element={<Checkout />} />
        <Route path="/orders/:reference" element={<OrderConfirmation />} />
        <Route path="/login" element={<Login />} />
        <Route path="/organizer/login" element={<Login />} />
        <Route path="/register" element={<Register />} />
        <Route path="/onboarding" element={<Onboarding />} />
        <Route path="/admin/dashboard" element={<AdminDashboard />} />
        <Route path="/organizer" element={<OrganizerLayout />}>
          <Route path="dashboard" element={<OrganizerDashboard />} />
          <Route path="events/create" element={<CreateEvent />} />
          <Route path="events/:id/edit" element={<EditEvent />} />
          <Route path="events/:id" element={<EventDetails />} />
          <Route path="events" element={<OrganizerEvents />} />
          <Route path="orders" element={<OrganizerOrders />} />
          <Route path="scanner" element={<OrganizerScanner />} />
          <Route path="wallet" element={<OrganizerWallet />} />
          <Route path="settings" element={<OrganizerSettings />} />
        </Route>
        <Route path="/h/:token" element={<HostPanel />} />
      </Routes>
    </BrowserRouter>
  )
}

export default App
