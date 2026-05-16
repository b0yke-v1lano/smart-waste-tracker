<?php
session_start();
include "../config/koneksi.php";

$email = $_POST['email'];
$password = $_POST['password'];

$query = "SELECT * FROM users WHERE email='$email'";
$result = mysqli_query($conn, $query);

$user = mysqli_fetch_assoc($result);

//jika 
if($user){
    if(password_verify($password, $user['password'])){
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];
        if($user['role'] == 'staff'){
            header('location: ../staff/dashboard.php');
        } else if($user['role'] == 'supervisor'){
            header('location: ../supervisor/dashboard_supervisor.php');
        }
        exit;
    } else {
        header('location: login.php?error=password');
        exit;
    }
} else {
    header('location: login.php?error=user');
    exit;
}
?>