function AuthFeedbackBanner({ success, message }) {
  if (!message) return null

  return (
    <div
      className={`px-4 py-3 rounded-lg mb-6 text-sm text-center ${
        success
          ? 'bg-green-50 border border-green-200 text-green-700'
          : 'bg-red-50 border border-red-200 text-red-700'
      }`}
    >
      {message}
      {success && <span className="block text-xs mt-1 opacity-80">Redirecting you now...</span>}
    </div>
  )
}

export default AuthFeedbackBanner
