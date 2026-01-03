<?php
session_start();
require 'db.php';

// --- 1. PENGATURAN BAHASA (LANGUAGE LOGIC) ---
$l = $_SESSION['lang'] ?? 'id'; 

$trans = [
    'id' => [
        // Summary Side
        'brand_sub' => 'VIP',
        'title_main' => 'Upgrade Pengalaman<br>Menonton Anda.',
        'desc_main' => 'Hanya satu harga untuk akses tak terbatas ke semua koleksi film premium tanpa gangguan.',
        'feat_1' => 'Akses Film Exclusive & Premiere',
        'feat_2' => 'Nonton Nyaman Tanpa Iklan',
        'feat_3' => 'Kualitas 4K HDR + Dolby Audio',
        'feat_4' => 'Download Sepuasnya (Offline)',
        'btn_back' => 'Kembali ke Dashboard',

        // Form Side
        'step_1' => '1. Pilih Paket Langganan',
        'step_2' => '2. Metode Pembayaran',
        
        // Plans
        'plan_mini' => 'Mini / Trial',
        'dur_3days' => 'Aktif 3 Hari',
        'plan_month' => 'Bulanan',
        'dur_30days' => 'Aktif 30 Hari',
        'badge_pop' => 'POPULER',
        'plan_year' => 'Tahunan',
        'dur_12months' => 'Aktif 12 Bulan',
        'badge_save' => 'HEMAT 50%',
        'plan_life' => 'Lifetime',
        'dur_forever' => 'Selamanya',
        'badge_sultan' => 'SULTAN',

        // Methods & Button
        'method_bank' => 'Transfer Bank',
        'method_manual' => 'Verifikasi Manual',
        'btn_pay_prefix' => 'Bayar', // Teks sebelum harga
        'txt_secure' => 'SSL Secured',
        'txt_cancel' => 'Cancel Anytime',

        // JavaScript Alerts
        'js_confirm' => 'Lanjutkan pembayaran?',
        'js_processing' => 'Memproses Transaksi...'
    ],
    'en' => [
        // Summary Side
        'brand_sub' => 'VIP',
        'title_main' => 'Upgrade Your<br>Watching Experience.',
        'desc_main' => 'One price for unlimited access to all premium movie collections without interruption.',
        'feat_1' => 'Exclusive & Premiere Movie Access',
        'feat_2' => 'Comfortable Ad-Free Viewing',
        'feat_3' => '4K HDR Quality + Dolby Audio',
        'feat_4' => 'Unlimited Downloads (Offline)',
        'btn_back' => 'Back to Dashboard',

        // Form Side
        'step_1' => '1. Choose Subscription Plan',
        'step_2' => '2. Payment Method',
        
        // Plans
        'plan_mini' => 'Mini / Trial',
        'dur_3days' => 'Active 3 Days',
        'plan_month' => 'Monthly',
        'dur_30days' => 'Active 30 Days',
        'badge_pop' => 'POPULAR',
        'plan_year' => 'Yearly',
        'dur_12months' => 'Active 12 Months',
        'badge_save' => 'SAVE 50%',
        'plan_life' => 'Lifetime',
        'dur_forever' => 'Forever',
        'badge_sultan' => 'ULTIMATE',

        // Methods & Button
        'method_bank' => 'Bank Transfer',
        'method_manual' => 'Manual Verification',
        'btn_pay_prefix' => 'Pay', // Text before price
        'txt_secure' => 'SSL Secured',
        'txt_cancel' => 'Cancel Anytime',

        // JavaScript Alerts
        'js_confirm' => 'Proceed with payment?',
        'js_processing' => 'Processing Transaction...'
    ]
];

$t = $trans[$l]; // Variabel pintas

// Cek Login
if (!isset($_SESSION['user_logged_in'])) {
    header("Location: login.php");
    exit;
}

