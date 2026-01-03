<?php
session_start();
require 'db.php'; // Pastikan file ini ada

// --- 1. PENGATURAN BAHASA (LANGUAGE SETUP) ---

// Set bahasa default ke Indonesia jika belum ada session
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'id';
}

// Ubah bahasa jika ada parameter di URL (misal: ?lang=en)
if (isset($_GET['lang'])) {
    $lang_code = $_GET['lang'];
    if ($lang_code == 'en' || $lang_code == 'id') {
        $_SESSION['lang'] = $lang_code;
    }
}

$l = $_SESSION['lang']; // Simpan kode bahasa saat ini (id/en)

// --- 2. KAMUS KATA (DICTIONARY) ---
$trans = [
    'id' => [
        // UI Titles & Text
        'title_login' => 'Selamat Datang',
        'title_reg' => 'Buat Akun',
        'title_reset' => 'Reset Password',
        'text_reset' => 'Masukkan detail Anda untuk mereset password.',
        'ph_username' => 'Nama Pengguna',
        'ph_pass' => 'Kata Sandi',
        'ph_email' => 'Alamat Email',
        'ph_new_pass' => 'Kata Sandi Baru',
        'btn_login' => 'Masuk',
        'btn_reg' => 'Daftar',
        'btn_reset' => 'Perbarui Password',
        'link_forgot' => 'Lupa Kata Sandi?',
        'link_new_user' => 'Baru di Movdo?',
        'link_create' => 'Buat Akun',
        'link_have_acc' => 'Sudah punya akun?',
        'link_signin' => 'Masuk',
        'link_back' => 'Kembali ke',
        
        // PHP Messages
        'msg_wrong_pass' => 'Password salah!',
        'msg_user_not_found' => 'Username tidak ditemukan!',
        'msg_db_error' => 'Kesalahan Database.',
        'msg_user_exist' => 'Username sudah dipakai.',
        'msg_reg_success' => 'Pendaftaran Berhasil! Silakan Login.',
        'msg_reg_fail' => 'Gagal mendaftar: ',
        'msg_pass_changed' => 'Password BERHASIL diubah! Login sekarang.',
        'msg_reset_fail' => 'Gagal mereset password.',
        'msg_data_mismatch' => 'Data tidak cocok!',
    ],
    'en' => [
        // UI Titles & Text
        'title_login' => 'Welcome Back',
        'title_reg' => 'Create Account',
        'title_reset' => 'Reset Password',
        'text_reset' => 'Enter your details to reset your password.',
        'ph_username' => 'Username',
        'ph_pass' => 'Password',
        'ph_email' => 'Email Address',
        'ph_new_pass' => 'New Password',
        'btn_login' => 'Sign In',
        'btn_reg' => 'Sign Up',
        'btn_reset' => 'Update Password',
        'link_forgot' => 'Forgot Password?',
        'link_new_user' => 'New to Movdo?',
        'link_create' => 'Create Account',
        'link_have_acc' => 'Already a member?',
        'link_signin' => 'Sign In',
        'link_back' => 'Back to',

        // PHP Messages
        'msg_wrong_pass' => 'Incorrect Password!',
        'msg_user_not_found' => 'Username not found!',
        'msg_db_error' => 'Database Error.',
        'msg_user_exist' => 'Username is already taken.',
        'msg_reg_success' => 'Registration Successful! Please Login.',
        'msg_reg_fail' => 'Registration Failed: ',
        'msg_pass_changed' => 'Password changed SUCCESSFULLY! Login now.',
        'msg_reset_fail' => 'Failed to reset password.',
        'msg_data_mismatch' => 'Data does not match!',
    ]
];

// Variabel pintas untuk teks saat ini
$t = $trans[$l]; 

// Kalau sudah login, lempar ke index
if (isset($_SESSION['user_logged_in'])) {
    header("Location: index.php");
    exit;
}

$message = "";
$msg_type = ""; 

// --- 3. PROSES PHP (Menggunakan Variabel $t) ---

// LOGIN
if (isset($_POST['action']) && $_POST['action'] == 'login') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (isset($conn)) {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_logged_in'] = true;
                $_SESSION['username'] = $user['username'];
                header("Location: index.php");
                exit;
            } else {
                $message = $t['msg_wrong_pass'];
                $msg_type = "error";
            }
        } else {
            $message = $t['msg_user_not_found'];
            $msg_type = "error";
        }
    } else {
        $message = $t['msg_db_error'];
        $msg_type = "error";
    }
}

