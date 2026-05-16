<?php
session_start();
include "../config/koneksi.php";

/* Proteksi supervisor */
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'supervisor'){
    header("Location: ../auth/login.php");
    exit;
}

/* Ambil semua laporan + join 2x */
$query = "SELECT waste.*,
                 u1.nama AS nama_staff,
                 u2.nama AS nama_supervisor
          FROM waste
          JOIN users u1 ON waste.user_id = u1.id
          LEFT JOIN users u2 ON waste.approved_by = u2.id
          WHERE waste.is_deleted = 0
          ORDER BY waste.id DESC";

$result = mysqli_query($conn, $query);

/* Hitung ringkasan */
$total_laporan = mysqli_num_rows($result);
mysqli_data_seek($result, 0);

$total_kerugian = 0;
$rows = [];
$status_count = ['pending' => 0, 'approved' => 0, 'rejected' => 0];
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
    $total_kerugian += $row['total_kerugian'];
    $s = strtolower(trim($row['status']));
    if (isset($status_count[$s])) $status_count[$s]++;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Total Laporan — Smart Waste Tracker</title>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

    <!-- DataTables & jQuery -->
    <link  rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>

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

        /* ===== SUMMARY CARDS ===== */
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
            cursor: default;
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
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .table-card-header h6 {
            margin: 0;
            font-size: 0.9rem;
            font-weight: 800;
            color: var(--green-dark);
        }

        /* Filter pills */
        .filter-pills {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        .pill {
            padding: 5px 14px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
            border: 1.5px solid #e5e7eb;
            background: #fff;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.18s;
        }
        .pill:hover       { border-color: var(--green-light); color: var(--green-dark); }
        .pill.active      { background: var(--green-mid); border-color: var(--green-mid); color: #fff; }
        .pill.pill-warn   { }
        .pill.pill-warn.active   { background: #f59e0b; border-color: #f59e0b; color: #fff; }
        .pill.pill-danger { }
        .pill.pill-danger.active { background: #ef4444; border-color: #ef4444; color: #fff; }

        /* Table overrides */
        #reportTable th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: var(--green-dark);
            font-weight: 800;
            background: #f0fdf4;
            border-bottom: 2px solid #d1fae5;
        }
        #reportTable td {
            font-size: 0.85rem;
            color: #374151;
            vertical-align: middle;
        }
        #reportTable tbody tr {
            transition: background 0.15s;
        }
        #reportTable tbody tr:hover {
            background: #f0fdf4;
        }

        .badge-status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .badge-status.pending  { background: #fffbeb; color: #b45309; }
        .badge-status.approved { background: #ecfdf5; color: #065f46; }
        .badge-status.rejected { background: #fef2f2; color: #b91c1c; }

        /* Action buttons */
        .btn-action-approve {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #ecfdf5;
            color: #059669;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-action-approve:hover { background: #d1fae5; color: #047857; text-decoration: none; transform: translateY(-2px); }

        .btn-action-reject {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #fef2f2;
            color: #dc2626;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-action-reject:hover { background: #fee2e2; color: #b91c1c; text-decoration: none; transform: translateY(-2px); }

        /* DataTables overrides */
        .dataTables_wrapper .dataTables_filter input {
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            padding: 6px 12px;
            font-family: 'Nunito', sans-serif;
            font-size: 0.85rem;
            outline: none;
            transition: border-color 0.2s;
        }
        .dataTables_wrapper .dataTables_filter input:focus { border-color: var(--green-light); }
        .dataTables_wrapper .dataTables_length select {
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            padding: 5px 10px;
            font-family: 'Nunito', sans-serif;
            font-size: 0.85rem;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: var(--green-mid) !important;
            color: #fff !important;
            border-color: var(--green-mid) !important;
            border-radius: 8px;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #ecfdf5 !important;
            color: var(--green-dark) !important;
            border-color: #d1fae5 !important;
            border-radius: 8px;
        }
        .dataTables_wrapper .dataTables_info {
            font-size: 0.82rem;
            color: #6b7280;
        }
        div.dataTables_wrapper { padding: 16px 20px; }

        /* ===== FOOTER ===== */
        footer {
            text-align: center;
            padding: 20px;
            color: #9ca3af;
            font-size: 0.8rem;
            margin-top: 4px;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            .cards-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.open { transform: translateX(0); }
            #hamburger { display: flex; }
            #topbar { left: 0; }
            #main-content { margin-left: 0; padding: 16px 14px; }
            .page-heading { flex-direction: column; align-items: flex-start; }
            .page-heading h1 { font-size: 1.1rem; }
            .export-form { width: 100%; }
            .export-form input[type="month"] { flex: 1; min-width: 0; }
            .btn-export { flex: 1; justify-content: center; }
            .cards-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            .stat-card { padding: 14px 16px; }
            .stat-value { font-size: 1rem; }
            .stat-icon { width: 38px; height: 38px; font-size: 1rem; border-radius: 10px; }
        }
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
        <a href="dashboard_supervisor.php">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="list_all_reports.php" class="active">
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

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-card-header">
            <h6><i class="fas fa-table mr-2" style="color:var(--green-light)"></i>Semua Laporan Waste</h6>
            <div class="filter-pills">
                <button class="pill active"        data-status="all">Semua</button>
                <button class="pill pill-warn"               data-status="pending">Pending</button>
                <button class="pill"               data-status="approved">Approved</button>
                <button class="pill pill-danger"   data-status="rejected">Rejected</button>
            </div>
        </div>

        <div class="table-responsive">
            <table id="reportTable" class="table table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Staff</th>
                        <th>Tanggal</th>
                        <th>Departemen</th>
                        <th>Bahan</th>
                        <th>Kategori</th>
                        <th>Jumlah</th>
                        <th>Harga/Unit</th>
                        <th>Total Kerugian</th>
                        <th>Status</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $no = 1;
                foreach ($rows as $row):
                    $st = strtolower(trim($row['status']));
                    if ($st === 'pending')  $label = 'Pending';
                    elseif ($st === 'approved') $label = 'Approved';
                    else                    $label = 'Rejected';
                ?>
                <tr data-status="<?php echo $st; ?>">
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($row['nama_staff']); ?></td>
                    <td><?php echo htmlspecialchars($row['tanggal']); ?></td>
                    <td><?php echo htmlspecialchars($row['departemen']); ?></td>
                    <td><?php echo htmlspecialchars($row['bahan']); ?></td>
                    <td><?php echo htmlspecialchars($row['kategori']); ?></td>
                    <td><?php echo htmlspecialchars($row['jumlah']); ?></td>
                    <td>Rp <?php echo number_format($row['harga_per_unit'], 0, ',', '.'); ?></td>
                    <td>Rp <?php echo number_format($row['total_kerugian'], 0, ',', '.'); ?></td>
                    <td>
                        <span class="badge-status <?php echo $st; ?>"><?php echo $label; ?></span>
                        <?php if ($st === 'rejected' && !empty($row['reject_reason'])): ?>
                            <small class="d-block text-danger mt-1" style="font-weight:700; line-height:1.2;">
                                Reason: <?php echo htmlspecialchars($row['reject_reason']); ?>
                            </small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($st === 'pending'): ?>
                            <div style="display: flex; gap: 6px; justify-content: center;">
                                <a href="approve_waste.php?id=<?php echo $row['id']; ?>" class="btn-action-approve" title="Approve Laporan">
                                    <i class="fas fa-check"></i>
                                </a>
                                <button type="button" class="btn-action-reject" title="Reject Laporan" 
                                        onclick="openRejectModal(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; color: #9ca3af; font-size: 0.85rem;">
                                -
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer>Copyright &copy; Smart Waste Tracker 2026</footer>
</main>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header" style="border-bottom: 1px solid #f0fdf4; padding: 20px 25px;">
                <h5 class="modal-title" id="rejectModalLabel" style="font-weight: 800; color: #b91c1c;">
                    <i class="fas fa-times-circle mr-2"></i>Tolak Laporan
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="reject_waste.php" method="POST">
                <div class="modal-body" style="padding: 25px;">
                    <input type="hidden" name="id" id="reject_id">
                    <div class="form-group">
                        <label for="reject_reason" style="font-weight: 700; color: #374151; font-size: 0.9rem;">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="reason" id="reject_reason" rows="3" 
                                  placeholder="Contoh: Stok bahan masih banyak, Data tidak sesuai, dll..." 
                                  style="border-radius: 10px; border: 1.5px solid #d1d5db; padding: 12px; font-size: 0.9rem;" required></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: none; padding: 0 25px 25px;">
                    <button type="button" class="btn btn-light" data-dismiss="modal" style="border-radius: 8px; font-weight: 700; font-size: 0.85rem; padding: 10px 20px;">Batal</button>
                    <button type="submit" class="btn btn-danger" style="border-radius: 8px; font-weight: 700; font-size: 0.85rem; padding: 10px 20px; background: #dc2626;">Kirim Penolakan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== SCRIPTS ===== -->
<script>
    /* ---- Hamburger / Sidebar Toggle ---- */
    const sidebar   = document.getElementById('sidebar');
    const overlay   = document.getElementById('sidebar-overlay');
    const hamburger = document.getElementById('hamburger');

    /* Guard: hamburger hanya ada di layar mobile */
    if (hamburger) {
        hamburger.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
        });
    }
    if (overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        });
    }

    /* ---- DataTable Init ---- */
    let table = $('#reportTable').DataTable({
        language: {
            search:         "Cari:",
            lengthMenu:     "Tampilkan _MENU_ data",
            info:           "Menampilkan _START_–_END_ dari _TOTAL_ laporan",
            infoEmpty:      "Tidak ada data",
            infoFiltered:   "(difilter dari _MAX_ total)",
            zeroRecords:    "Tidak ada laporan yang cocok",
            paginate: {
                first:    "«",
                previous: "‹",
                next:     "›",
                last:     "»"
            }
        },
        pageLength: 10,
        order: [[0, 'asc']],
        columnDefs: [
            /* Kolom ke-10 (index 9) = Status, Kolom ke-11 (index 10) = Aksi, tidak perlu sortable */
            { orderable: false, targets: [9, 10] }
        ]
    });

    /* ---- Status Filter Pills ---- */
    /*
     * Cara robust: baca nilai sel Status (kolom index 9) dari data[9],
     * bukan dari DOM node — karena node() = null saat baris di-paginate ke halaman lain.
     * data[9] berisi inner HTML badge, kita ambil teks-nya saja lalu lowercase.
     */
    $.fn.dataTable.ext.search.push(function(settings, data) {
        const activeStatus = (document.querySelector('.pill.active') || {}).dataset.status || 'all';
        if (activeStatus === 'all') return true;

        /* data[9] = HTML sel Status, strip tag lalu lowercase */
        const cellText = data[9].replace(/<[^>]+>/g, '').trim().toLowerCase();
        return cellText.startsWith(activeStatus);
    });

    document.querySelectorAll('.pill').forEach(function(pill) {
        pill.addEventListener('click', function() {
            document.querySelectorAll('.pill').forEach(function(p) {
                p.classList.remove('active');
            });
            this.classList.add('active');
            table.draw();
        });
    });

    /* ---- Reject Modal Function ---- */
    function openRejectModal(id) {
        document.getElementById('reject_id').value = id;
        $('#rejectModal').modal('show');
    }
</script>

</body>
</html>
