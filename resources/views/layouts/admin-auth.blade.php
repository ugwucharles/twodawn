<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin · {{ config('app.name', '2DAWN') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root {
        --font-ui: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        min-height: 100vh;
        font-family: var(--font-ui);
        background: #FFFFFF;
        display: flex; align-items: center; justify-content: center;
        color: #111827;
    }
    .admin-card {
        width: 100%; max-width: 400px;
        background: #FFFFFF;
        border: 1px solid #E5E7EB;
        border-radius: 16px;
        padding: 48px 40px;
        margin: 40px 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 6px 16px rgba(0,0,0,0.04);
    }
    .admin-badge {
        display: inline-flex; align-items: center; gap: 6px;
        background: #F3F4F6; border: 1px solid #E5E7EB;
        color: #374151; font-size: 11px; font-weight: 600;
        text-transform: uppercase; letter-spacing: 0.08em;
        padding: 4px 12px; border-radius: 6px; margin-bottom: 24px;
    }
    .admin-title {
        color: #111827; font-size: 24px; font-weight: 700; margin: 0 0 6px;
    }
    .admin-subtitle {
        color: #9CA3AF; font-size: 14px; font-weight: 400; margin: 0 0 32px;
    }
    .admin-form { display: flex; flex-direction: column; gap: 16px; }
    .admin-form label { color: #374151; font-size: 13px; font-weight: 500; margin-bottom: 6px; display: block; }
    .admin-form input[type="email"],
    .admin-form input[type="password"] {
        width: 100%; padding: 12px 14px;
        background: #F9FAFB; border: 1px solid #D1D5DB; border-radius: 6px;
        color: #111827; font-family: inherit; font-size: 14px;
        outline: none; transition: border-color 0.2s, box-shadow 0.2s;
    }
    .admin-form input:focus { border-color: #6366F1; box-shadow: 0 0 0 3px rgba(99,102,241,0.2); }
    .admin-form input::placeholder { color: #9CA3AF; }
    .form-row { display: flex; justify-content: space-between; align-items: center; }
    .form-row label { color: #6B7280; font-size: 13px; font-weight: 500; text-transform: none; letter-spacing: 0; display: flex; align-items: center; gap: 6px; cursor: pointer; }
    .form-row a { color: #6366F1; font-size: 13px; text-decoration: none; font-weight: 500; }
    .form-row a:hover { text-decoration: underline; }
    .admin-btn {
        width: 100%; padding: 10px 16px; margin-top: 8px;
        background: #6366F1; color: #FFFFFF; border: none; border-radius: 6px;
        font-family: inherit; font-size: 14px; font-weight: 600;
        cursor: pointer; transition: background 0.2s, transform 0.1s;
    }
    .admin-btn:hover { background: #4F46E5; }
    .admin-btn:active { transform: scale(0.98); }
    .alert-error { background: #FEF2F2; border: 1px solid #FECACA; color: #DC2626; padding: 10px 14px; border-radius: 6px; font-size: 13px; margin-bottom: 8px; }
    .alert-success { background: #F0FDF4; border: 1px solid #BBF7D0; color: #16A34A; padding: 10px 14px; border-radius: 6px; font-size: 13px; margin-bottom: 8px; }
</style>
</head>
<body>
    @yield('content')
</body>
</html>
