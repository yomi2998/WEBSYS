<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $myphpadmin_host = "127.0.0.1:3306";
    $myphpadmin_username = "root";
    $myphpadmin_password = "";
    $myphpadmin_dbname = "fun_fair";
    $data = json_decode(file_get_contents('php://input'), true);
    $event = $data['event_name'];
    $token = $data['token_id'];
    $quantity = $data['quantity'];

    $conn = new mysqli($myphpadmin_host, $myphpadmin_username, $myphpadmin_password, $myphpadmin_dbname);
    if(!$conn->connect_error){
        $result = $conn->query("SELECT * FROM account WHERE token_id = '$token'");
        $row = $result->fetch_assoc();
        if(count($row) == 0)
        {
            $response = array('success' => false);
            echo json_encode($response);
            exit();
        }
        $usr = $row['username'];
        $bal = $row['balance'];
        $result = $conn->query("SELECT * FROM events WHERE event_name = '$event'");
        $row = $result->fetch_assoc();
        if(count($row) == 0)
        {
            $response = array('success' => false);
            echo json_encode($response);
            exit();
        }
        $evn = $row['event_id'];
        $result = $conn->query("SELECT * FROM recordpurchase WHERE event_id = '$evn' AND username = '$usr' AND quantity = '$quantity");
        $row = $result->fetch_assoc();
        if(count($row) == 0)
        {
            $response = array('success' => false);
            echo json_encode($response);
            exit();
        }
        $price = $row['price'];
        $conn->query("DELETE recordpurchase WHERE event_id = '$evn' AND username = '$usr' AND quantity = '$quantity'");
        $conn->query("UPDATE account SET balance = balance + '$price' WHERE token_id = '$token'");
        $conn->query("UPDATE events SET event_ticket_purchased = event_ticket_purchased - '$quantity' WHERE event_name = '$event'");
        $response = array('success' => true);
        echo json_encode($response);
    }
    else{
        $response = array('success' => false);
        echo json_encode($response);
        exit();
    }
}
?>