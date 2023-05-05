<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $s = new stdClass();
    $myphpadmin_host = "127.0.0.1:3306";
    $myphpadmin_username = "root";
    $myphpadmin_password = "";
    $myphpadmin_dbname = "fun_fair";
    $s->flag = 0;
    $data = json_decode(file_get_contents('php://input'), true);
    $purchase_id = $data['purchase_id'];
    $event = $data['event_name'];
    $username = $data['username'];
    $quantity = $data['quantity'];

    $conn = new mysqli($myphpadmin_host, $myphpadmin_username, $myphpadmin_password, $myphpadmin_dbname);
    if(!$conn->connect_error){
        $result = $conn->query("SELECT * FROM account WHERE username = '$username'");
        $row = $result->fetch_assoc();
        if(count($row) == 0)
        {
            $s->flag = 1;
            $s->error = "Undo failed: Unable to find username.";
            echo json_encode($s);
            exit();
        }
        $usr = $row['username'];
        $bal = $row['balance'];
        $result = $conn->query("SELECT * FROM recordpurchase WHERE purchase_id = '$purchase_id'");
        $row = $result->fetch_assoc();
        if(count($row) == 0)
        {
            $s->flag = 1;
            $s->error = "Undo failed: Unable to find purchase record.";
            echo json_encode($s);
            exit();
        }
        $price = $row['price'];
        $conn->query("DELETE FROM recordpurchase WHERE purchase_id = '$purchase_id'");
        $conn->query("UPDATE account SET balance = balance + '$price' WHERE username = '$username'");
        $conn->query("UPDATE events SET event_ticket_purchased = event_ticket_purchased - '$quantity' WHERE event_name = '$event'");
        $s->error = "Purchase undone successfully, balance has been returned back to user.";
        echo json_encode($s);
    }
    else{
        $s->flag = 1;
        $s->error = "Connection failed: " . $conn->connect_error;
        echo json_encode($s);
        exit();
    }
}
?>