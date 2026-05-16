<?php
session_start();
include "../config/koneksi.php";

//validasi apakah sudah login sebagai supervisor
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'supervisor'){
    header('Location: ../auth/login.php');
    exit;
}

//ambil semua laporan pending di database
$query = "SELECT waste.id,
                 waste.tanggal,
                 waste.departemen,
                 waste.bahan,
                 waste.kategori,
                 waste.jumlah,
                 waste.harga_per_unit,
                 waste.total_kerugian,
                 users.nama AS nama_staff
        FROM waste
        JOIN users ON waste.user_id = users.id
        WHERE waste.status = 'pending'
        AND waste.is_deleted = 0
        ORDER BY waste.id DESC";

$result = mysqli_query($conn,$query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Laporan Pending — Smart Waste Tracker</title>

    <!-- Font Awesome & Google Font -->
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="../img/logo.png">

    <!-- JS Libraries -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

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
        .table th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: var(--green-dark);
            font-weight: 800;
            background: #f0fdf4;
            border-bottom: 2px solid #d1fae5;
            white-space: nowrap;
        }
        .table td {
            font-size: 0.85rem;
            color: #374151;
            vertical-align: middle;
        }
        .table tbody tr {
            transition: background 0.15s;
        }
        .table tbody tr:hover {
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
        <a href="list_pending.php" class="active">
            <i class="fas fa-clock"></i> Laporan Pending
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

<!-- ===== MAIN CONTENT ===== -->
<main id="main-content">

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-card-header">
            <h6><i class="fas fa-clock mr-2" style="color:#f59e0b"></i>Daftar Laporan Menunggu Review</h6>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="padding:12px 16px">No</th>
                        <th style="padding:12px 16px">Nama Staff</th>
                        <th style="padding:12px 16px">Tanggal</th>
                        <th style="padding:12px 16px">Departemen</th>
                        <th style="padding:12px 16px">Bahan</th>
                        <th style="padding:12px 16px">Kategori</th>
                        <th style="padding:12px 16px">Jumlah</th>
                        <th style="padding:12px 16px">Total Kerugian</th>
                        <th style="padding:12px 16px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (mysqli_num_rows($result) == 0): ?>
                    <tr class="empty-row">
                        <td colspan="9">
                            <i class="fas fa-inbox"></i>
                            Tidak ada laporan yang menunggu review saat ini
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $no = 1; 
                    while($row = mysqli_fetch_assoc($result)): 
                    ?>
                    <tr>
                        <td style="padding:11px 16px"><?php echo $no++; ?></td>
                        <td style="padding:11px 16px"><?php echo htmlspecialchars($row['nama_staff']); ?></td>
                        <td style="padding:11px 16px"><?php echo htmlspecialchars($row['tanggal']); ?></td>
                        <td style="padding:11px 16px"><?php echo htmlspecialchars($row['departemen']); ?></td>
                        <td style="padding:11px 16px"><?php echo htmlspecialchars($row['bahan']); ?></td>
                        <td style="padding:11px 16px"><?php echo htmlspecialchars($row['kategori']); ?></td>
                        <td style="padding:11px 16px"><?php echo htmlspecialchars($row['jumlah']); ?></td>
                        <td style="padding:11px 16px">Rp <?php echo number_format($row['total_kerugian'], 0, ',', '.'); ?></td>
                        <td style="padding:11px 16px">
                            <div style="display: flex; gap: 6px; justify-content: center;">
                                <a href="approve_waste.php?id=<?php echo $row['id'];?>" class="btn-action-approve" title="Approve Laporan">
                                    <i class="fas fa-check"></i>
                                </a>
                                <button type="button" class="btn-action-reject" title="Reject Laporan" 
                                        onclick="openRejectModal(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
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

    /* ---- Reject Modal Function ---- */
    function openRejectModal(id) {
        document.getElementById('reject_id').value = id;
        $('#rejectModal').modal('show');
    }
</script>

</body>
</html>
