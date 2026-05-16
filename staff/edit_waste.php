<?php
session_start();
include '../config/koneksi.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff'){
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if(!isset($_GET['id'])){
    header('location: list_waste.php');
    exit;
}

$id = $_GET['id'];

$query = "SELECT * FROM waste
WHERE id = '$id'
AND user_id = '$user_id'
AND is_deleted = 0
AND (status = 'pending' OR status = 'rejected')";

$result = mysqli_query($conn, $query);
$data   = mysqli_fetch_assoc($result);

if(!$data){
    header('location: list_waste.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Edit Waste — Smart Waste Tracker</title>

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

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 8px;
            font-family: 'Nunito', sans-serif;
            font-size: 0.82rem;
            font-weight: 700;
            background: #f3f4f6;
            color: #374151;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
            white-space: nowrap;
        }
        .btn-back:hover { background: #e5e7eb; color: #111827; text-decoration: none; }

        /* ===== MAIN CONTENT ===== */
        #main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-h);
            padding: 28px 24px;
            min-height: calc(100vh - var(--topbar-h));
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* ===== STATUS BADGE (info di atas form) ===== */
        .status-banner {
            width: 100%;
            max-width: 680px;
            margin-bottom: 16px;
            padding: 10px 16px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
            font-weight: 700;
        }
        .status-banner.pending  { background: #fffbeb; color: #b45309; border: 1.5px solid #fde68a; }
        .status-banner.rejected { background: #fef2f2; color: #b91c1c; border: 1.5px solid #fecaca; }

        /* ===== FORM CARD ===== */
        .form-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 1px 8px rgba(0,0,0,0.08);
            overflow: hidden;
            width: 100%;
            max-width: 680px;
        }

        .form-card-header {
            background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%);
            padding: 20px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .form-card-header .header-icon {
            width: 40px; height: 40px;
            background: rgba(255,255,255,0.15);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .form-card-header h5 {
            margin: 0;
            color: #fff;
            font-size: 1rem;
            font-weight: 800;
        }
        .form-card-header p {
            margin: 2px 0 0;
            color: rgba(255,255,255,0.75);
            font-size: 0.8rem;
        }

        .form-card-body {
            padding: 28px 28px 24px;
        }

        /* ===== FORM ELEMENTS ===== */
        .form-row-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group-custom {
            margin-bottom: 18px;
        }
        .form-group-custom label {
            display: block;
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--green-dark);
            margin-bottom: 6px;
            letter-spacing: 0.3px;
        }
        .form-group-custom label .required {
            color: #ef4444;
            margin-left: 2px;
        }

        .form-control-custom {
            width: 100%;
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            padding: 9px 12px;
            font-family: 'Nunito', sans-serif;
            font-size: 0.88rem;
            color: #374151;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #fff;
        }
        .form-control-custom:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }

        .form-divider {
            border: none;
            border-top: 1px solid #f3f4f6;
            margin: 20px 0;
        }

        /* Submit button */
        .btn-submit {
            width: 100%;
            padding: 11px;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-family: 'Nunito', sans-serif;
            font-size: 0.95rem;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.2s, transform 0.15s;
            margin-top: 8px;
        }
        .btn-submit:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        /* ===== FOOTER ===== */
        footer {
            text-align: center;
            padding: 20px;
            color: #9ca3af;
            font-size: 0.8rem;
            margin-top: 16px;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.open { transform: translateX(0); }
            #hamburger { display: flex; }
            #topbar { left: 0; }
            #main-content { margin-left: 0; padding: 16px 14px; }
            .form-card-body { padding: 20px 18px; }
            .form-row-grid { grid-template-columns: 1fr; }
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
        <small>Staff Panel</small>
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
    <h5>Edit Data Waste</h5>
    <a href="list_waste.php" class="btn-back">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</header>

<!-- ===== MAIN CONTENT ===== -->
<main id="main-content">

    <!-- Status banner -->
    <?php
    $st = strtolower(trim($data['status']));
    $icon  = ($st === 'pending') ? 'fa-clock' : 'fa-times-circle';
    $label = ($st === 'pending') ? 'Status: Menunggu Review' : 'Status: Ditolak — silakan perbaiki data';
    ?>
    <div class="status-banner <?php echo $st; ?>">
        <i class="fas <?php echo $icon; ?>"></i>
        <?php echo $label; ?>
    </div>

    <div class="form-card">

        <div class="form-card-header">
            <div class="header-icon">
                <i class="fas fa-edit"></i>
            </div>
            <div>
                <h5>Perbarui Data Waste</h5>
                <p>Ubah data yang diperlukan lalu simpan</p>
            </div>
        </div>

        <div class="form-card-body">
            <form action="proses_edit_waste.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $data['id']; ?>">

                <!-- Baris 1: Tanggal & Departemen -->
                <div class="form-row-grid">
                    <div class="form-group-custom">
                        <label>Tanggal <span class="required">*</span></label>
                        <input type="date" name="tanggal" value="<?php echo $data['tanggal']; ?>"
                               class="form-control-custom" required>
                    </div>
                    <div class="form-group-custom">
                        <label>Departemen <span class="required">*</span></label>
                        <select name="departemen" class="form-control-custom" required>
                            <option value="Kitchen" <?php if($data['departemen'] == 'Kitchen') echo 'selected'; ?>>Kitchen</option>
                            <option value="Service" <?php if($data['departemen'] == 'Service') echo 'selected'; ?>>Service</option>
                        </select>
                    </div>
                </div>

                <!-- Baris 2: Bahan & Kategori -->
                <div class="form-row-grid">
                    <div class="form-group-custom">
                        <label>Nama Bahan <span class="required">*</span></label>
                        <input type="text" name="bahan" value="<?php echo htmlspecialchars($data['bahan']); ?>"
                               class="form-control-custom" required>
                    </div>
                    <div class="form-group-custom">
                        <label>Kategori <span class="required">*</span></label>
                        <select name="kategori" class="form-control-custom" required>
                            <option value="Trim Waste"     <?php if($data['kategori']=='Trim Waste')     echo 'selected'; ?>>Trim Waste</option>
                            <option value="Leftover"       <?php if($data['kategori']=='Leftover')       echo 'selected'; ?>>Leftover</option>
                            <option value="Overproduction" <?php if($data['kategori']=='Overproduction') echo 'selected'; ?>>Overproduction</option>
                            <option value="Expired"        <?php if($data['kategori']=='Expired')        echo 'selected'; ?>>Expired</option>
                        </select>
                    </div>
                </div>

                <!-- Baris 3: Jumlah & Satuan -->
                <div class="form-row-grid">
                    <div class="form-group-custom">
                        <label>Jumlah <span class="required">*</span></label>
                        <input type="number" step="0.01" name="jumlah" id="jumlah"
                               value="<?php echo $data['jumlah']; ?>"
                               class="form-control-custom" required>
                    </div>
                    <div class="form-group-custom">
                        <label>Satuan <span class="required">*</span></label>
                        <select name="satuan" id="satuan" class="form-control-custom" required>
                            <option value="gram"  <?php if($data['satuan']=='gram')  echo 'selected'; ?>>gram</option>
                            <option value="kg"    <?php if($data['satuan']=='kg')    echo 'selected'; ?>>kg</option>
                            <option value="ons"   <?php if($data['satuan']=='ons')   echo 'selected'; ?>>ons</option>
                            <option value="ml"    <?php if($data['satuan']=='ml')    echo 'selected'; ?>>ml</option>
                            <option value="liter" <?php if($data['satuan']=='liter') echo 'selected'; ?>>liter</option>
                            <option value="pcs"   <?php if($data['satuan']=='pcs')   echo 'selected'; ?>>pcs</option>
                        </select>
                    </div>
                </div>

                <!-- Harga per unit -->
                <div class="form-group-custom">
                    <label>Harga Bahan (Rp) <span class="required">*</span></label>
                    <input type="number" step="0.01" name="harga_per_unit"
                           value="<?php echo $data['harga_per_unit']; ?>"
                           class="form-control-custom" required>
                </div>

                <input type="hidden" name="jumlah_final" id="jumlah_final" value="<?php echo $data['jumlah_final'] ?? $data['jumlah']; ?>">

                <hr class="form-divider">

                <!-- Submit -->
                <button type="submit" class="btn-submit">
                    <i></i> Simpan Perubahan
                </button>

            </form>
        </div>
    </div>

    <footer>Copyright &copy; Smart Waste Tracker 2026</footer>
</main>

<!-- ===== SCRIPTS ===== -->
<script>
    /* ---- Hamburger / Sidebar Toggle ---- */
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

    /* ---- Konversi Satuan (update jumlah_final hidden) ---- */
    const jumlahInput   = document.getElementById('jumlah');
    const satuanInput   = document.getElementById('satuan');
    const jumlahFinalIn = document.getElementById('jumlah_final');

    function konversi() {
        let jumlah = parseFloat(jumlahInput.value) || 0;
        let satuan = satuanInput.value;
        let hasil  = jumlah;

        if      (satuan === 'gram') hasil = jumlah / 1000;
        else if (satuan === 'ons')  hasil = jumlah / 10;
        else if (satuan === 'ml')   hasil = jumlah / 1000;

        jumlahFinalIn.value = hasil;
    }

    jumlahInput.addEventListener('input',  konversi);
    satuanInput.addEventListener('change', konversi);
    konversi(); // jalankan sekali saat load
</script>

</body>
</html>
