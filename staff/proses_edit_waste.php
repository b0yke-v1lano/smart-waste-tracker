<?php
session_start();
include '../config/koneksi.php';

error_reporting(E_ALL);
ini_set('display_errors',1);

if(!isset($_SESSION['user_id'])|| $_SESSION['role'] != 'staff'){
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if(!isset($_POST['id'])){
    header('location: list_waste.php');
    exit;
}

$id = $_POST['id'];
$tanggal = $_POST['tanggal'];
$departemen = $_POST['departemen'];
$bahan = $_POST['bahan'];
$kategori = $_POST['kategori'];
$jumlah = $_POST['jumlah'];
$harga_per_unit = $_POST['harga_per_unit'];

if($jumlah <= 0 || $harga_per_unit <= 0){
    die('Jumlah dan harga harus lebih dari 0');
}

$total_kerugian = $jumlah * $harga_per_unit;

$query = "UPDATE waste SET 
          tanggal = '$tanggal',
          departemen = '$departemen',
          bahan = '$bahan',
          kategori = '$kategori',
          jumlah = '$jumlah',
          harga_per_unit = '$harga_per_unit',
          total_kerugian = '$total_kerugian',
          status = 'pending',
          approved_by = NULL,
          approved_at = NULL
          WHERE id = '$id'
          AND user_id = '$user_id'
          AND is_deleted = 0";
mysqli_query($conn,$query);
header('location: list_waste.php');
exit;
