import { GoogleLogin } from '@react-oauth/google'
import api from '../services/api'
import { useNavigate } from 'react-router-dom'

function GoogleSignInButton({ onError, disabled }) {
  const navigate = useNavigate()

  const handleSuccess = async (credentialResponse) => {
    try {
      const response = await api.post('/organizer/google-auth', {
        credential: credentialResponse.credential,
      })

      if (response.data.token) {
        localStorage.setItem('token', response.data.token)
      }
      navigate(response.data.redirect || '/organizer/dashboard')
    } catch (err) {
      onError?.(err.response?.data?.message || 'Google sign-in failed')
    }
  }

  return (
    <div className={`w-full flex justify-center ${disabled ? 'opacity-50 pointer-events-none' : ''}`}>
      <div className="w-full max-w-[300px]">
        <GoogleLogin
          onSuccess={handleSuccess}
          onError={() => onError?.('Google sign-in was cancelled or failed')}
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
