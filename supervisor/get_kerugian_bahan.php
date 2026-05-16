<?php
include '../config/koneksi.php';
session_start();

header('Content-Type: application/json');

$filter = $_GET['filter'];

if($filter == 'harian'){
    $query = "SELECT bahan, tanggal, SUM(total_kerugian) as total
              FROM waste
              WHERE status = 'approved' 
              AND is_deleted = 0
              AND tanggal = (SELECT MAX(tanggal) FROM waste WHERE status = 'approved' AND is_deleted = 0)
              GROUP BY bahan";
}

elseif($filter == 'mingguan'){
    $query = "SELECT bahan, tanggal, SUM(total_kerugian) as total
              FROM waste
              WHERE status = 'approved' 
              AND is_deleted = 0
              AND tanggal >= DATE_SUB((SELECT MAX(tanggal) FROM waste WHERE status = 'approved' AND is_deleted = 0),INTERVAL 7 DAY)
              GROUP BY bahan";
}

else { // bulanan
    $query = "SELECT bahan, tanggal, SUM(total_kerugian) as total
              FROM waste
              WHERE status = 'approved'
              AND is_deleted = 0
              AND MONTH(tanggal) = MONTH((SELECT MAX(tanggal) FROM waste WHERE status = 'approved' AND is_deleted = 0))
              AND YEAR(tanggal) = YEAR((SELECT MAX(tanggal) FROM waste WHERE status = 'approved' AND is_deleted = 0))
              GROUP BY bahan";
}


$result = mysqli_query($conn, $query);

$labels = [];
$values = [];

while($row = mysqli_fetch_assoc($result)){
    $labels[] = $row['bahan']. "\n(".date('d M Y',strtotime($row['tanggal'])).")";
    $values[] = $row['total'];
}

echo json_encode([
    "labels" => $labels,
    "values" => $values
]);

