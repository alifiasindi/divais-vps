<?php
require_once 'koneksi.php';

$stmt = $pdo->query("SELECT * FROM temp_rfid");

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<pre>";
print_r($data);
echo "</pre>";