// REGISTER
if (isset($_POST['action']) && $_POST['action'] == 'register') {
    $user = $_POST['reg_username'];
    $email = $_POST['reg_email'];
    $pass = $_POST['reg_password'];
    
    $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

    if (isset($conn)) {
        $check = $conn->query("SELECT id FROM users WHERE username = '$user'");
        if ($check->num_rows > 0) {
            $message = $t['msg_user_exist'];
            $msg_type = "error";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, subscription_type) VALUES (?, ?, ?, 'Free')");
            $stmt->bind_param("sss", $user, $email, $hashed_pass);
            if ($stmt->execute()) {
                $message = $t['msg_reg_success'];
                $msg_type = "success";
            } else {
                $message = $t['msg_reg_fail'] . $conn->error;
                $msg_type = "error";
            }
        }
    }
}

// RESET PASSWORD
if (isset($_POST['action']) && $_POST['action'] == 'reset_password') {
    $r_user = $_POST['reset_username'];
    $r_email = $_POST['reset_email'];
    $r_new_pass = $_POST['reset_new_password'];

    if (isset($conn)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND email = ?");
        $stmt->bind_param("ss", $r_user, $r_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $new_hash = password_hash($r_new_pass, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
            $update->bind_param("ss", $new_hash, $r_user);
            
            if ($update->execute()) {
                $message = $t['msg_pass_changed'];
                $msg_type = "success";
            } else {
                $message = $t['msg_reset_fail'];
                $msg_type = "error";
            }
        } else {
            $message = $t['msg_data_mismatch'];
            $msg_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?= $l ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MOVDO</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* --- COPY CSS ASLI ANDA --- */
        :root {
            --bg-body: #020617;
            --primary: #3b82f6;
            --accent: #06b6d4;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --gradient-main: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%);
            --glass-bg: rgba(15, 23, 42, 0.6);
            --glass-border: 1px solid rgba(255,255,255,0.08);
        }

        * { box-sizing: border-box; outline: none; }
        body {
            margin: 0; padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            height: 100vh;
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
            position: relative;
        }

        body::before {
            content: ''; position: absolute;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(59,130,246,0.15) 0%, rgba(2,6,23,0) 70%);
            top: -100px; left: -100px; z-index: -1;
        }
        body::after {
            content: ''; position: absolute;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(6,182,212,0.1) 0%, rgba(2,6,23,0) 70%);
            bottom: -50px; right: -50px; z-index: -1;
        }

        .container {
            width: 100%; max-width: 400px;
            padding: 40px;
            background: var(--glass-bg);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: var(--glass-border);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: floatUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1);
            position: relative; z-index: 10;
        }

        .logo {
            text-align: center; margin-bottom: 30px;
            font-size: 32px; font-weight: 800; letter-spacing: -1px;
            background: var(--gradient-main); -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            text-shadow: 0 0 30px rgba(59, 130, 246, 0.3);
        }

        h3 { color: #fff; margin: 0 0 20px; font-weight: 700; font-size: 20px; text-align: center; }
        p { color: var(--text-muted); font-size: 13px; text-align: center; margin-top: -15px; margin-bottom: 25px; line-height: 1.5; }

        .input-group { position: relative; margin-bottom: 16px; }
        
        .input-icon {
            position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
            color: var(--text-muted); font-size: 14px; transition: 0.3s;
        }

        input {
            width: 100%; padding: 14px 14px 14px 45px;
            background: rgba(2, 6, 23, 0.5);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            color: #fff; font-size: 14px; font-family: inherit;
            transition: 0.3s;
        }
        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.2);
            background: rgba(2, 6, 23, 0.8);
        }
        input:focus + .input-icon { color: var(--primary); }

        button {
            width: 100%; padding: 14px; margin-top: 10px;
            background: var(--gradient-main);
            color: #fff; border: none; border-radius: 12px;
            font-weight: 700; font-size: 14px; cursor: pointer;
            transition: 0.3s; letter-spacing: 0.5px;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
        }

        .msg {
            padding: 12px; border-radius: 10px; font-size: 13px; text-align: center; margin-bottom: 20px; font-weight: 600;
        }
        .msg.error { background: rgba(239, 68, 68, 0.15); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.2); }
        .msg.success { background: rgba(34, 197, 94, 0.15); color: #86efac; border: 1px solid rgba(34, 197, 94, 0.2); }

        .links { margin-top: 25px; text-align: center; font-size: 13px; color: var(--text-muted); }
        .links span {
            color: var(--accent); font-weight: 600; cursor: pointer; transition: 0.2s;
            margin-left: 5px;
        }
        .links span:hover { color: #fff; text-shadow: 0 0 10px var(--accent); }

        .forgot-pass {
            text-align: right; font-size: 12px; margin-top: -8px; margin-bottom: 20px;
        }
        .forgot-pass span { color: var(--text-muted); cursor: pointer; transition: 0.2s; }
        .forgot-pass span:hover { color: #fff; }

        .hidden { display: none; }
        
        @keyframes floatUp { from { opacity: 0; transform: translateY(30px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
        
        .fade-in { animation: fadeIn 0.4s ease forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateX(10px); } to { opacity: 1; transform: translateX(0); } }

        /* --- TAMBAHAN STYLE UNTUK BAHASA --- */
        .lang-switch {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 20;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 5px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.05);
        }
        .lang-switch a {
            text-decoration: none;
            color: #94a3b8;
            font-size: 12px;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 15px;
            transition: all 0.3s;
            display: inline-block;
        }
        .lang-switch a.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 2px 10px rgba(59, 130, 246, 0.4);
        }
        .lang-switch a:hover:not(.active) {
            color: white;
        }
    </style>
</head>
<body>

<div class="lang-switch">
    <a href="?lang=id" class="<?= $l == 'id' ? 'active' : '' ?>">ID</a>
    <a href="?lang=en" class="<?= $l == 'en' ? 'active' : '' ?>">EN</a>
</div>

<div class="container">
    <div class="logo">MOVDO</div>
    
    <?php if($message): ?>
        <div class="msg <?= $msg_type == 'success' ? 'success' : 'error' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <div id="loginForm" class="fade-in">
        <h3><?= $t['title_login'] ?></h3>
        <form method="POST">
            <input type="hidden" name="action" value="login">
            
            <div class="input-group">
                <input type="text" name="username" placeholder="<?= $t['ph_username'] ?>" required>
                <i class="fas fa-user input-icon"></i>
            </div>
            
            <div class="input-group">
                <input type="password" name="password" placeholder="<?= $t['ph_pass'] ?>" required>
                <i class="fas fa-lock input-icon"></i>
            </div>

            <div class="forgot-pass">
                <span onclick="showPage('reset')"><?= $t['link_forgot'] ?></span>
            </div>

            <button type="submit"><?= $t['btn_login'] ?></button>
        </form>
        <div class="links">
            <?= $t['link_new_user'] ?> <span onclick="showPage('register')"><?= $t['link_create'] ?></span>
        </div>
    </div>

    <div id="regForm" class="hidden fade-in">
        <h3><?= $t['title_reg'] ?></h3>
        <form method="POST">
            <input type="hidden" name="action" value="register">
            
            <div class="input-group">
                <input type="text" name="reg_username" placeholder="<?= $t['ph_username'] ?>" required>
                <i class="fas fa-user input-icon"></i>
            </div>

            <div class="input-group">
                <input type="email" name="reg_email" placeholder="<?= $t['ph_email'] ?>" required>
                <i class="fas fa-envelope input-icon"></i>
            </div>
            
            <div class="input-group">
                <input type="password" name="reg_password" placeholder="<?= $t['ph_pass'] ?>" required>
                <i class="fas fa-lock input-icon"></i>
            </div>

            <button type="submit"><?= $t['btn_reg'] ?></button>
        </form>
        <div class="links">
            <?= $t['link_have_acc'] ?> <span onclick="showPage('login')"><?= $t['link_signin'] ?></span>
        </div>
    </div>

    <div id="resetForm" class="hidden fade-in">
        <h3><?= $t['title_reset'] ?></h3>
        <p><?= $t['text_reset'] ?></p>
        <form method="POST">
            <input type="hidden" name="action" value="reset_password">
            
            <div class="input-group">
                <input type="text" name="reset_username" placeholder="<?= $t['ph_username'] ?>" required>
                <i class="fas fa-user input-icon"></i>
            </div>

            <div class="input-group">
                <input type="email" name="reset_email" placeholder="<?= $t['ph_email'] ?>" required>
                <i class="fas fa-envelope input-icon"></i>
            </div>
            
            <div class="input-group">
                <input type="password" name="reset_new_password" placeholder="<?= $t['ph_new_pass'] ?>" required>
                <i class="fas fa-key input-icon"></i>
            </div>

            <button type="submit"><?= $t['btn_reset'] ?></button>
        </form>
        <div class="links">
            <?= $t['link_back'] ?> <span onclick="showPage('login')"><?= $t['link_signin'] ?></span>
        </div>
    </div>

</div>

<script>
    function showPage(pageId) {
        // Hide all
        document.getElementById('loginForm').classList.add('hidden');
        document.getElementById('regForm').classList.add('hidden');
        document.getElementById('resetForm').classList.add('hidden');

        // Remove animation class temporarily to re-trigger it
        const target = (pageId === 'login') ? document.getElementById('loginForm') 
                     : (pageId === 'register') ? document.getElementById('regForm') 
                     : document.getElementById('resetForm');
        
        target.classList.remove('hidden');
        
        // Simple trick to restart CSS animation
        target.classList.remove('fade-in');
        void target.offsetWidth; // trigger reflow
        target.classList.add('fade-in');
    }
</script>

</body>
</html>