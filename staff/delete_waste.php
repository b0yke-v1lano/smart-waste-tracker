<?php
session_start();
include '../config/koneksi.php';

if(!isset($_SESSION['user_id'])|| $_SESSION['role'] != 'staff'){
    header('Location: ../auth/login.php');
    exit;
}

$id = $_GET['id'];
$user_id = $_SESSION['user_id'];

$query = "UPDATE waste 
SET is_deleted = 1
WHERE id = '$id'
AND user_id = '$user_id'
AND status = 'pending'";

mysqli_query($conn,$query);
header('location: list_waste.php');
exit;
