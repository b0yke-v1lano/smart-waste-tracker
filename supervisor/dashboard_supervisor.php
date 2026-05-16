<?php
session_start();
include "../config/koneksi.php";
 
// validasi apakah sudah login menggunakan akun supervisor
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'supervisor'){
    header('Location: ../auth/login.php');
    exit;
}
 
// Ambil data total kerugian untuk card
$q1 = mysqli_query($conn, "SELECT SUM(total_kerugian) as total FROM waste WHERE status='approved' AND is_deleted=0");
$total_kerugian = mysqli_fetch_assoc($q1)['total'] ?? 0;
 
// Ambil data total laporan untuk card
$q2 = mysqli_query($conn, "SELECT COUNT(*) as total FROM waste WHERE is_deleted=0");
$total_laporan = mysqli_fetch_assoc($q2)['total'];
 
// Ambil laporan dengan status pending untuk card
$status_data = ['pending'=>0,'approved'=>0,'rejected'=>0];
$q3 = mysqli_query($conn, "SELECT status, COUNT(*) as total FROM waste WHERE is_deleted = 0 GROUP BY status");
while ($row = mysqli_fetch_assoc($q3)){
    $status = strtolower(trim($row['status']));
    $status_data[$status] = $row['total'];
}
 
// Ambil data laporan yang dihapus untuk card
$q_deleted = mysqli_query($conn, "SELECT COUNT(*) as total FROM waste WHERE is_deleted=1");
$total_deleted = mysqli_fetch_assoc($q_deleted)['total'];
 
// Ambil data kategori untuk dibuat Pie Chart
$kategori = [];
$total_kategori = [];
$q4 = mysqli_query($conn, "SELECT kategori, COUNT(*) as total FROM waste WHERE is_deleted=0 GROUP BY kategori");
while($row = mysqli_fetch_assoc($q4)){
    $kategori[] = $row['kategori'];
    $total_kategori[] = $row['total'];
}
 
