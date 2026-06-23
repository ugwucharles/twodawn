import { GoogleLogin } from '@react-oauth/google'
import api from '../services/api'
import { useNavigate } from 'react-router-dom'

function GoogleSignInButton({ onError, disabled, onSuccess }) {
  const navigate = useNavigate()

  const handleSuccess = async (credentialResponse) => {
    try {
      const response = await api.post('/organizer/google-auth', {
        credential: credentialResponse.credential,
      })

      if (response.data.token) {
        localStorage.setItem('token', response.data.token)
      }
      onSuccess?.()
      setTimeout(() => {
        navigate(response.data.redirect || '/organizer/dashboard')
      }, 2000)
    } catch (err) {
      const errorData = {
        message: err.message,
        response: err.response?.data,
        status: err.response?.status,
        timestamp: new Date().toISOString()
      }
      localStorage.setItem('google_signin_error', JSON.stringify(errorData))
      onError?.(err.response?.data?.message || 'Google sign-in failed')
    }
  }

  return (
    <div className={`w-full flex justify-center ${disabled ? 'opacity-50 pointer-events-none' : ''}`}>
      <div className="w-full max-w-[300px]">
        <GoogleLogin
          onSuccess={handleSuccess}
          onError={(error) => {
            const errorData = {
              message: 'Google login widget error',
              error: error,
              timestamp: new Date().toISOString()
            }
            localStorage.setItem('google_signin_error', JSON.stringify(errorData))
            onError?.('Google sign-in was cancelled or failed')
          }}
          theme="outline"
          size="large"
          text="continue_with"
          shape="rectangular"
          width="300"
          use_one_tap
          auto_select={false}
        />
      </div>
    </div>
  )
}

export default GoogleSignInButton
