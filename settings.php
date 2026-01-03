<?php
session_start();
require 'db.php';

// --- 1. PENGATURAN BAHASA (LANGUAGE LOGIC) ---
$l = $_SESSION['lang'] ?? 'id'; 

$trans = [
    'id' => [
        'nav_account' => 'Akun', 'nav_prefs' => 'Preferensi', 'nav_list' => 'Daftar', 'nav_hist' => 'Riwayat', 
        'nav_help' => 'Bantuan', 'nav_sec' => 'Keamanan', 'nav_back' => 'Kembali', 'nav_logout' => 'Keluar',
        'tab_acc_title' => 'Informasi Akun', 'lbl_member_since' => 'Member sejak:', 'btn_upgrade' => 'Upgrade', 
        'lbl_username' => 'Username (Tidak dapat diubah)', 'lbl_email' => 'Alamat Email', 'btn_save_profile' => 'Simpan Perubahan',
        'tab_pref_title' => 'Preferensi Tampilan', 'sect_sub' => 'Pengaturan Subtitle', 'sect_sub_desc' => 'Sesuaikan tampilan teks saat menonton.',
        'sub_prev_text' => 'Halo, ini contoh tampilan subtitle Anda.', 'lbl_sub_size' => 'Ukuran Teks', 'lbl_sub_color' => 'Warna Teks',
        'opt_small' => 'Kecil', 'opt_normal' => 'Normal', 'opt_large' => 'Besar', 
        'opt_white' => 'Putih', 'opt_yellow' => 'Kuning', 'opt_blue' => 'Biru', 'btn_save_pref' => 'Simpan Preferensi',
        'lbl_autoplay' => 'Autoplay', 'desc_autoplay' => 'Putar episode berikutnya otomatis.', 
        'lbl_saver' => 'Mode Hemat Data', 'desc_saver' => 'Kurangi kualitas video untuk hemat kuota.',
        'tab_list_title' => 'Daftar Saya', 'btn_clear_all' => 'Hapus Semua', 'empty_list' => 'Belum ada film di daftar tontonan.',
        'tab_hist_title' => 'Riwayat Tontonan', 'btn_clear' => 'Bersihkan', 'empty_hist' => 'Belum ada aktivitas tontonan.', 'js_confirm_clear' => 'Hapus semua daftar?', 'js_confirm_hist' => 'Bersihkan riwayat?',
        'tab_help_title' => 'Bantuan & Dukungan', 'sect_contact' => 'Hubungi Kami', 'sect_contact_desc' => 'Punya kendala teknis atau saran film? Kirim pesan di bawah.',
        'lbl_subject' => 'Subjek', 'lbl_msg' => 'Pesan Anda', 'ph_msg' => 'Jelaskan detail masalah atau permintaan Anda...', 'btn_send' => 'Kirim Pesan',
        'sect_faq' => 'Pertanyaan Umum (FAQ)', 'faq_1_q' => 'Bagaimana cara berhenti berlangganan?', 'faq_1_a' => 'Anda dapat membatalkan langganan kapan saja melalui menu Payment.',
        'faq_2_q' => 'Apakah video bisa diunduh?', 'faq_2_a' => 'Saat ini fitur unduh hanya tersedia di aplikasi mobile MOVDO.',
        'tab_sec_title' => 'Keamanan', 'sect_pass' => 'Ganti Password', 'lbl_old_pass' => 'Password Lama', 'lbl_new_pass' => 'Password Baru', 'lbl_confirm_pass' => 'Konfirmasi', 'btn_update_pass' => 'Update Password',
        'sect_device' => 'Perangkat Aktif', 'sect_device_desc' => 'Perangkat yang saat ini masuk ke akun Anda.', 'dev_current' => 'Sesi Ini', 'dev_online' => 'Online',
        'sect_danger' => 'Zona Bahaya', 'desc_danger' => 'Tindakan ini tidak dapat dibatalkan. Data watchlist dan history akan hilang.', 'ph_del_pass' => 'Masukan password untuk konfirmasi', 'btn_del_acc' => 'Hapus Akun Saya', 'js_del_confirm' => 'YAKIN HAPUS AKUN PERMANEN?',
        'msg_upload_fail' => 'Gagal mengunggah gambar.', 'msg_file_size' => 'Ukuran file maksimal 2MB.', 'msg_file_format' => 'Format file harus JPG, PNG, atau WEBP.', 'msg_email_invalid' => 'Format email tidak valid.',
        'msg_prof_success' => 'Profil berhasil diperbarui.', 'msg_db_fail' => 'Gagal update database.', 'msg_pref_success' => 'Preferensi tampilan disimpan.',
        'msg_feedback_success' => 'Pesan Anda telah terkirim ke tim support.', 'msg_feedback_fail' => 'Gagal mengirim pesan.',
        'msg_pass_success' => 'Password berhasil diubah.', 'msg_pass_min' => 'Password minimal 6 karakter.', 'msg_pass_mismatch' => 'Konfirmasi password tidak cocok.', 'msg_pass_wrong' => 'Password lama salah.',
        'msg_list_cleared' => 'Daftar tontonan dibersihkan.', 'msg_hist_cleared' => 'Riwayat dihapus.', 'msg_acc_deleted' => 'Akun dihapus.',
    ],
    'en' => [
        'nav_account' => 'Account', 'nav_prefs' => 'Preferences', 'nav_list' => 'My List', 'nav_hist' => 'History', 
        'nav_help' => 'Support', 'nav_sec' => 'Security', 'nav_back' => 'Back', 'nav_logout' => 'Logout',
        'tab_acc_title' => 'Account Information', 'lbl_member_since' => 'Member since:', 'btn_upgrade' => 'Upgrade', 
        'lbl_username' => 'Username (Cannot be changed)', 'lbl_email' => 'Email Address', 'btn_save_profile' => 'Save Changes',
        'tab_pref_title' => 'Appearance Preferences', 'sect_sub' => 'Subtitle Settings', 'sect_sub_desc' => 'Customize how text appears when watching.',
        'sub_prev_text' => 'Hello, this is an example of your subtitle.', 'lbl_sub_size' => 'Text Size', 'lbl_sub_color' => 'Text Color',
        'opt_small' => 'Small', 'opt_normal' => 'Normal', 'opt_large' => 'Large', 
        'opt_white' => 'White', 'opt_yellow' => 'Yellow', 'opt_blue' => 'Blue', 'btn_save_pref' => 'Save Preferences',
        'lbl_autoplay' => 'Autoplay', 'desc_autoplay' => 'Play next episode automatically.', 
        'lbl_saver' => 'Data Saver Mode', 'desc_saver' => 'Reduce video quality to save data.',
        'tab_list_title' => 'My List', 'btn_clear_all' => 'Clear All', 'empty_list' => 'No movies in your watchlist yet.',
        'tab_hist_title' => 'Watch History', 'btn_clear' => 'Clear', 'empty_hist' => 'No watch activity yet.', 'js_confirm_clear' => 'Clear entire watchlist?', 'js_confirm_hist' => 'Clear watch history?',
        'tab_help_title' => 'Help & Support', 'sect_contact' => 'Contact Us', 'sect_contact_desc' => 'Having technical issues or movie requests? Send a message below.',
        'lbl_subject' => 'Subject', 'lbl_msg' => 'Your Message', 'ph_msg' => 'Explain the details of your issue or request...', 'btn_send' => 'Send Message',
        'sect_faq' => 'Frequently Asked Questions (FAQ)', 'faq_1_q' => 'How do I cancel my subscription?', 'faq_1_a' => 'You can cancel your subscription anytime via the Payment menu.',
        'faq_2_q' => 'Can I download videos?', 'faq_2_a' => 'Currently, download features are only available on the MOVDO mobile app.',
        'tab_sec_title' => 'Security', 'sect_pass' => 'Change Password', 'lbl_old_pass' => 'Old Password', 'lbl_new_pass' => 'New Password', 'lbl_confirm_pass' => 'Confirm', 'btn_update_pass' => 'Update Password',
        'sect_device' => 'Active Devices', 'sect_device_desc' => 'Devices currently logged into your account.', 'dev_current' => 'This Session', 'dev_online' => 'Online',
        'sect_danger' => 'Danger Zone', 'desc_danger' => 'This action cannot be undone. Watchlist and history data will be lost.', 'ph_del_pass' => 'Enter password to confirm', 'btn_del_acc' => 'Delete My Account', 'js_del_confirm' => 'ARE YOU SURE YOU WANT TO PERMANENTLY DELETE YOUR ACCOUNT?',
        'msg_upload_fail' => 'Failed to upload image.', 'msg_file_size' => 'Max file size is 2MB.', 'msg_file_format' => 'File format must be JPG, PNG, or WEBP.', 'msg_email_invalid' => 'Invalid email format.',
        'msg_prof_success' => 'Profile successfully updated.', 'msg_db_fail' => 'Database update failed.', 'msg_pref_success' => 'Display preferences saved.',
        'msg_feedback_success' => 'Your message has been sent to support.', 'msg_feedback_fail' => 'Failed to send message.',
        'msg_pass_success' => 'Password successfully changed.', 'msg_pass_min' => 'Password must be at least 6 characters.', 'msg_pass_mismatch' => 'Password confirmation does not match.', 'msg_pass_wrong' => 'Incorrect old password.',
        'msg_list_cleared' => 'Watchlist cleared.', 'msg_hist_cleared' => 'History cleared.', 'msg_acc_deleted' => 'Account deleted.',
    ]
];