// Ambil data bahan makanan untuk dibuat bar chart
$bahan = [];
$total_bahan = [];
$q5 = mysqli_query($conn, "SELECT bahan, SUM(total_kerugian) as total FROM waste WHERE status='approved' AND is_deleted=0 GROUP BY bahan");
while($row = mysqli_fetch_assoc($q5)){
    $bahan[] = $row['bahan'];
    $total_bahan[] = $row['total'];
}
?>
 
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Smart Waste Tracker</title>
 
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
 
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
 
        /* ===== PAGE HEADING ===== */
        .page-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .page-heading h1 {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--green-dark);
            margin: 0;
        }
 
        /* ===== EXPORT FORM ===== */
        .export-form {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .export-form input[type="month"] {
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            padding: 7px 12px;
            font-family: 'Nunito', sans-serif;
            font-size: 0.85rem;
            outline: none;
            transition: border-color 0.2s;
        }
        .export-form input[type="month"]:focus { border-color: var(--green-light); }
        .btn-export {
            background: var(--green-mid);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-family: 'Nunito', sans-serif;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s;
            white-space: nowrap;
        }
        .btn-export:hover { background: var(--green-dark); }
 
        /* ===== CARDS GRID ===== */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
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
 
        /* ===== CHARTS ROW ===== */
        .charts-row {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 20px;
        }
 
        .chart-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.07);
            overflow: hidden;
        }
        .chart-card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .chart-card-header h6 {
            margin: 0;
            font-size: 0.9rem;
            font-weight: 800;
            color: var(--green-dark);
        }
        .filter-select {
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            padding: 5px 10px;
            font-family: 'Nunito', sans-serif;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--green-dark);
            outline: none;
            cursor: pointer;
            transition: border-color 0.2s;
        }
        .filter-select:focus { border-color: var(--green-light); }
 
        .chart-card-body { padding: 16px 20px; }
        .chart-wrapper { position: relative; height: 280px; }
        .chart-wrapper-pie { position: relative; height: 260px; }
 
        /* ===== FOOTER ===== */
        footer {
            text-align: center;
            padding: 20px;
            color: #9ca3af;
            font-size: 0.8rem;
            margin-top: 16px;
        }
 
        /* ===== RESPONSIVE: Tablet (≤ 992px) ===== */
        @media (max-width: 992px) {
            .cards-grid { grid-template-columns: repeat(2, 1fr); }
            .charts-row { grid-template-columns: 1fr; }
        }
 
        /* ===== RESPONSIVE: Mobile (≤ 768px) ===== */
        @media (max-width: 768px) {
            #sidebar {
                transform: translateX(-100%);
            }
            #sidebar.open {
                transform: translateX(0);
            }
 
            #hamburger { display: flex; }
 
            #topbar {
                left: 0;
            }
 
            #main-content {
                margin-left: 0;
                padding: 16px 14px;
            }
 
            .page-heading {
                flex-direction: column;
                align-items: flex-start;
            }
            .page-heading h1 { font-size: 1.1rem; }
 
            .export-form {
                width: 100%;
            }
            .export-form input[type="month"] {
                flex: 1;
                min-width: 0;
            }
            .btn-export { flex: 1; justify-content: center; }
 
            .cards-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
 
            .stat-card { padding: 14px 16px; }
            .stat-value { font-size: 1rem; }
            .stat-icon { width: 38px; height: 38px; font-size: 1rem; border-radius: 10px; }
 
            .chart-wrapper { height: 240px; }
            .chart-wrapper-pie { height: 220px; }
            .chart-card-header { flex-direction: column; align-items: flex-start; gap: 8px; }
            .filter-select { width: 100%; }
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
 
    
    <nav id="sidebar">
        <div class="sidebar-brand">
            <img src="../img/logo.png" alt="Logo">
            <span>Smart Waste Tracker</span>
        </div>
 
        <div class="sidebar-nav">
            <a href="dashboard_supervisor.php" class="active">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="list_all_reports.php">
                <i class="fas fa-clipboard-list"></i> Semua Laporan
            </a>
            <a href="list_deleted.php">
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
 
    <!-- ===== TOP BAR ===== -->
    <header id="topbar">
        <button id="hamburger" aria-label="Toggle menu">
            <i class="fas fa-bars"></i>
        </button>
        <h5>Supervisor Panel</h5>
    </header>
 
    <!-- ===== MAIN CONTENT ===== -->
    <main id="main-content">
        <div class="page-heading">
            <div>
                <h1>Selamat Datang, Supervisor! 👋</h1>
                <p style="margin:4px 0 0; font-size:0.85rem; color:#6b7280;">Pantau laporan waste dan generate report bulanan.</p>
            </div>
            <form action="export_pdf.php" method="POST" class="export-form">
                <input type="month" name="bulan" required>
                <button type="submit" class="btn-export">
                    <i class="fas fa-file-pdf"></i> Generate Report
                </button>
            </form>
        </div>

 
        <!-- ===== CARDS ===== -->
        <div class="cards-grid">
 
            <!-- Total Kerugian -->
            <div class="stat-card danger">
                <div class="stat-card-body">
                    <div class="stat-label">TOTAL KERUGIAN</div>
                    <div class="stat-value danger">-Rp <?php echo number_format($total_kerugian,0,',','.'); ?></div>
                </div>
                <div class="stat-icon danger">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
 
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
            <div class="stat-card info">
                <div class="stat-card-body">
                    <div class="stat-label">PENDING</div>
                    <div class="stat-value info"><?php echo $status_data['pending']; ?></div>
                </div>
                <div class="stat-icon info">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
 
            <!-- Dihapus -->
            <div class="stat-card warning">
                <div class="stat-card-body">
                    <div class="stat-label">DELETED</div>
                    <div class="stat-value warning"><?php echo $total_deleted; ?></div>
                </div>
                <div class="stat-icon warning">
                    <i class="fas fa-trash"></i>
                </div>
            </div>
 
        </div>
 
        <!-- ===== CHARTS ===== -->
        <div class="charts-row">
 
            <!-- Bar Chart -->
            <div class="chart-card">
                <div class="chart-card-header">
                    <h6><i class="fas fa-chart-bar mr-2" style="color:var(--green-light)"></i>Kerugian Berdasarkan Bahan</h6>
                    <select class="filter-select" id="filterKerugian">
                        <option value="harian">Harian</option>
                        <option value="mingguan">Mingguan</option>
                        <option value="bulanan">Bulanan</option>
                    </select>
                </div>
                <div class="chart-card-body">
                    <div class="chart-wrapper">
                        <canvas id="barChart"></canvas>
                    </div>
                </div>
            </div>
 
            <!-- Pie Chart -->
            <div class="chart-card">
                <div class="chart-card-header">
                    <h6><i class="fas fa-chart-pie mr-2" style="color:var(--green-light)"></i>Distribusi Kategori Waste</h6>
                </div>
                <div class="chart-card-body">
                    <div class="chart-wrapper-pie">
                        <canvas id="pieChart"></canvas>
                    </div>
                </div>
            </div>
 
        </div>
 
        <footer>Copyright &copy; Smart Waste Tracker 2026</footer>
    </main>
 
    <!-- ===== SCRIPTS ===== -->
    <script>
        /* ---- Hamburger / Sidebar Toggle ---- */
        const sidebar  = document.getElementById('sidebar');
        const overlay  = document.getElementById('sidebar-overlay');
        const hamburger = document.getElementById('hamburger');
 
        hamburger.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
        });
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        });
 
        /* ---- PIE CHART ---- */
        new Chart(document.getElementById('pieChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($kategori); ?>,
                datasets: [{
                    data: <?php echo json_encode($total_kategori); ?>,
                    backgroundColor: ["#065f46","#059669","#10b981","#34d399","#a7f3d0"],
                    borderColor: "#ffffff",
                    borderWidth: 2
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: "#065f46",
                            font: { size: 11, family: 'Nunito' },
                            boxWidth: 12,
                            padding: 10
                        }
                    },
                    datalabels: {
                        color: '#ffffff',
                        font: { weight: 'bold', size: 11 },
                        formatter: (value, context) => {
                            let total = context.chart.data.datasets[0].data
                                .reduce((s, v) => s + Number(v), 0);
                            return total ? (value / total * 100).toFixed(1) + '%' : '0%';
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
 
        /* ---- BAR CHART ---- */
        let barChart;
        function loadChart(filter) {
            fetch('get_kerugian_bahan.php?filter=' + filter)
                .then(res => res.json())
                .then(data => {
                    if (barChart) barChart.destroy();
                    barChart = new Chart(document.getElementById('barChart'), {
                        type: 'bar',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Total Kerugian',
                                data: data.values,
                                backgroundColor: '#10b981',
                                hoverBackgroundColor: '#059669',
                                borderRadius: 8
                            }]
                        },
                        options: {
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: '#065f46',
                                    titleColor: '#fff',
                                    bodyColor: '#fff',
                                    borderColor: '#10b981',
                                    borderWidth: 1,
                                    callbacks: {
                                        label: ctx => 'Rp ' + ctx.raw.toLocaleString('id-ID')
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    ticks: { color: "#065f46", font: { family: 'Nunito', size: 11 } },
                                    grid: { display: false }
                                },
                                y: {
                                    ticks: {
                                        color: "#065f46",
                                        font: { family: 'Nunito', size: 11 },
                                        callback: v => 'Rp ' + v.toLocaleString('id-ID')
                                    },
                                    grid: { color: "#e5e7eb" }
                                }
                            }
                        }
                    });
                });
        }
 
        loadChart('harian');
        document.getElementById('filterKerugian').addEventListener('change', function () {
            loadChart(this.value);
        });
    </script>
 
</body>
</html>
