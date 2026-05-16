<?php

require 'vendor/autoload.php';
include '../config/koneksi.php';

use Dompdf\Dompdf;

$dompdf = new Dompdf();

/* Ambil data dari form */
$bulan = $_POST['bulan'];


/* Pecah bulan */
$pecah = explode('-', $bulan);

$tahun = $pecah[0];
$bulanAngka = $pecah[1];
$namaBulan = [
    '01' => 'January',
    '02' => 'February',
    '03' => 'March',
    '04' => 'April',
    '05' => 'May',
    '06' => 'June',
    '07' => 'July',
    '08' => 'August',
    '09' => 'September',
    '10' => 'October',
    '11' => 'November',
    '12' => 'December'
];

/* Query database */
$query = "SELECT *
          FROM waste
          WHERE status='approved'
          AND is_deleted=0
          AND MONTH(tanggal)='$bulanAngka'
          AND YEAR(tanggal)='$tahun'
          ORDER BY tanggal DESC";

$result = mysqli_query($conn, $query);

$totalKerugian = 0;
$totalData = mysqli_num_rows($result);

/* HTML PDF */
$html = '

<style>

body{
    font-family: Arial, sans-serif;
    color:#374151;
    padding:20px;
    font-size:13px;
}

.header{
    width:100%;
    margin-bottom:25px;
}

.title{
    text-align:center;
}

.title h1{
    color:#065f46;
    margin:0;
    font-size:28px;
}

.title p{
    color:#6b7280;
    margin-top:5px;
}

.info-table{
    width:25%;
    margin-bottom:15px;
    
}

.info-table td{
    padding:2px 0;
    font-size:13px;
}


.report-table{
    width:100%;
    border-collapse:collapse;
}

.report-table th{
    background:#10b981;
    color:white;
    padding:12px;
    font-size:13px;
}

.report-table td{
    border:1px solid #d1d5db;
    padding:10px;
    font-size:12px;
}

.report-table tr:nth-child(even){
    background:#f9fafb;
}

.total-box{
    margin-top:25px;
    text-align:right;
    font-size:16px;
    font-weight:bold;
    color:#dc2626;
}

.footer{
    margin-top:50px;
    text-align:right;
    font-size:13px;
}

.signature{
    margin-top:60px;
    font-weight:bold;
}
</style>



<div class="header">

    <div class="title">
        <h1>Smart Waste Tracker</h1>
        <p>Laporan Waste Bulanan</p>
    </div>

</div>


<table class="info-table">
<tr>
    <td><strong>Periode</strong></td>
    <td>: '.$namaBulan[$bulanAngka].'  '.$tahun.'</td>
</tr>

<tr>
    <td><strong>Tanggal Cetak</strong></td>
    <td>: '.date('d M Y').'</td>
</tr>

<tr>
    <td><strong>Total Laporan</strong></td>
    <td>: '.$totalData.'</td>
</tr>

</table>

<table class="report-table">

<tr>
    <th>No</th>
    <th>Tanggal</th>
    <th>Departemen</th>
    <th>Bahan</th>
    <th>Kategori</th>
    <th>Total Kerugian</th>
</tr>

';

$no = 1;

while($row = mysqli_fetch_assoc($result)){

    $html .= '

    <tr>
        <td>'.$no++.'</td>
        <td>'.$row['tanggal'].'</td>
        <td>'.$row['departemen'].'</td>
        <td>'.$row['bahan'].'</td>
        <td>'.$row['kategori'].'</td>
        <td>Rp '.number_format($row['total_kerugian'],0,',','.').'</td>
    </tr>

    ';

    $totalKerugian += $row['total_kerugian'];
}

$html .= '

</table>

<div class="total-box">
    Total Kerugian:  -Rp '.number_format($totalKerugian,0,',','.').'
</div>

';

$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'landscape');

$dompdf->render();

$dompdf->stream("laporan_waste_bulanan.pdf", ["Attachment"=>false]);
