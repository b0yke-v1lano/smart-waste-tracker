<?php
session_start();
include "../config/koneksi.php";

/* Proteksi */
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff'){
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* Ambil data laporan milik user ini */
$query = "SELECT * FROM waste
          WHERE user_id = $user_id
          AND is_deleted = 0
          ORDER BY id DESC";

$result = mysqli_query($conn, $query);

/* Hitung ringkasan */
$rows = [];
$total_pending = $total_approved = $total_rejected = 0;
$total_kerugian = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
    $s = strtolower(trim($row['status']));
    if ($s === 'pending')  $total_pending++;
    elseif ($s === 'approved') {
        $total_approved++;
        $total_kerugian += $row['total_kerugian'];
    }
    else $total_rejected++;
}
$total_laporan = count($rows);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Laporan Saya — Smart Waste Tracker</title>

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>

    <!-- Font Awesome & Google Font -->
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="../img/logo.png">

    <!-- Bootstrap 5 (untuk modal) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

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

        .btn-input-top {
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
            text-decoration: none;
            transition: background 0.2s;
            white-space: nowrap;
        }
        .btn-input-top:hover { background: var(--green-dark); color: #fff; text-decoration: none; }

        /* ===== MAIN CONTENT ===== */
        #main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-h);
            padding: 24px 20px;
            min-height: calc(100vh - var(--topbar-h));
        }

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
            padding: 16px 18px;
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
        .pill.pill-warn.active   { background: #f59e0b; border-color: #f59e0b; color: #fff; }
        .pill.pill-danger.active { background: #ef4444; border-color: #ef4444; color: #fff; }
        .pill.pill-info.active { background: #3b82f6; border-color: #3b82f6; color: #fff; }

        /* Table styles */
        #wasteTable th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: var(--green-dark);
            font-weight: 800;
            background: #f0fdf4;
            border-bottom: 2px solid #d1fae5;
        }
        #wasteTable td {
            font-size: 0.85rem;
            color: #374151;
            vertical-align: middle;
        }
        #wasteTable tbody tr { transition: background 0.15s; }
        #wasteTable tbody tr:hover { background: #f0fdf4; }

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
        .btn-action-edit {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.78rem;
            font-weight: 700;
            background: #eff6ff;
            color: #2563eb;
            text-decoration: none;
            transition: background 0.15s;
        }
        .btn-action-edit:hover { background: #dbeafe; color: #1d4ed8; text-decoration: none; }

        .btn-action-delete {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.78rem;
            font-weight: 700;
            background: #fef2f2;
            color: #dc2626;
            border: none;
            cursor: pointer;
            transition: background 0.15s;
        }
        .btn-action-delete:hover { background: #fee2e2; }

        .text-locked {
            font-size: 0.78rem;
            color: #9ca3af;
            font-style: italic;
        }

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
        .dataTables_wrapper .dataTables_info { font-size: 0.82rem; color: #6b7280; }
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
            .cards-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            .stat-card { padding: 14px 16px; }
            .stat-value { font-size: 1rem; }
            .stat-icon { width: 38px; height: 38px; font-size: 1rem; border-radius: 10px; }
            .table-card-header { flex-direction: column; align-items: flex-start; }
            .btn-input-top span { display: none; }
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
        <a href="dashboard.php">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="list_waste.php" class="active">
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

    <!-- ===== TABLE CARD ===== -->
    <div class="table-card">
        <div class="table-card-header">
            <h6><i class="fas fa-table mr-2" style="color:var(--green-light)"></i>Daftar Laporan Waste Saya</h6>
            <div class="filter-pills">
                <button class="pill active"        data-status="all">Total Laporan</button>
                <button class="pill pill-warn"      data-status="pending">Pending</button>
                <button class="pill pill-info"                data-status="approved">Approved</button>
                <button class="pill pill-danger"                data-status="rejected">Rejected</button>
            </div>
        </div>

        <div class="table-responsive">
            <table id="wasteTable" class="table table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Departemen</th>
                        <th>Bahan</th>
                        <th>Kategori</th>
                        <th>Jumlah</th>
                        <th>Total Kerugian</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $no = 1;
                foreach ($rows as $row):
                    $st = strtolower(trim($row['status']));
                    $label = ucfirst($st);
                ?>
                <tr data-status="<?php echo $st; ?>">
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($row['tanggal']); ?></td>
                    <td><?php echo htmlspecialchars($row['departemen']); ?></td>
                    <td><?php echo htmlspecialchars($row['bahan']); ?></td>
                    <td><?php echo htmlspecialchars($row['kategori']); ?></td>
                    <td><?php echo htmlspecialchars($row['jumlah']); ?></td>
                    <td>Rp <?php echo number_format($row['total_kerugian'], 0, ',', '.'); ?></td>
                    <td>
                        <span class="badge-status <?php echo $st; ?>"><?php echo $label; ?></span>
                        <?php if ($st === 'rejected' && !empty($row['reject_reason'])): ?>
                            <small class="d-block text-danger mt-1" style="font-weight:700; font-size:0.7rem;">
                                Alasan: <?php echo htmlspecialchars($row['reject_reason']); ?>
                            </small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($row['status'] == 'pending' || $row['status'] == 'rejected'): ?>
                            <a href="edit_waste.php?id=<?php echo $row['id']; ?>" class="btn-action-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button onclick="showDeleteModal(<?php echo $row['id']; ?>)" type="button" class="btn-action-delete ms-1">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        <?php elseif($row['status'] == 'approved'): ?>
                            <span class="text-locked"><i class="fas fa-lock"></i> Terkunci</span>
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

<!-- ===== MODAL DELETE ===== -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:14px; border:none;">
            <form method="POST" action="proses_delete_waste.php">
                <div class="modal-header" style="border-bottom:1px solid #f3f4f6;">
                    <h5 class="modal-title" style="font-weight:800; color:var(--green-dark); font-size:1rem;">
                        <i class="fas fa-trash me-2" style="color:#ef4444;"></i>Alasan Penghapusan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="delete_id">
                    <label style="font-size:0.85rem; font-weight:700; color:#374151; margin-bottom:6px; display:block;">
                        Masukkan alasan penghapusan:
                    </label>
                    <textarea name="alasan" class="form-control" rows="3" required
                        placeholder="Contoh: Data salah input..."
                        style="border:1.5px solid #d1d5db; border-radius:8px; font-family:'Nunito',sans-serif; font-size:0.85rem; resize:none;">
                    </textarea>
                </div>
                <div class="modal-footer" style="border-top:1px solid #f3f4f6;">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash me-1"></i> Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== SCRIPTS ===== -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    /* ---- Hamburger / Sidebar Toggle ---- */
    const sidebar   = document.getElementById('sidebar');
    const overlay   = document.getElementById('sidebar-overlay');
    const hamburger = document.getElementById('hamburger');

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
    let table = $('#wasteTable').DataTable({
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
            { orderable: false, targets: [7, 8] }
        ]
    });

    /* ---- Status Filter Pills ---- */
    $.fn.dataTable.ext.search.push(function(settings, data) {
        const activeStatus = (document.querySelector('.pill.active') || {}).dataset.status || 'all';
        if (activeStatus === 'all') return true;
        
        /* Ambil teks saja, hilangkan tag HTML, ubah ke lowercase */
        const cellText = data[7].replace(/<[^>]+>/g, '').trim().toLowerCase();
        
        /* Cek apakah teks status (seperti 'rejected') ada di dalam sel tersebut */
        return cellText.includes(activeStatus);
    });

    document.querySelectorAll('.pill').forEach(function(pill) {
        pill.addEventListener('click', function() {
            document.querySelectorAll('.pill').forEach(function(p) { p.classList.remove('active'); });
            this.classList.add('active');
            table.draw();
        });
    });

    /* ---- Delete Modal ---- */
    function showDeleteModal(id) {
        document.getElementById('delete_id').value = id;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>

</body>
</html>
