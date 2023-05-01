<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $myphpadmin_host = "127.0.0.1:3306";
    $myphpadmin_username = "root";
    $myphpadmin_password = "";
    $myphpadmin_dbname = "fun_fair";
    $conn = new mysqli($myphpadmin_host, $myphpadmin_username, $myphpadmin_password, $myphpadmin_dbname);

    if (!$conn->connect_error) {
    $stmt = $conn->prepare("SELECT * FROM events ORDER BY event_date_time ASC");
    $stmt->execute();
    $result1 = $stmt->get_result();
    // not finished!!!!!!!!!!!!!!!!!
    }
}
?>