$t = $trans[$l]; 

// --- KONFIGURASI ---
define('UPLOAD_DIR', 'uploads/avatars/');
if (!file_exists(UPLOAD_DIR)) { mkdir(UPLOAD_DIR, 0777, true); }

if (!isset($_SESSION['user_logged_in'])) { header("Location: login.php"); exit; }
$current_user = $_SESSION['username'];
$message = ""; $msg_type = "";

if (isset($_GET['action']) && $_GET['action'] == 'logout') { session_destroy(); header("Location: login.php"); exit; }

// 3. AMBIL DATA USER
if(isset($conn)) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $current_user);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $subs_pref = json_decode($user['subtitle_prefs'] ?? '{"size": "Normal", "color": "White"}', true);
} else {
    die("Koneksi database gagal.");
}

// --- LOGIKA BACKEND ---
if (isset($_POST['update_profile'])) {
    $new_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $upload_ok = true;
    $avatar_path = $user['avatar'];

    if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['avatar_file']['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed)) {
            if ($_FILES['avatar_file']['size'] <= 2000000) { 
                $new_filename = $current_user . '_' . time() . '.' . $file_ext;
                $target = UPLOAD_DIR . $new_filename;
                if (move_uploaded_file($_FILES['avatar_file']['tmp_name'], $target)) {
                    if ($user['avatar'] && file_exists($user['avatar'])) { unlink($user['avatar']); }
                    $avatar_path = $target;
                } else {
                    $message = $t['msg_upload_fail']; $msg_type = "error"; $upload_ok = false;
                }
            } else { $message = $t['msg_file_size']; $msg_type = "error"; $upload_ok = false; }
        } else { $message = $t['msg_file_format']; $msg_type = "error"; $upload_ok = false; }
    }

    if ($upload_ok) {
        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $message = $t['msg_email_invalid']; $msg_type = "error";
        } else {
            $update = $conn->prepare("UPDATE users SET email = ?, avatar = ? WHERE username = ?");
            $update->bind_param("sss", $new_email, $avatar_path, $current_user);
            if ($update->execute()) {
                $message = $t['msg_prof_success']; $msg_type = "success";
                $user['email'] = $new_email;
                $user['avatar'] = $avatar_path;
            } else { $message = $t['msg_db_fail']; $msg_type = "error"; }
        }
    }
}

