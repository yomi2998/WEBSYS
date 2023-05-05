<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $myphpadmin_host = "127.0.0.1:3306";
    $myphpadmin_username = "root";
    $myphpadmin_password = "";
    $myphpadmin_dbname = "fun_fair";
    $token = $data['token_id'];
    $conn = new mysqli($myphpadmin_host, $myphpadmin_username, $myphpadmin_password, $myphpadmin_dbname);
    $result = $conn->query("SELECT username FROM account WHERE token_id = '$token'");
    $row = $result->fetch_assoc();
    $username = $row['username'];
    $result = $conn->query("SELECT e.event_name, e.image_link, r.price, r.quantity, r.purchase_date FROM recordpurchase r JOIN events e ON e.event_id = r.event_id WHERE r.username = '$username' ORDER BY r.purchase_date ASC");

    $rows = array();
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $conn->close();

    $json = json_encode($rows);
    header('Content-Type: application/json');
    echo $json;
}
else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $data = json_decode(file_get_contents('php://input'), true);
    $myphpadmin_host = "127.0.0.1:3306";
    $myphpadmin_username = "root";
    $myphpadmin_password = "";
    $myphpadmin_dbname = "fun_fair";
    $conn = new mysqli($myphpadmin_host, $myphpadmin_username, $myphpadmin_password, $myphpadmin_dbname);
    $result = $conn->query("SELECT p.purchase_id, p.username, e.event_name, p.quantity, p.price, p.purchase_date FROM recordpurchase p JOIN events e ON e.event_id = p.event_id ORDER BY p.purchase_date ASC");

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