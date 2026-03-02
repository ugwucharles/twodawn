<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', '2DAWN') }} - Auth</title>

<style>
    /* Uiverse form CSS */
    .form-container {
      max-width: 300px; /* Reduced max-width */
      background-color: #fff;
      padding: 32px 24px;
      font-size: 14px;
      font-family: inherit;
      color: #212121;
      display: flex;
      flex-direction: column;
      gap: 20px;
      box-sizing: border-box;
      border-radius: 10px;
      box-shadow: 0px 0px 3px rgba(0, 0, 0, 0.084), 0px 2px 3px rgba(0, 0, 0, 0.168);
      margin: 80px auto;
    }
    .form-container button:active {scale:0.95;}
    .form-container .logo-container {text-align:center;font-weight:600;font-size:18px;}
    .form-container .form {display:flex;flex-direction:column;}
    .form-container .form-group {display:flex;flex-direction:column;gap:2px;}
    .form-container .form-group label {margin-bottom:5px;}
    .form-container .form-group input {width:100%;padding:12px 16px;border-radius:6px;font-family:inherit;border:1px solid #ccc;box-sizing:border-box;}
    .form-container .form-group input::placeholder {opacity:0.5;}
    .form-container .form-group input:focus {outline:none;border-color:#1778f2;}
    .form-container .form-submit-btn {display:flex;justify-content:center;align-items:center;font-family:inherit;color:#fff;background-color:#212121;border:none;width:100%;padding:12px 16px;font-size:inherit;gap:8px;margin:12px 0;cursor:pointer;border-radius:6px;box-shadow:0px 0px 3px rgba(0,0,0,0.084),0px 2px 3px rgba(0,0,0,0.168);}
    .form-container .form-submit-btn:hover {background-color:#313131;}
    .form-container .link {color:#1778f2;text-decoration:none;}
    .form-container .signup-link {align-self:center;font-weight:500;}
    .form-container .signup-link .link {font-weight:400;}
    .form-container .link:hover {text-decoration:underline;}
    .alert-success {background:#e6ffed;color:#0f5132;padding:10px;border-radius:5px;margin-bottom:10px;}
    .alert-error {background:#f8d7da;color:#842029;padding:10px;border-radius:5px;margin-bottom:10px;}

    /* Responsive media query */
    @media (max-width: 400px) {
        .form-container {
            margin: 20px auto; /* Adjust margin for smaller screens */
            padding: 20px 15px; /* Adjust padding for smaller screens */
        }
    }
</style>
</head>
<body class="bg-[#f8f7fa]">
    @yield('content')
</body>
</html>
