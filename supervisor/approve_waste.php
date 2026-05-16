<?php
session_start();
include "../config/koneksi.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'supervisor'){
    header('Location: ../auth/login.php');
    exit;
}

if(!isset($_GET['id'])){
    header('location: list_pending.php');
    exit;
}

$id = $_GET['id'];
$supervisor_id = $_SESSION['user_id'];

$query_check= "SELECT * FROM waste
                  WHERE id = '$id'
                  AND status = 'pending'
                  AND is_deleted = 0";

$result_check = mysqli_query($conn,$query_check);
$data = mysqli_fetch_assoc($result_check);

if(!$data){
    header('location: list_pending.php');
    exit;   
}

$query = "UPDATE waste SET
          status ='approved',
          approved_by = '$supervisor_id',
          approved_at = NOW()
          WHERE id = '$id'";
mysqli_query($conn,$query);
header('location: list_pending.php');
exit;
