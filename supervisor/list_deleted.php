<?php
session_start();
include "../config/koneksi.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'supervisor'){
    header("Location: ../auth/login.php");
    exit;
}

/* Ambil data deleted */
$query = "
SELECT waste.*, users.nama
FROM waste
JOIN users ON waste.user_id = users.id
WHERE waste.is_deleted = 1
ORDER BY waste.deleted_at DESC
";

$result = mysqli_query($conn, $query);
$rows = [];
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
}
$total_deleted = count($rows);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Laporan Dihapus — Smart Waste Tracker</title>

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

        /* ===== MAIN CONTENT ===== */
        #main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-h);
            padding: 24px 20px;
            min-height: calc(100vh - var(--topbar-h));
        }

        /* ===== SUMMARY CARD ===== */
        .summary-card {
            background: #fff;
            border-radius: 14px;
            padding: 18px 20px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.07);
            border-left: 4px solid #f59e0b;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            max-width: 280px;
            margin-bottom: 24px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }
        .summary-card .stat-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            margin-bottom: 4px;
        }
        .summary-card .stat-value {
            font-size: 1.4rem;
            font-weight: 800;
            color: #f59e0b;
        }
        .summary-card .stat-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            background: #fffbeb;
            color: #f59e0b;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        /* ===== TABLE CARD ===== */
        .table-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.07);
            overflow: hidden;
            margin-bottom: 24px;
        }
        .table-card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .table-card-header h6 {
            margin: 0;
            font-size: 0.9rem;
            font-weight: 800;
            color: var(--green-dark);
        }

        /* Table overrides */
        #deletedTable th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: var(--green-dark);
            font-weight: 800;
            background: #f0fdf4;
            border-bottom: 2px solid #d1fae5;
            white-space: nowrap;
        }
        #deletedTable td {
            font-size: 0.85rem;
            color: #374151;
            vertical-align: middle;
        }
        #deletedTable tbody tr {
            transition: background 0.15s;
        }
        #deletedTable tbody tr:hover {
            background: #f0fdf4;
        }

        .empty-row td {
            text-align: center;
            padding: 40px 20px;
            color: #9ca3af;
            font-size: 0.9rem;
        }
        .empty-row td i {
            display: block;
            font-size: 2rem;
            margin-bottom: 8px;
            color: #d1d5db;
        }

        /* ===== FOOTER ===== */
        footer {
            text-align: center;
            padding: 20px;
            color: #9ca3af;
            font-size: 0.8rem;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.open { transform: translateX(0); }
            #hamburger { display: flex; }
            #topbar { left: 0; }
            #main-content {
                margin-left: 0;
                padding: 16px 14px;
            }
            .summary-card { max-width: 100%; }
        }
    </style>
</head>
<body>

<div id="sidebar-overlay"></div>

<!-- ===== TOP BAR ===== -->
<header id="topbar">
    <button id="hamburger" aria-label="Toggle menu">
        <i class="fas fa-bars"></i>
    </button>
    <h5>Supervisor Panel</h5>
</header>

<!-- ===== SIDEBAR ===== -->
<nav id="sidebar">
    <div class="sidebar-brand">
        <img src="../img/logo.png" alt="Logo">
        <span>Smart Waste Tracker</span>
    </div>

    <div class="sidebar-nav">
        <a href="dashboard_supervisor.php">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="list_all_reports.php">
            <i class="fas fa-clipboard-list"></i> Semua Laporan
        </a>
        <a href="list_deleted.php" class="active">
            <i class="fas fa-trash"></i> Laporan Dihapus
        </a>
        <hr class="sidebar-divider">
    </div>

    <div class="sidebar-logout">
        <a href="../auth/logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</nav>

<!-- ===== MAIN CONTENT ===== -->
<main id="main-content">

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-card-header">
            <h6><i class="fas fa-table mr-2" style="color:var(--green-light)"></i>Riwayat Laporan Dihapus</h6>
        </div>

        <div class="table-responsive">
            <table id="deletedTable" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="padding:12px 16px">No</th>
                        <th style="padding:12px 16px">Nama Staff</th>
                        <th style="padding:12px 16px">Bahan</th>
                        <th style="padding:12px 16px">Kategori</th>
                        <th style="padding:12px 16px">Total Kerugian</th>
                        <th style="padding:12px 16px">Alasan Hapus</th>
                        <th style="padding:12px 16px">Waktu Hapus</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($rows)): ?>
                    <tr class="empty-row">
                        <td colspan="7">
                            <i class="fas fa-inbox"></i>
                            Belum ada laporan yang dihapus
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($rows as $row): ?>
                    <tr>
                        <td style="padding:11px 16px"><?php echo $no++; ?></td>
                        <td style="padding:11px 16px"><?php echo htmlspecialchars($row['nama']); ?></td>
                        <td style="padding:11px 16px"><?php echo htmlspecialchars($row['bahan']); ?></td>
                        <td style="padding:11px 16px"><?php echo htmlspecialchars($row['kategori']); ?></td>
                        <td style="padding:11px 16px">Rp <?php echo number_format($row['total_kerugian'], 0, ',', '.'); ?></td>
                        <td style="padding:11px 16px"><?php echo htmlspecialchars($row['deleted_reason'] ?? '-'); ?></td>
                        <td style="padding:11px 16px"><?php echo htmlspecialchars($row['deleted_at'] ?? '-'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer>Copyright &copy; Smart Waste Tracker 2026</footer>
</main>

<!-- ===== SCRIPTS ===== -->
<script>
    const sidebar   = document.getElementById('sidebar');
    const overlay   = document.getElementById('sidebar-overlay');
    const hamburger = document.getElementById('hamburger');

    if (hamburger) {
        hamburger.addEventListener('click', function() {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
        });
    }
    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        });
    }
</script>

</body>
</html>
