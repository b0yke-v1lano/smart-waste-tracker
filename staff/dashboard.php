<?php
session_start();
include "../config/koneksi.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff'){
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$nama    = $_SESSION['nama'] ?? 'Staff';

/* ── Stats untuk card ── */
$q1 = mysqli_query($conn, "SELECT COUNT(*) as total FROM waste WHERE user_id=$user_id AND is_deleted=0");
$total_laporan = mysqli_fetch_assoc($q1)['total'] ?? 0;

$q2 = mysqli_query($conn, "SELECT COUNT(*) as total FROM waste WHERE user_id=$user_id AND is_deleted=0 AND status='pending'");
$total_pending = mysqli_fetch_assoc($q2)['total'] ?? 0;

$q3 = mysqli_query($conn, "SELECT COUNT(*) as total FROM waste WHERE user_id=$user_id AND is_deleted=0 AND status='approved'");
$total_approved = mysqli_fetch_assoc($q3)['total'] ?? 0;

$q4 = mysqli_query($conn, "SELECT SUM(total_kerugian) as total FROM waste WHERE user_id=$user_id AND is_deleted=0 AND status='approved'");
$total_kerugian = mysqli_fetch_assoc($q4)['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Dashboard Staff — Smart Waste Tracker</title>

    <!-- Font Awesome & Google Font -->
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="../img/logo.png">

    <style>
        :root {
            --sidebar-width: 250px;
            --green-dark:   #065f46;
            --green-mid:    #059669;
            --green-light:  #10b981;
            --green-pale:   #d1fae5;
            --sidebar-bg:   #064e3b;
            --topbar-h:     60px;
        }

        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: 'Nunito', sans-serif;
            background: #f0fdf4;
            margin: 0;
            overflow-x: hidden;
        }

        /* ===== SIDEBAR ===== */
        #sidebar {
            position: fixed;
            top: 0; left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
            overflow-y: auto;
        }

        .sidebar-brand {
            padding: 24px 20px 16px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-brand img {
            width: 70px;
            height: 70px;
            object-fit: contain;
            display: block;
            margin: 0 auto 10px;
            filter: drop-shadow(0 0 5px rgba(16,185,129,0.3));
        }
        .sidebar-brand span {
            color: #fff;
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .sidebar-brand small {
            display: block;
            color: var(--green-light);
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .sidebar-nav { padding: 12px 0; flex: 1; }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: rgba(255,255,255,0.75);
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 600;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            color: #fff;
            background: rgba(16,185,129,0.15);
            border-left-color: var(--green-light);
        }
        .sidebar-nav a i { width: 18px; text-align: center; font-size: 0.9rem; }

        .sidebar-divider {
            border: none;
            border-top: 1px solid rgba(255,255,255,0.1);
            margin: 8px 20px;
        }

        .sidebar-logout {
            padding: 16px 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-logout a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: color 0.2s;
        }
        .sidebar-logout a:hover { color: #fc8181; }

        /* ===== OVERLAY (mobile) ===== */
        #sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        #sidebar-overlay.show { display: block; }

        /* ===== TOP BAR ===== */
        #topbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--topbar-h);
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            padding: 0 20px;
            z-index: 900;
            gap: 12px;
        }

        #hamburger {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px;
            color: var(--green-dark);
            font-size: 1.2rem;
        }

        #topbar h5 {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: var(--green-dark);
            flex: 1;
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--green-dark);
        }
        .topbar-user .avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: var(--green-pale);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.85rem;
            color: var(--green-dark);
            font-weight: 800;
        }

        /* ===== MAIN CONTENT ===== */
        #main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-h);
            padding: 28px 24px;
            min-height: calc(100vh - var(--topbar-h));
        }

        /* ===== PAGE HEADING ===== */
        .page-heading {
            margin-bottom: 24px;
        }
        .page-heading h1 {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--green-dark);
            margin: 0 0 4px;
        }
        .page-heading p {
            margin: 0;
            font-size: 0.85rem;
            color: #6b7280;
        }

        /* ===== CARDS GRID ===== */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: #fff;
            border-radius: 14px;
            padding: 18px 20px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.07);
            border-left: 4px solid var(--green-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }
        .stat-card.danger  { border-left-color: #ef4444; }
        .stat-card.success { border-left-color: #10b981; }
        .stat-card.info    { border-left-color: #3b82f6; }
        .stat-card.warning { border-left-color: #f59e0b; }

        .stat-card-body { flex: 1; min-width: 0; }
        .stat-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            margin-bottom: 4px;
        }
        .stat-value {
            font-size: 1.2rem;
            font-weight: 800;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .stat-value.danger  { color: #ef4444; }
        .stat-value.success { color: var(--green-mid); }
        .stat-value.info    { color: #3b82f6; }
        .stat-value.warning { color: #f59e0b; }

        .stat-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .stat-icon.danger  { background: #fef2f2; color: #ef4444; }
        .stat-icon.success { background: #ecfdf5; color: var(--green-mid); }
        .stat-icon.info    { background: #eff6ff; color: #3b82f6; }
        .stat-icon.warning { background: #fffbeb; color: #f59e0b; }

        /* ===== ACTION CARDS ===== */
        .action-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 28px;
        }

        .action-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.07);
            padding: 28px 24px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
        }
        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            text-decoration: none;
        }

        .action-card-icon {
            width: 52px; height: 52px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
        }
        .action-card-icon.green  { background: #ecfdf5; color: var(--green-mid); }
        .action-card-icon.blue   { background: #eff6ff; color: #3b82f6; }

        .action-card h5 {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: var(--green-dark);
        }
        .action-card p {
            margin: 0;
            font-size: 0.83rem;
            color: #6b7280;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: 8px;
            font-family: 'Nunito', sans-serif;
            font-size: 0.85rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
        }
        .btn-action.primary {
            background: var(--green-mid);
            color: #fff;
        }
        .btn-action.primary:hover { background: var(--green-dark); text-decoration: none; }
        .btn-action.secondary {
            background: #3b82f6;
            color: #fff;
        }
        .btn-action.secondary:hover { background: #2563eb; text-decoration: none; }

        /* ===== FOOTER ===== */
        footer {
            text-align: center;
            padding: 20px;
            color: #9ca3af;
            font-size: 0.8rem;
            margin-top: 8px;
        }

        /* ===== RESPONSIVE: Tablet (≤ 992px) ===== */
        @media (max-width: 992px) {
            .cards-grid { grid-template-columns: repeat(2, 1fr); }
        }

        /* ===== RESPONSIVE: Mobile (≤ 768px) ===== */
        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.open { transform: translateX(0); }
            #hamburger { display: flex; }
            #topbar { left: 0; }
            #main-content { margin-left: 0; padding: 16px 14px; }

            .page-heading h1 { font-size: 1.1rem; }

            .cards-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
            .stat-card { padding: 14px 16px; }
            .stat-value { font-size: 1rem; }
            .stat-icon { width: 38px; height: 38px; font-size: 1rem; border-radius: 10px; }

            .action-row { grid-template-columns: 1fr; gap: 14px; }
            .action-card { padding: 20px 18px; }
        }

        /* ===== RESPONSIVE: Small Mobile (≤ 480px) ===== */
        @media (max-width: 480px) {
            .cards-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
            .stat-label { font-size: 0.65rem; }
            .stat-value { font-size: 0.95rem; }
        }
    </style>
</head>
<body>

<div id="sidebar-overlay"></div>

<!-- ===== SIDEBAR ===== -->
<nav id="sidebar">
    <div class="sidebar-brand">
        <img src="../img/logo.png" alt="Logo">
        <span>Smart Waste Tracker</span>
    </div>

    <div class="sidebar-nav">
        <a href="dashboard.php" class="active">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="list_waste.php">
            <i class="fas fa-clipboard-list"></i> Laporan Saya
        </a>
        <a href="input_waste.php">
            <i class="fas fa-plus-circle"></i> Input Waste
        </a>
        <hr class="sidebar-divider">
    </div>

    <div class="sidebar-logout">
        <a href="../auth/logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</nav>

<!-- ===== TOP BAR ===== -->
<header id="topbar">
    <button id="hamburger" aria-label="Toggle menu">
        <i class="fas fa-bars"></i>
    </button>
    <h5>Staff Panel</h5>
</header>

<!-- ===== MAIN CONTENT ===== -->
<main id="main-content">

    <div class="page-heading">
        <h1>Selamat Datang, <?php echo htmlspecialchars($nama); ?>! 👋</h1>
        <p>Pantau dan kelola laporan waste harian Anda dari sini.</p>
    </div>

    <!-- ===== CARDS ===== -->
    <div class="cards-grid">

        <!-- Total Laporan -->
        <div class="stat-card success">
            <div class="stat-card-body">
                <div class="stat-label">TOTAL LAPORAN</div>
                <div class="stat-value success"><?php echo $total_laporan; ?></div>
            </div>
            <div class="stat-icon success">
                <i class="fas fa-clipboard-list"></i>
            </div>
        </div>

        <!-- Pending -->
        <div class="stat-card warning">
            <div class="stat-card-body">
                <div class="stat-label">PENDING</div>
                <div class="stat-value warning"><?php echo $total_pending; ?></div>
            </div>
            <div class="stat-icon warning">
                <i class="fas fa-clock"></i>
            </div>
        </div>

        <!-- Approved -->
        <div class="stat-card info">
            <div class="stat-card-body">
                <div class="stat-label">APPROVED</div>
                <div class="stat-value info"><?php echo $total_approved; ?></div>
            </div>
            <div class="stat-icon info">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>

        <!-- Total Kerugian -->
        <div class="stat-card danger">
            <div class="stat-card-body">
                <div class="stat-label">TOTAL KERUGIAN</div>
                <div class="stat-value danger">Rp <?php echo number_format($total_kerugian, 0, ',', '.'); ?></div>
            </div>
            <div class="stat-icon danger">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>

    </div>

    <!-- ===== ACTION CARDS ===== -->
    <div class="action-row">

        <div class="action-card">
            <div class="action-card-icon green">
                <i class="fas fa-plus-circle"></i>
            </div>
            <h5>Input Waste Baru</h5>
            <p>Catat data waste harian Anda dengan mudah dan cepat.</p>
            <a href="input_waste.php" class="btn-action primary">
                <i class="fas fa-plus"></i> Input Sekarang
            </a>
        </div>

        <div class="action-card">
            <div class="action-card-icon blue">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <h5>Laporan Saya</h5>
            <p>Lihat semua laporan waste yang telah Anda inputkan.</p>
            <a href="list_waste.php" class="btn-action secondary">
                <i class="fas fa-eye"></i> Lihat Laporan
            </a>
        </div>

    </div>

    <footer>Copyright &copy; Smart Waste Tracker 2026</footer>
</main>

<!-- ===== SCRIPTS ===== -->
<script>
    const sidebar   = document.getElementById('sidebar');
    const overlay   = document.getElementById('sidebar-overlay');
    const hamburger = document.getElementById('hamburger');

    hamburger.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('show');
    });
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
    });
</script>

</body>
</html>
