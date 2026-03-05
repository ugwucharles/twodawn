<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin · {{ config('app.name', '2DAWN') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

<style>
    :root {
        --font-ui: 'Montserrat', ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
    }
    * { box-sizing: border-box; }
    body {
        margin: 0; min-height: 100vh;
        font-family: var(--font-ui);
        background: #0f0f0f;
        display: flex; align-items: center; justify-content: center;
    }
    .admin-card {
        width: 100%; max-width: 360px;
        background: #1a1a1a;
        border: 1px solid #2a2a2a;
        border-radius: 16px;
        padding: 40px 32px;
        margin: 40px 16px;
    }
    .admin-badge {
        display: inline-flex; align-items: center; gap: 6px;
        background: #ff4d2d15; border: 1px solid #ff4d2d30;
        color: #ff6b4d; font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.1em;
        padding: 4px 12px; border-radius: 6px; margin-bottom: 24px;
    }
    .admin-title {
        color: #fff; font-size: 22px; font-weight: 800; margin: 0 0 8px;
    }
    .admin-subtitle {
        color: #666; font-size: 13px; font-weight: 500; margin: 0 0 28px;
    }
    .admin-form { display: flex; flex-direction: column; gap: 16px; }
    .admin-form label { color: #888; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; display: block; }
    .admin-form input[type="email"],
    .admin-form input[type="password"] {
        width: 100%; padding: 12px 14px;
        background: #111; border: 1px solid #2a2a2a; border-radius: 8px;
        color: #fff; font-family: inherit; font-size: 14px;
        outline: none; transition: border-color 0.2s;
    }
    .admin-form input:focus { border-color: #ff4d2d; }
    .admin-form input::placeholder { color: #444; }
    .form-row { display: flex; justify-content: space-between; align-items: center; }
    .form-row label { color: #666; font-size: 13px; font-weight: 500; text-transform: none; letter-spacing: 0; display: flex; align-items: center; gap: 6px; cursor: pointer; }
    .form-row a { color: #ff6b4d; font-size: 13px; text-decoration: none; }
    .form-row a:hover { text-decoration: underline; }
    .admin-btn {
        width: 100%; padding: 13px; margin-top: 8px;
        background: #ff4d2d; color: #fff; border: none; border-radius: 8px;
        font-family: inherit; font-size: 14px; font-weight: 700;
        cursor: pointer; transition: background 0.2s, transform 0.1s;
    }
    .admin-btn:hover { background: #e8432a; }
    .admin-btn:active { transform: scale(0.98); }
    .alert-error { background: #ff4d2d15; border: 1px solid #ff4d2d30; color: #ff6b4d; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 8px; }
    .alert-success { background: #00c85115; border: 1px solid #00c85130; color: #00c851; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 8px; }
</style>
</head>
<body>
    @yield('content')
</body>
</html>
