<?php
session_start();
include '../config/koneksi.php';
//validasi user sudah login
if(!isset($_SESSION['user_id'])||$_SESSION['role'] != 'staff'){
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$tanggal = $_POST['tanggal'];
$departemen = $_POST['departemen'];
$bahan = $_POST['bahan'];
$kategori = $_POST['kategori'];
$jumlah = $_POST['jumlah'];
$satuan = $_POST['satuan'];
$jumlah_final = $_POST['jumlah_final'];
$harga_per_unit = $_POST['harga_per_unit'];

//validasi jumlah dan harga tidak boleh kurang dari atau sama 0
if($jumlah <= 0 || $harga_per_unit<= 0){
    die('jumlah dan harga harus lebih dari 0');
}

//rumus total kerugian
$total_kerugian = $jumlah * $harga_per_unit;

//input data ke database
$query = "INSERT INTO waste
(user_id,tanggal,departemen,bahan,kategori,jumlah,satuan,jumlah_final,harga_per_unit,total_kerugian,status)
VALUES
('$user_id','$tanggal','$departemen','$bahan','$kategori','$jumlah','$satuan','$jumlah_final','$harga_per_unit','$total_kerugian','pending')";

//menghubungkan ke database
mysqli_query($conn,$query);
header('Location: dashboard.php');
exit;
?>
