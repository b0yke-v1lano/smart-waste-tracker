<?php
session_start();
include "../config/koneksi.php";

/* Proteksi supervisor */
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'supervisor'){
    header("Location: ../auth/login.php");
    exit;
}

/* Validasi id (bisa dari GET atau POST) */
$id = isset($_POST['id']) ? $_POST['id'] : (isset($_GET['id']) ? $_GET['id'] : null);
$reason = isset($_POST['reason']) ? $_POST['reason'] : '';

if(!$id){
    header("Location: list_pending.php");
    exit;
}

/* Pastikan data memang pending */
$query_check = "SELECT * FROM waste
                WHERE id = '$id'
                AND status = 'pending'
                AND is_deleted = 0";

$result_check = mysqli_query($conn, $query_check);
$data = mysqli_fetch_assoc($result_check);

if(!$data){
    header("Location: list_pending.php");
    exit;
}

/* Update reject dengan alasan */
$query = "UPDATE waste SET
          status = 'rejected',
          reject_reason = '$reason'
          WHERE id = '$id'";

mysqli_query($conn, $query);

header("Location: list_all_reports.php");
exit;
