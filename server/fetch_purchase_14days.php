<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $myphpadmin_host = "127.0.0.1:3306";
    $myphpadmin_username = "root";
    $myphpadmin_password = "";
    $myphpadmin_dbname = "fun_fair";
    $conn = new mysqli($myphpadmin_host, $myphpadmin_username, $myphpadmin_password, $myphpadmin_dbname);

    $sql = "SELECT * FROM recordpurchase WHERE purchase_date <= DATE_ADD(NOW(), INTERVAL 14 DAY) ORDER BY purchase_date ASC";
    $result = $conn->query($sql);

    $rows = array();
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $conn->close();

    $json = json_encode($rows);
    header('Content-Type: application/json');
    echo $json;
}
?>