// LOGIKA PROSES PEMBAYARAN
if (isset($_POST['pay_now'])) {
    $current_user = $_SESSION['username'];
    $paket = $_POST['selected_package']; 
    
    // Hitung tanggal kadaluarsa berdasarkan paket baru
    $days_to_add = 0;
    if ($paket == 'mini') { $days_to_add = 3; }        // 3 Hari
    elseif ($paket == 'month') { $days_to_add = 30; }  // 1 Bulan
    elseif ($paket == 'year') { $days_to_add = 365; }  // 1 Tahun
    elseif ($paket == 'life') { $days_to_add = 36500; }// Lifetime (100 Tahun)

    // Buat format tanggal: Hari ini + jumlah hari paket
    $expire_date = date('Y-m-d H:i:s', strtotime("+$days_to_add days"));

    // Update Database
    $stmt = $conn->prepare("UPDATE users SET subscription_type = 'Premium', subscription_end = ? WHERE username = ?");
    $stmt->bind_param("ss", $expire_date, $current_user);
    
    if ($stmt->execute()) {
        header("Location: index.php?status=success");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="<?= $l ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upgrade Premium - MOVDO</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* --- THEME VARIABLES --- */
        :root {
            --bg-body: #020617;
            --card-bg: rgba(30, 41, 59, 0.6);
            --sidebar-bg: rgba(15, 23, 42, 0.95);
            --primary: #3b82f6;
            --accent: #06b6d4;
            --gold: #fbbf24;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border: rgba(255,255,255,0.08);
            --gradient-main: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%);
            --gradient-gold: linear-gradient(135deg, #fbbf24 0%, #d97706 100%);
            --glass-backdrop: blur(20px);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { box-sizing: border-box; outline: none; }
        
        body { 
            background-color: var(--bg-body); 
            color: var(--text-main); 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            margin: 0; 
            display: flex; justify-content: center; align-items: center; 
            min-height: 100vh; overflow-x: hidden;
        }

        /* Background Visuals */
        body::before {
            content: ''; position: absolute;
            width: 80vw; height: 80vh;
            background: radial-gradient(circle, rgba(59,130,246,0.1) 0%, rgba(2,6,23,0) 60%);
            top: 50%; left: 50%; transform: translate(-50%, -50%);
            z-index: -1; pointer-events: none;
        }

        /* --- CONTAINER --- */
        .pay-container {
            background: var(--card-bg);
            backdrop-filter: var(--glass-backdrop);
            border: 1px solid var(--border);
            width: 1000px; max-width: 95%;
            display: flex;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            min-height: 600px;
            animation: slideUp 0.6s cubic-bezier(0.2, 0.8, 0.2, 1);
        }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

        /* --- LEFT SIDE (INFO) --- */
        .summary { 
            flex: 0.7; 
            background: var(--sidebar-bg); 
            padding: 50px 40px; 
            border-right: 1px solid var(--border); 
            display: flex; flex-direction: column; justify-content: space-between;
            position: relative; overflow: hidden;
        }
        
        /* Hiasan latar belakang kiri */
        .summary::after {
            content: ''; position: absolute; top: -50px; left: -50px;
            width: 200px; height: 200px;
            background: var(--primary); filter: blur(120px); opacity: 0.15;
            z-index: 0;
        }

        .brand-area { position: relative; z-index: 2; }
        .brand {
            font-size: 28px; font-weight: 800;
            background: var(--gradient-main); -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            margin-bottom: 15px; display: inline-block; letter-spacing: -1px;
        }
        
        .summary h2 { margin: 0 0 15px 0; font-size: 32px; line-height: 1.1; color: white; letter-spacing: -0.5px;}
        .summary p { color: var(--text-muted); font-size: 15px; line-height: 1.6; margin-bottom: 30px; }

        .feature-list { list-style: none; padding: 0; }
        .feature-list li { 
            margin-bottom: 20px; display: flex; align-items: start; gap: 15px; 
            font-size: 15px; color: #e2e8f0; font-weight: 500;
        }
        .feature-list i { 
            color: #22d3ee; background: rgba(34, 211, 238, 0.1); 
            min-width: 28px; height: 28px; border-radius: 8px; 
            display: flex; align-items: center; justify-content: center; font-size: 14px;
            margin-top: -2px;
        }

        .back-btn { 
            color: var(--text-muted); text-decoration: none; font-size: 14px; 
            transition: var(--transition); display: flex; align-items: center; gap: 8px; font-weight: 600;
            position: relative; z-index: 2; margin-top: auto;
        }
        .back-btn:hover { color: white; transform: translateX(-3px); }

        /* --- RIGHT SIDE (FORM) --- */
        .payment { flex: 1.3; padding: 50px 40px; overflow-y: auto; display: flex; flex-direction: column; }
        
        .section-header { margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; }
        .step-label { 
            font-size: 12px; font-weight: 700; color: var(--accent); 
            text-transform: uppercase; letter-spacing: 1.5px;
        }

        /* --- PLAN CARDS GRID --- */
        .plans-grid { 
            display: grid; 
            grid-template-columns: repeat(2, 1fr); 
            gap: 15px; 
            margin-bottom: 40px; 
        }
        
        .plan-card {
            background: rgba(15, 23, 42, 0.6); 
            border: 2px solid transparent; 
            padding: 20px; border-radius: 16px;
            cursor: pointer; transition: var(--transition); position: relative;
            display: flex; flex-direction: column; gap: 10px;
            overflow: hidden;
        }
        
        /* Border effect */
        .plan-card::before {
            content: ''; position: absolute; inset: 0; 
            border-radius: 16px; padding: 2px; 
            background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05)); 
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0); 
            -webkit-mask-composite: xor; mask-composite: exclude; pointer-events: none;
        }

        .plan-card:hover { transform: translateY(-4px); background: rgba(30, 41, 59, 0.8); }
        
        /* Active State */
        .plan-card.active { 
            background: rgba(59, 130, 246, 0.15); 
            border-color: var(--primary);
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.2);
        }
        .plan-card.active .plan-icon { background: var(--gradient-main); color: white; border: none; }

        /* Card Content */
        .card-top { display: flex; justify-content: space-between; align-items: flex-start; }
        .plan-icon {
            width: 36px; height: 36px; border-radius: 10px;
            background: rgba(255,255,255,0.05); color: var(--text-muted);
            display: flex; align-items: center; justify-content: center; font-size: 16px;
            transition: var(--transition);
        }
        
        .plan-name { font-size: 14px; color: var(--text-muted); font-weight: 600; }
        .plan-duration { font-size: 12px; opacity: 0.7; }
        .plan-price { font-size: 18px; font-weight: 700; color: white; margin-top: 5px; }
        
        /* Badges */
        .badge { 
            position: absolute; top: 0; right: 0;
            font-size: 10px; padding: 4px 10px; border-bottom-left-radius: 12px; font-weight: 700;
        }
        .badge.pop { background: var(--gradient-main); color: white; }
        .badge.gold { background: var(--gradient-gold); color: black; }

        /* --- PAYMENT METHODS --- */
        .methods { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        
        .method-item { 
            display: flex; align-items: center; gap: 15px;
            background: rgba(2, 6, 23, 0.4); 
            padding: 15px; border-radius: 12px; cursor: pointer; 
            border: 1px solid var(--border); transition: var(--transition);
        }
        .method-item:hover { border-color: var(--text-muted); background: rgba(255,255,255,0.05); }
        .method-item.active { border-color: var(--accent); background: rgba(6, 182, 212, 0.1); }
        
        .method-item input[type="radio"] { 
            appearance: none; width: 16px; height: 16px; 
            border: 2px solid var(--text-muted); border-radius: 50%; 
            position: relative; cursor: pointer; flex-shrink: 0;
        }
        .method-item input[type="radio"]:checked { border-color: var(--accent); border-width: 5px; }

        /* --- BUTTON --- */
        .btn-confirm { 
            background: var(--gradient-main); color: white; 
            border: none; width: 100%; padding: 18px; font-size: 16px; font-weight: 700; 
            border-radius: 14px; margin-top: 30px; cursor: pointer; transition: var(--transition); 
            box-shadow: 0 4px 20px rgba(59, 130, 246, 0.4);
            display: flex; justify-content: center; align-items: center; gap: 10px;
        }
        .btn-confirm:hover { transform: translateY(-2px); box-shadow: 0 10px 40px rgba(59, 130, 246, 0.6); }

        /* Responsive */
        @media (max-width: 850px) {
            .pay-container { flex-direction: column; width: 100%; border-radius: 0; min-height: 100vh; }
            .summary { padding: 30px 20px; border-right: none; border-bottom: 1px solid var(--border); }
            .payment { padding: 30px 20px; }
            .plans-grid { grid-template-columns: 1fr 1fr; } 
            .methods { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="pay-container">
    <div class="summary">
        <div class="brand-area">
            <div class="brand">MOVDO <span style="font-weight:300; color: white;"><?= $t['brand_sub'] ?></span></div>
            <h2><?= $t['title_main'] ?></h2>
            <p><?= $t['desc_main'] ?></p>
            
            <ul class="feature-list">
                <li><i class="fas fa-gem"></i> <span><?= $t['feat_1'] ?></span></li>
                <li><i class="fas fa-ban"></i> <span><?= $t['feat_2'] ?></span></li>
                <li><i class="fas fa-tv"></i> <span><?= $t['feat_3'] ?></span></li>
                <li><i class="fas fa-cloud-download-alt"></i> <span><?= $t['feat_4'] ?></span></li>
            </ul>
        </div>
        
        <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> <?= $t['btn_back'] ?></a>
    </div>

    <div class="payment">
        <form method="POST" id="paymentForm">
            
            <div class="section-header">
                <span class="step-label"><?= $t['step_1'] ?></span>
            </div>

            <div class="plans-grid">
                <div class="plan-card" onclick="selectPlan('mini', 10000, this)">
                    <div class="card-top">
                        <div class="plan-icon"><i class="fas fa-bolt"></i></div>
                    </div>
                    <div>
                        <div class="plan-name"><?= $t['plan_mini'] ?></div>
                        <div class="plan-price">Rp 10.000</div>
                        <div class="plan-duration"><?= $t['dur_3days'] ?></div>
                    </div>
                </div>

                <div class="plan-card active" onclick="selectPlan('month', 45000, this)">
                    <span class="badge pop"><?= $t['badge_pop'] ?></span>
                    <div class="card-top">
                        <div class="plan-icon"><i class="fas fa-star"></i></div>
                    </div>
                    <div>
                        <div class="plan-name"><?= $t['plan_month'] ?></div>
                        <div class="plan-price">Rp 45.000</div>
                        <div class="plan-duration"><?= $t['dur_30days'] ?></div>
                    </div>
                </div>

                <div class="plan-card" onclick="selectPlan('year', 250000, this)">
                    <span class="badge" style="background:#10b981; color:white;"><?= $t['badge_save'] ?></span>
                    <div class="card-top">
                        <div class="plan-icon"><i class="fas fa-calendar-check"></i></div>
                    </div>
                    <div>
                        <div class="plan-name"><?= $t['plan_year'] ?></div>
                        <div class="plan-price">Rp 250.000</div>
                        <div class="plan-duration"><?= $t['dur_12months'] ?></div>
                    </div>
                </div>

                <div class="plan-card" onclick="selectPlan('life', 500000, this)">
                    <span class="badge gold"><?= $t['badge_sultan'] ?></span>
                    <div class="card-top">
                        <div class="plan-icon"><i class="fas fa-crown"></i></div>
                    </div>
                    <div>
                        <div class="plan-name"><?= $t['plan_life'] ?></div>
                        <div class="plan-price">Rp 500.000</div>
                        <div class="plan-duration"><?= $t['dur_forever'] ?></div>
                    </div>
                </div>
            </div>
            
            <input type="hidden" name="selected_package" id="selectedPackageInput" value="month">

            <div class="section-header">
                <span class="step-label"><?= $t['step_2'] ?></span>
            </div>
            
            <div class="methods">
                <label class="method-item active" onclick="selectMethod(this)">
                    <input type="radio" name="payment_method" value="qris" checked>
                    <i class="fas fa-qrcode" style="font-size:20px; color:white;"></i>
                    <div style="line-height:1.2;">
                        <strong style="color:white; font-size:14px; display:block;">QRIS Instant</strong>
                        <span style="font-size:11px; color:var(--text-muted);">Gopay, Dana, OVO</span>
                    </div>
                </label>
                <label class="method-item" onclick="selectMethod(this)">
                    <input type="radio" name="payment_method" value="bank">
                    <i class="fas fa-university" style="font-size:18px; color:white;"></i>
                    <div style="line-height:1.2;">
                        <strong style="color:white; font-size:14px; display:block;"><?= $t['method_bank'] ?></strong>
                        <span style="font-size:11px; color:var(--text-muted);"><?= $t['method_manual'] ?></span>
                    </div>
                </label>
            </div>

            <button type="button" class="btn-confirm" id="btnPay" onclick="processPayment()">
                <i class="fas fa-lock"></i> <span id="btnText"><?= $t['btn_pay_prefix'] ?> Rp 45.000</span>
            </button>
            
            <input type="hidden" name="pay_now" value="1">
            
            <div style="margin-top: 20px; text-align: center; color: var(--text-muted); font-size: 11px; display: flex; justify-content: center; gap: 15px;">
                <span><i class="fas fa-shield-alt"></i> <?= $t['txt_secure'] ?></span>
                <span><i class="fas fa-check-circle"></i> <?= $t['txt_cancel'] ?></span>
            </div>
        </form>
    </div>
</div>

<script>
    // Ambil kata "Bayar" atau "Pay" dari PHP ke JS
    const txtPayPrefix = "<?= $t['btn_pay_prefix'] ?>";

    // Inisialisasi format Rupiah
    const formatRupiah = (number) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(number);
    }

    // Fungsi Pilih Paket
    function selectPlan(planValue, price, element) {
        // Reset styles
        document.querySelectorAll('.plan-card').forEach(card => card.classList.remove('active'));
        
        // Active Style
        element.classList.add('active');
        
        // Update Hidden Input
        document.getElementById('selectedPackageInput').value = planValue;
        
        // Update Button Text with Price (Dinamis Bahasa)
        document.getElementById('btnText').innerText = txtPayPrefix + " " + formatRupiah(price);
    }

    // Fungsi Styling Radio Button (Opsional untuk efek border)
    function selectMethod(element) {
        document.querySelectorAll('.method-item').forEach(item => item.classList.remove('active'));
        element.classList.add('active');
        // Trigger radio click inside
        element.querySelector('input').checked = true;
    }

    // Logika Submit
    function processPayment() {
        const btn = document.getElementById('btnPay');
        const btnText = document.getElementById('btnText');
        
        // Menggunakan teks konfirmasi dari PHP
        if(confirm("<?= $t['js_confirm'] ?>")) {
            // Efek Loading
            btn.style.opacity = "0.7";
            btn.style.pointerEvents = "none";
            btnText.innerText = "<?= $t['js_processing'] ?>";
            btn.querySelector('i').className = "fas fa-circle-notch fa-spin";
            
            setTimeout(() => {
                document.getElementById('paymentForm').submit();
            }, 1200);
        }
    }
</script>
</body>
</html>