if (isset($_POST['save_preferences'])) {
    $prefs = ['size' => $_POST['sub_size'], 'color' => $_POST['sub_color']];
    $json_prefs = json_encode($prefs);
    $stmt = $conn->prepare("UPDATE users SET subtitle_prefs = ? WHERE username = ?");
    $stmt->bind_param("ss", $json_prefs, $current_user);
    if($stmt->execute()){
        $message = $t['msg_pref_success']; $msg_type = "success";
        $subs_pref = $prefs;
    }
}

if (isset($_POST['send_feedback'])) {
    $subject = htmlspecialchars($_POST['subject']);
    $msg_body = htmlspecialchars($_POST['message']);
    $stmt = $conn->prepare("INSERT INTO feedback (username, subject, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $current_user, $subject, $msg_body);
    if($stmt->execute()){
        $message = $t['msg_feedback_success']; $msg_type = "success";
    } else { $message = $t['msg_feedback_fail']; $msg_type = "error"; }
}

if (isset($_POST['change_password'])) {
    $old = $_POST['old_pass']; $new = $_POST['new_pass']; $confirm = $_POST['confirm_pass'];
    if (password_verify($old, $user['password'])) {
        if ($new === $confirm) {
            if (strlen($new) >= 6) { 
                $hash = password_hash($new, PASSWORD_DEFAULT);
                $conn->query("UPDATE users SET password = '$hash' WHERE username = '$current_user'");
                $message = $t['msg_pass_success']; $msg_type = "success";
            } else { $message = $t['msg_pass_min']; $msg_type = "error"; }
        } else { $message = $t['msg_pass_mismatch']; $msg_type = "error"; }
    } else { $message = $t['msg_pass_wrong']; $msg_type = "error"; }
}

if (isset($_POST['clear_watchlist'])) {
    $conn->query("DELETE FROM watchlist WHERE username = '$current_user'");
    $message = $t['msg_list_cleared']; $msg_type = "success";
}
if (isset($_POST['clear_history'])) {
    $conn->query("DELETE FROM watch_history WHERE username = '$current_user'");
    $message = $t['msg_hist_cleared']; $msg_type = "success";
}
if (isset($_POST['delete_account'])) {
    $verif_pass = $_POST['del_password'];
    if (password_verify($verif_pass, $user['password'])) {
        $conn->query("DELETE FROM watchlist WHERE username = '$current_user'");
        $conn->query("DELETE FROM watch_history WHERE username = '$current_user'");
        $conn->query("DELETE FROM users WHERE username = '$current_user'");
        session_destroy(); header("Location: login.php?msg=account_deleted"); exit;
    } else { $message = $t['msg_pass_wrong']; $msg_type = "error"; }
}

$q_list = $conn->query("SELECT m.id, m.title, m.image, w.created_at FROM watchlist w JOIN movies m ON w.movie_id = m.id WHERE w.username = '$current_user' ORDER BY w.created_at DESC");
$q_hist = $conn->query("SELECT m.id, m.title, m.image, h.watched_at FROM watch_history h JOIN movies m ON h.movie_id = m.id WHERE h.username = '$current_user' ORDER BY h.watched_at DESC");

$initial = strtoupper(substr($user['username'], 0, 1));
$has_avatar = !empty($user['avatar']) && file_exists($user['avatar']);
?>

<!DOCTYPE html>
<html lang="<?= $l ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - MOVDO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-body: #020617; --sidebar-bg: rgba(15, 23, 42, 0.95); --card-bg: rgba(30, 41, 59, 0.4);
            --primary: #3b82f6; --accent: #06b6d4; --text-main: #f8fafc; --text-muted: #94a3b8;
            --border: rgba(255,255,255,0.08); --gradient-main: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%);
            --glass-backdrop: blur(12px); --radius: 16px; --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { box-sizing: border-box; outline: none; }
        
        /* FIX SCROLL: BODY HIDDEN, MAIN CONTENT AUTO SCROLL */
        body { 
            background-color: var(--bg-body); color: var(--text-main); font-family: 'Plus Jakarta Sans', sans-serif; 
            margin: 0; display: flex; height: 100vh; overflow: hidden; 
        }
        body::before { content: ''; position: absolute; width: 500px; height: 500px; background: radial-gradient(circle, rgba(59,130,246,0.15) 0%, rgba(2,6,23,0) 70%); top: -100px; left: -100px; z-index: -1; pointer-events: none; }

        .sidebar { width: 280px; background: var(--sidebar-bg); border-right: 1px solid var(--border); padding: 30px; display: flex; flex-direction: column; z-index: 10; }
        .brand { font-size: 26px; font-weight: 800; background: var(--gradient-main); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 50px; display: block; text-decoration: none; }
        .nav-menu { display: flex; flex-direction: column; gap: 8px; }
        .nav-item { padding: 12px 16px; border-radius: 10px; color: var(--text-muted); cursor: pointer; transition: var(--transition); font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .nav-item:hover, .nav-item.active { background: rgba(59, 130, 246, 0.1); color: #fff; }
        .nav-item.active { border-left: 3px solid var(--primary); }
        .nav-item.logout { color: #fca5a5; margin-top: auto; }
        .nav-item.logout:hover { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .nav-divider { height: 1px; background: var(--border); margin: 20px 0; }

        /* SCROLLABLE AREA */
        .main-content { 
            flex: 1; 
            padding: 40px 60px; 
            overflow-y: auto; /* Kunci scrolling ada disini */
            position: relative; 
            scroll-behavior: smooth;
        }

        /* TOMBOL SCROLL */
        .scroll-btn {
            position: fixed;
            bottom: 30px;
            right: 40px;
            width: 50px;
            height: 50px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(0,0,0,0.5);
            z-index: 9999;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
            transition: 0.3s;
            opacity: 0.9;
        }
        .scroll-btn:hover { transform: scale(1.1); opacity: 1; background: var(--accent); }

        .card { background: var(--card-bg); backdrop-filter: var(--glass-backdrop); border: 1px solid var(--border); border-radius: var(--radius); padding: 30px; margin-bottom: 25px; }
        .form-grid { display: grid; gap: 20px; max-width: 500px; }
        label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: var(--text-muted); }
        input, select, textarea { width: 100%; padding: 12px 15px; background: rgba(2, 6, 23, 0.6); border: 1px solid var(--border); border-radius: 10px; color: #fff; font-family: inherit; font-size: 14px; transition: var(--transition); }
        input:focus, select:focus, textarea:focus { border-color: var(--primary); box-shadow: 0 0 15px rgba(59, 130, 246, 0.15); }
        textarea { resize: vertical; min-height: 100px; }
        
        .btn { padding: 12px 24px; border-radius: 10px; border: none; font-weight: 600; font-size: 14px; cursor: pointer; transition: var(--transition); display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary { background: var(--gradient-main); color: white; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(59, 130, 246, 0.5); }
        .btn-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }
        .btn-danger:hover { background: #ef4444; color: white; }
        .btn-text { background: none; color: var(--text-muted); padding: 0; }
        .btn-text:hover { color: white; text-decoration: underline; }

        .profile-header { display: flex; align-items: center; gap: 25px; padding-bottom: 25px; border-bottom: 1px solid var(--border); margin-bottom: 25px; }
        .avatar-container { position: relative; width: 100px; height: 100px; cursor: pointer; group; }
        .avatar-circle { width: 100%; height: 100%; background: var(--gradient-main); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 36px; font-weight: 800; color: white; box-shadow: 0 0 20px rgba(59, 130, 246, 0.4); overflow: hidden; object-fit: cover; border: 3px solid var(--card-bg); }
        .avatar-circle img { width: 100%; height: 100%; object-fit: cover; }
        .avatar-edit-icon { position: absolute; bottom: 0; right: 0; background: var(--primary); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 14px; border: 3px solid var(--card-bg); transition: var(--transition); }
        .avatar-container:hover .avatar-edit-icon { transform: scale(1.1); }

        .badge { font-size: 11px; padding: 5px 10px; border-radius: 20px; font-weight: 700; text-transform: uppercase; background: rgba(255,255,255,0.1); color: var(--text-muted); border: 1px solid var(--border); }
        .badge.premium { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; border: none; }

        .movie-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 20px; }
        .movie-card { position: relative; border-radius: 12px; overflow: hidden; aspect-ratio: 2/3; cursor: pointer; transition: var(--transition); }
        .movie-card img { width: 100%; height: 100%; object-fit: cover; }
        .movie-overlay { position: absolute; inset: 0; background: linear-gradient(to top, rgba(2,6,23,0.9), transparent); display: flex; flex-direction: column; justify-content: flex-end; padding: 15px; opacity: 0; transition: var(--transition); }
        .movie-card:hover { transform: scale(1.03); }
        .movie-card:hover .movie-overlay { opacity: 1; }
        .movie-title { font-size: 14px; font-weight: 600; margin-bottom: 4px; color: white; }

        .subtitle-preview-box { width: 100%; height: 150px; background-image: url('https://w0.peakpx.com/wallpaper/237/264/HD-wallpaper-stranger-things-logo-4k-stranger-things.jpg'); background-size: cover; background-position: center; border-radius: 12px; display: flex; align-items: flex-end; justify-content: center; padding-bottom: 20px; margin-bottom: 20px; position: relative; }
        .subtitle-text { background: rgba(0,0,0,0.5); padding: 4px 10px; border-radius: 4px; }
        
        .preference-item { display: flex; justify-content: space-between; align-items: center; padding: 20px 0; border-bottom: 1px solid var(--border); }
        .switch { position: relative; display: inline-block; width: 44px; height: 24px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; inset: 0; background-color: rgba(255,255,255,0.1); transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: var(--primary); }
        input:checked + .slider:before { transform: translateX(20px); }

        .alert-box { position: fixed; top: 20px; right: 20px; z-index: 100; padding: 16px 20px; border-radius: 12px; display: flex; align-items: center; gap: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); animation: slideIn 0.5s; backdrop-filter: blur(10px); font-size: 14px; font-weight: 600; }
        .alert-success { background: rgba(34, 197, 94, 0.2); color: #86efac; border: 1px solid rgba(34, 197, 94, 0.3); }
        .alert-error { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

        .tab-content { display: none; padding-bottom: 80px; }
        .tab-content.active { display: block; animation: fadeIn 0.4s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        @media (max-width: 768px) {
            body { flex-direction: column; overflow: auto; height: auto; }
            .sidebar { width: 100%; padding: 15px; flex-direction: row; overflow-x: auto; position: sticky; top: 0; background: #0f172a; }
            .brand, .nav-divider { display: none; }
            .main-content { padding: 20px; overflow-y: visible; }
            .scroll-btn { bottom: 20px; right: 20px; width: 45px; height: 45px; }
        }
    </style>
</head>
<body>

    <?php if($message): ?>
    <div class="alert-box <?= ($msg_type == 'success') ? 'alert-success' : 'alert-error' ?>" id="alert">
        <i class="fas <?= ($msg_type == 'success') ? 'fa-check-circle' : 'fa-exclamation-triangle' ?>"></i>
        <span><?= $message ?></span>
        <i class="fas fa-times" style="margin-left:auto; cursor:pointer; opacity:0.7;" onclick="document.getElementById('alert').remove()"></i>
    </div>
    <script>setTimeout(() => { document.getElementById('alert').style.opacity='0'; setTimeout(()=>document.getElementById('alert').remove(), 500); }, 4000);</script>
    <?php endif; ?>

    <nav class="sidebar">
        <a href="index.php" class="brand">MOVDO</a>
        <div class="nav-menu">
            <div class="nav-item active" onclick="openTab('account', this)"><i class="fas fa-user-circle"></i> <?= $t['nav_account'] ?></div>
            <div class="nav-item" onclick="openTab('preferences', this)"><i class="fas fa-sliders-h"></i> <?= $t['nav_prefs'] ?></div>
            <div class="nav-item" onclick="openTab('mylist', this)"><i class="fas fa-bookmark"></i> <?= $t['nav_list'] ?></div>
            <div class="nav-item" onclick="openTab('history', this)"><i class="fas fa-history"></i> <?= $t['nav_hist'] ?></div>
            <div class="nav-item" onclick="openTab('support', this)"><i class="fas fa-headset"></i> <?= $t['nav_help'] ?></div>
            <div class="nav-item" onclick="openTab('security', this)"><i class="fas fa-shield-alt"></i> <?= $t['nav_sec'] ?></div>
        </div>
        <div class="nav-divider"></div>
        <div class="nav-menu">
            <a href="index.php" class="nav-item"><i class="fas fa-arrow-left"></i> <?= $t['nav_back'] ?></a>
            <a href="settings.php?action=logout" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> <?= $t['nav_logout'] ?></a>
        </div>
    </nav>

    <main class="main-content" id="mainScrollContainer">
        
        <button id="smartScrollBtn" class="scroll-btn">
            <i class="fas fa-arrow-down"></i>
        </button>

        <div id="account" class="tab-content active">
            <div class="header-area"><h1 class="page-title"><?= $t['tab_acc_title'] ?></h1></div>
            <div class="card">
                <form method="POST" enctype="multipart/form-data" class="form-grid">
                    <div class="profile-header">
                        <div class="avatar-container" onclick="document.getElementById('avatarInput').click()">
                            <div class="avatar-circle">
                                <?php if($has_avatar): ?>
                                    <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar">
                                <?php else: ?>
                                    <?= $initial ?>
                                <?php endif; ?>
                            </div>
                            <div class="avatar-edit-icon"><i class="fas fa-camera"></i></div>
                            <input type="file" name="avatar_file" id="avatarInput" style="display:none;" accept="image/*" onchange="previewAvatar(this)">
                        </div>
                        <div class="profile-meta">
                            <h3 style="margin: 0 0 5px 0; font-size: 20px; color:white;"><?= htmlspecialchars($user['username']) ?></h3>
                            <span class="badge <?= ($user['subscription_type'] == 'Premium') ? 'premium' : '' ?>">
                                <?= $user['subscription_type'] ?> Plan
                            </span>
                            <div style="font-size:12px; color:var(--text-muted); margin-top:5px;"><?= $t['lbl_member_since'] ?> <?= date('M Y', strtotime($user['created_at'] ?? 'now')) ?></div>
                        </div>
                        <?php if($user['subscription_type'] == 'Free'): ?>
                            <a href="payment.php" class="btn btn-primary" style="margin-left:auto;"><?= $t['btn_upgrade'] ?></a>
                        <?php endif; ?>
                    </div>
                    <div><label><?= $t['lbl_username'] ?></label><input type="text" value="<?= htmlspecialchars($user['username']) ?>" readonly style="cursor:not-allowed; opacity:0.7;"></div>
                    <div><label><?= $t['lbl_email'] ?></label><input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required></div>
                    <button type="submit" name="update_profile" class="btn btn-primary"><i class="fas fa-save"></i> <?= $t['btn_save_profile'] ?></button>
                </form>
            </div>
        </div>

        <div id="preferences" class="tab-content">
            <div class="header-area"><h1 class="page-title"><?= $t['tab_pref_title'] ?></h1></div>
            <div class="card">
                <h3 style="margin-top:0; color:white;"><?= $t['sect_sub'] ?></h3>
                <p style="color:var(--text-muted); font-size:13px; margin-bottom:20px;"><?= $t['sect_sub_desc'] ?></p>
                <div class="subtitle-preview-box">
                    <div id="subPreviewText" class="subtitle-text" style="color: <?= $subs_pref['color'] ?>; font-size: <?= ($subs_pref['size'] == 'Small' ? '12px' : ($subs_pref['size'] == 'Large' ? '20px' : '16px')) ?>;"><?= $t['sub_prev_text'] ?></div>
                </div>
                <form method="POST" class="form-grid" style="grid-template-columns: 1fr 1fr;">
                    <div><label><?= $t['lbl_sub_size'] ?></label><select name="sub_size" id="subSize" onchange="updatePreview()"><option value="Small" <?= ($subs_pref['size'] == 'Small') ? 'selected' : '' ?>><?= $t['opt_small'] ?></option><option value="Normal" <?= ($subs_pref['size'] == 'Normal') ? 'selected' : '' ?>><?= $t['opt_normal'] ?></option><option value="Large" <?= ($subs_pref['size'] == 'Large') ? 'selected' : '' ?>><?= $t['opt_large'] ?></option></select></div>
                    <div><label><?= $t['lbl_sub_color'] ?></label><select name="sub_color" id="subColor" onchange="updatePreview()"><option value="White" <?= ($subs_pref['color'] == 'White') ? 'selected' : '' ?>><?= $t['opt_white'] ?></option><option value="Yellow" <?= ($subs_pref['color'] == 'Yellow') ? 'selected' : '' ?>><?= $t['opt_yellow'] ?></option><option value="#3b82f6" <?= ($subs_pref['color'] == '#3b82f6') ? 'selected' : '' ?>><?= $t['opt_blue'] ?></option></select></div>
                    <button type="submit" name="save_preferences" class="btn btn-primary" style="grid-column: span 2;"><?= $t['btn_save_pref'] ?></button>
                </form>
            </div>
            <div class="card">
                <div class="preference-item"><div><div style="font-weight:600; color:white;"><?= $t['lbl_autoplay'] ?></div><div style="font-size:12px; color:var(--text-muted);"><?= $t['desc_autoplay'] ?></div></div><label class="switch"><input type="checkbox" checked><span class="slider"></span></label></div>
                <div class="preference-item" style="border-bottom:none;"><div><div style="font-weight:600; color:white;"><?= $t['lbl_saver'] ?></div><div style="font-size:12px; color:var(--text-muted);"><?= $t['desc_saver'] ?></div></div><label class="switch"><input type="checkbox"><span class="slider"></span></label></div>
            </div>
        </div>

        <div id="mylist" class="tab-content">
            <div class="header-area"><h1 class="page-title"><?= $t['tab_list_title'] ?></h1><?php if($q_list->num_rows > 0): ?><form method="POST" onsubmit="return confirm('<?= $t['js_confirm_clear'] ?>');"><button type="submit" name="clear_watchlist" class="btn btn-text"><?= $t['btn_clear_all'] ?></button></form><?php endif; ?></div>
            <?php if($q_list->num_rows > 0): ?><div class="movie-grid"><?php while($row = $q_list->fetch_assoc()): ?><div class="movie-card" onclick="window.location='watch.php?id=<?= $row['id'] ?>'"><img src="<?= htmlspecialchars($row['image']) ?>" alt="Movie"><div class="movie-overlay"><div class="movie-title"><?= htmlspecialchars($row['title']) ?></div><div class="movie-date">Added: <?= date('d M', strtotime($row['created_at'])) ?></div></div></div><?php endwhile; ?></div><?php else: ?><div class="card" style="text-align:center; padding:50px;"><i class="far fa-bookmark" style="font-size:40px; color:var(--text-muted); margin-bottom:15px;"></i><p style="color:var(--text-muted);"><?= $t['empty_list'] ?></p></div><?php endif; ?>
        </div>

        <div id="history" class="tab-content">
            <div class="header-area"><h1 class="page-title"><?= $t['tab_hist_title'] ?></h1><?php if($q_hist->num_rows > 0): ?><form method="POST" onsubmit="return confirm('<?= $t['js_confirm_hist'] ?>');"><button type="submit" name="clear_history" class="btn btn-text"><?= $t['btn_clear'] ?></button></form><?php endif; ?></div>
            <?php if($q_hist->num_rows > 0): ?><div class="movie-grid"><?php while($row = $q_hist->fetch_assoc()): ?><div class="movie-card" onclick="window.location='watch.php?id=<?= $row['id'] ?>'"><img src="<?= htmlspecialchars($row['image']) ?>" alt="Movie"><div class="movie-overlay"><div class="movie-title"><?= htmlspecialchars($row['title']) ?></div><div class="movie-date"><?= date('d M H:i', strtotime($row['watched_at'])) ?></div><div style="height:3px; background:rgba(255,255,255,0.2); width:100%; margin-top:10px;"><div style="height:100%; width:85%; background:var(--primary);"></div></div></div></div><?php endwhile; ?></div><?php else: ?><div class="card" style="text-align:center; padding:50px;"><i class="fas fa-history" style="font-size:40px; color:var(--text-muted); margin-bottom:15px;"></i><p style="color:var(--text-muted);"><?= $t['empty_hist'] ?></p></div><?php endif; ?>
        </div>

        <div id="support" class="tab-content">
            <div class="header-area"><h1 class="page-title"><?= $t['tab_help_title'] ?></h1></div>
            <div class="card">
                <h3 style="margin-top:0; color:white;"><?= $t['sect_contact'] ?></h3><p style="color:var(--text-muted); font-size:13px;"><?= $t['sect_contact_desc'] ?></p>
                <form method="POST" class="form-grid"><div><label><?= $t['lbl_subject'] ?></label><select name="subject"><option>Request Film / Series</option><option>Laporan Error / Bug</option><option>Masalah Pembayaran</option><option>Lainnya</option></select></div><div><label><?= $t['lbl_msg'] ?></label><textarea name="message" placeholder="<?= $t['ph_msg'] ?>" required></textarea></div><button type="submit" name="send_feedback" class="btn btn-primary"><i class="fas fa-paper-plane"></i> <?= $t['btn_send'] ?></button></form>
            </div>
            <div class="card"><h4 style="color:white; margin-top:0;"><?= $t['sect_faq'] ?></h4><details style="margin-bottom:10px; border-bottom:1px solid var(--border); padding-bottom:10px;"><summary style="cursor:pointer; font-weight:600; color:var(--accent);"><?= $t['faq_1_q'] ?></summary><p style="font-size:13px; color:var(--text-muted); margin-top:5px;"><?= $t['faq_1_a'] ?></p></details><details style="border-bottom:1px solid var(--border); padding-bottom:10px;"><summary style="cursor:pointer; font-weight:600; color:var(--accent);"><?= $t['faq_2_q'] ?></summary><p style="font-size:13px; color:var(--text-muted); margin-top:5px;"><?= $t['faq_2_a'] ?></p></details></div>
        </div>

        <div id="security" class="tab-content">
            <div class="header-area"><h1 class="page-title"><?= $t['tab_sec_title'] ?></h1></div>
            <div class="card">
                <h3 style="margin-top:0; color:white;"><?= $t['sect_pass'] ?></h3>
                <form method="POST" class="form-grid"><div><label><?= $t['lbl_old_pass'] ?></label><input type="password" name="old_pass" required></div><div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;"><div><label><?= $t['lbl_new_pass'] ?></label><input type="password" name="new_pass" required></div><div><label><?= $t['lbl_confirm_pass'] ?></label><input type="password" name="confirm_pass" required></div></div><button type="submit" name="change_password" class="btn btn-primary"><?= $t['btn_update_pass'] ?></button></form>
            </div>
            <div class="card">
                <h3 style="margin-top:0; color:white;"><?= $t['sect_device'] ?></h3><p style="font-size:13px; color:var(--text-muted); margin-bottom:20px;"><?= $t['sect_device_desc'] ?></p>
                <div style="display:flex; align-items:center; gap:15px; padding:15px; background:rgba(255,255,255,0.03); border-radius:10px; margin-bottom:10px;"><i class="fas fa-desktop" style="font-size:20px; color:var(--primary);"></i><div style="flex:1;"><div style="font-weight:600; font-size:14px; color:white;">Chrome di Windows (<?= $t['dev_current'] ?>)</div><div style="font-size:12px; color:var(--text-muted);">Tasikmalaya, ID • Aktif sekarang</div></div><div style="font-size:12px; color:#86efac;"><?= $t['dev_online'] ?></div></div>
            </div>
            <div class="card" style="border: 1px solid rgba(239, 68, 68, 0.3); background: rgba(239,68,68,0.02);"><h3 style="color:#ef4444; margin-top:0;"><?= $t['sect_danger'] ?></h3><p style="font-size:13px; color:var(--text-muted);"><?= $t['desc_danger'] ?></p><form method="POST" class="form-grid" style="margin-top:15px;"><div><input type="password" name="del_password" placeholder="<?= $t['ph_del_pass'] ?>" required style="border-color:rgba(239,68,68,0.3);"></div><button type="submit" name="delete_account" class="btn btn-danger" onclick="return confirm('<?= $t['js_del_confirm'] ?>')"><i class="fas fa-trash-alt"></i> <?= $t['btn_del_acc'] ?></button></form></div>
        </div>

    </main>

    <script>
        function openTab(tabId, element) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
            if(element) element.classList.add('active');
            // Reset scroll ketika pindah tab
            const cont = document.getElementById('mainScrollContainer');
            if(cont) cont.scrollTop = 0;
        }

        function updatePreview() {
            const size = document.getElementById('subSize').value;
            const color = document.getElementById('subColor').value;
            const text = document.getElementById('subPreviewText');
            text.style.color = color;
            if(size === 'Small') text.style.fontSize = '12px';
            else if(size === 'Large') text.style.fontSize = '20px';
            else text.style.fontSize = '16px';
        }

        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('.avatar-circle').innerHTML = '<img src="'+e.target.result+'" style="width:100%; height:100%; object-fit:cover;">';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // --- SCROLL BUTTON LOGIC (FIXED) ---
        const container = document.getElementById('mainScrollContainer');
        const btn = document.getElementById('smartScrollBtn');
        const icon = btn.querySelector('i');

        // Logic Saat Di-Klik
        btn.addEventListener('click', () => {
            const isAtBottom = container.scrollTop + container.clientHeight >= container.scrollHeight - 50;
            if (isAtBottom) {
                // Scroll ke Atas
                container.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                // Scroll ke Bawah
                container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
            }
        });

        // Logic Saat User Scroll Manual (Ubah Icon)
        container.addEventListener('scroll', () => {
            const isAtBottom = container.scrollTop + container.clientHeight >= container.scrollHeight - 50;
            if (isAtBottom) {
                icon.className = 'fas fa-arrow-up';
            } else {
                icon.className = 'fas fa-arrow-down';
            }
        });
    </script>
</body>
</html>