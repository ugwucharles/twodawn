import { Link } from 'react-router-dom'

function AuthLogo() {
  return (
    <div className="flex justify-center mb-6">
      <Link to="/">
        <img src="/logo-white2.svg" alt="2DAWN" className="h-8 w-auto" />
      </Link>
    </div>
  )
}

export default AuthLogo
