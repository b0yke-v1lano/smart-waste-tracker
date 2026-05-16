<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login — Smart Waste Tracker</title>

    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../img/logo.png">

    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            min-height: 100vh;
            overflow-x: hidden;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #022c22 0%, #065f46 50%, #10b981 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }

        /* Decorative blobs */
        body::before,
        body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.25;
            pointer-events: none;
            z-index: 0;
        }
        body::before {
            width: 300px; height: 300px;
            background: #10b981;
            top: -50px; left: -50px;
        }
        body::after {
            width: 250px; height: 250px;
            background: #059669;
            bottom: -50px; right: -50px;
        }

        /* ===== CARD ===== */
        .login-wrapper {
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 1;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow:
                0 8px 40px rgba(0, 0, 0, 0.3),
                inset 0 0 0 1px rgba(255,255,255,0.06);
            padding: 40px 36px;
            color: #fff;
        }

        /* ===== LOGO / HEADER ===== */
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-logo {
            width: 64px; height: 64px;
            background: rgba(16,185,129,0.2);
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            border: 1px solid rgba(16,185,129,0.3);
        }
        .login-logo i {
            font-size: 1.6rem;
            color: #10b981;
        }
        .login-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 14px;
        }
        .login-header h1 {
            font-size: 1.3rem;
            font-weight: 800;
            margin: 0 0 4px;
            color: #fff;
            letter-spacing: 0.3px;
        }
        .login-header p {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.6);
            margin: 0;
            font-weight: 600;
        }

        /* ===== FORM ===== */
        .form-group-login {
            margin-bottom: 16px;
        }
        .form-group-login label {
            display: block;
            font-size: 0.8rem;
            font-weight: 700;
            color: rgba(255,255,255,0.75);
            margin-bottom: 6px;
            letter-spacing: 0.3px;
        }

        .input-wrapper {
            position: relative;
        }
        .input-wrapper i {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.45);
            font-size: 0.9rem;
            pointer-events: none;
        }
        .input-wrapper input {
            width: 100%;
            background: rgba(255, 255, 255, 0.12);
            border: 1.5px solid rgba(255,255,255,0.18);
            border-radius: 10px;
            padding: 10px 12px 10px 38px;
            font-family: 'Nunito', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            color: #fff;
            caret-color: #10b981;
            outline: none;
            transition: background 0.2s, border-color 0.2s, box-shadow 0.2s;
        }
        .input-wrapper input::placeholder {
            color: rgba(255,255,255,0.4);
            font-weight: 400;
        }
        .input-wrapper input:focus {
            background: rgba(255, 255, 255, 0.2);
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16,185,129,0.2);
        }

        /* Toggle password */
        .toggle-pw {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: rgba(255,255,255,0.45);
            font-size: 0.85rem;
            padding: 0;
            transition: color 0.2s;
        }
        .toggle-pw:hover { color: rgba(255,255,255,0.8); }

        /* ===== SUBMIT BUTTON ===== */
        .btn-login {
            width: 100%;
            padding: 11px;
            background: linear-gradient(135deg, #10b981, #059669);
            border: none;
            border-radius: 10px;
            font-family: 'Nunito', sans-serif;
            font-size: 0.95rem;
            font-weight: 800;
            color: #fff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 4px 14px rgba(16,185,129,0.35);
            margin-top: 8px;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #059669, #047857);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(16,185,129,0.4);
        }
        .btn-login:active {
            transform: translateY(0);
        }

        /* ===== ALERT ===== */
        .alert-error {
            margin-top: 16px;
            padding: 10px 14px;
            background: rgba(239,68,68,0.15);
            border: 1px solid rgba(239,68,68,0.35);
            border-radius: 10px;
            color: #fca5a5;
            font-size: 0.83rem;
            font-weight: 700;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
        }

        /* ===== FOOTER ===== */
        .login-footer {
            text-align: center;
            margin-top: 28px;
            font-size: 0.75rem;
            color: rgba(255,255,255,0.35);
            font-weight: 600;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 480px) {
            .login-card {
                padding: 28px 22px;
                border-radius: 18px;
            }
            .login-header h1 { font-size: 1.15rem; }
        }

        @media (max-width: 360px) {
            body { padding: 12px; }
            .login-card { padding: 24px 18px; }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">

        <!-- Header -->
        <div class="login-header">
            <div class="login-logo">
                <img src="../img/logo.png" alt="Logo">
            </div>
            <h1>Smart Waste Tracker</h1>
            <p>Masuk ke akun Anda</p>
        </div>

        <!-- Form -->
        <form action="proses_login.php" method="POST" autocomplete="off">

            <div class="form-group-login">
                <label for="login-email">Email</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="login-email" name="email"
                           placeholder="nama@email.com" required autocomplete="email">
                </div>
            </div>

            <div class="form-group-login">
                <label for="login-password">Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="login-password" name="password"
                           placeholder="••••••••" required autocomplete="current-password">
                    <button type="button" class="toggle-pw" id="togglePw" aria-label="Tampilkan password">
                        <i id="togglePwIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login">
                Masuk
            </button>

        </form>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php 
                    if($_GET['error'] == 'password') echo "Password yang Anda masukkan salah.";
                    else if($_GET['error'] == 'user') echo "Email tidak terdaftar.";
                    else echo "Login gagal. Silakan coba lagi.";
                ?>
            </div>
        <?php endif; ?>

    </div>

    <div class="login-footer">Copyright &copy; Smart Waste Tracker 2026</div>
</div>

<script>
    /* Toggle show/hide password */
    const togglePw   = document.getElementById('togglePw');
    const pwInput    = document.getElementById('login-password');
    const toggleIcon = document.getElementById('togglePwIcon');

    togglePw.addEventListener('click', () => {
        const isHidden = pwInput.type === 'password';
        pwInput.type       = isHidden ? 'text' : 'password';
        toggleIcon.className = isHidden ? 'fas fa-eye-slash' : 'fas fa-eye';
    });
</script>

</body>
</html>