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
        $price = $row['event_price'];
        if($bal < $price * $quantity)
        {
            $response = array('success' => false);
            echo json_encode($response);
            exit();
        }
        function generateID() {
            $prefix = "PUR";
            $characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            $randomString = "";
            
            for ($i = 0; $i < 7; $i++) {
              $randomString .= $characters[rand(0, strlen($characters) - 1)];
            }
            
            return $prefix . $randomString;
          }
          $new_id = generateID();
        $conn->query("INSERT INTO recordpurchase (purchase_id, username, event_id, quantity, price) VALUES ('$new_id', '$usr', '$evn', '$quantity', '$price' * '$quantity')");
        $conn->query("UPDATE account SET balance = balance - '$price' * '$quantity' WHERE token_id = '$token'");
        $conn->query("UPDATE events SET event_ticket_purchased = event_ticket_purchased + '$quantity' WHERE event_name = '$event'");
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