<?php
include "../config/koneksi.php";

$id = $_POST['id'];
$alasan = $_POST['alasan'];

$query = "UPDATE waste SET 
            is_deleted = 1,
            deleted_reason = '$alasan',
            deleted_at = NOW()
          WHERE id = $id";

mysqli_query($conn, $query);

header("Location: list_waste.php");
