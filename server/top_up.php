<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $s = new stdClass();
  $s->error = "Error: ";
  $s->flag = 0;

  $data = json_decode(file_get_contents('php://input'), true);
  $amount = $data['amount'];
  $token = $data['token_id'];

  if (!$s->flag) {
    $myphpadmin_host = "127.0.0.1:3306";
    $myphpadmin_username = "root";
    $myphpadmin_password = "";
    $myphpadmin_dbname = "fun_fair";

    $conn = new mysqli($myphpadmin_host, $myphpadmin_username, $myphpadmin_password, $myphpadmin_dbname);

    if (!$conn->connect_error) {
      if (!$s->flag) {
        $stmt3 = $conn->prepare("UPDATE account SET balance = balance + ? WHERE token_id = ?");
        $is_admin = 0;
        $stmt3->bind_param("ss", $amount, $token);
        $stmt3->execute();

        if (!$stmt3->affected_rows) {
          $s->flag = 1;
          $s->error .= "failed to add funds from database";
        }
        $stmt3->close();
        $conn->close();
      }
    } else {
      $s->flag = 1;
      $s->error .= "could not connect to mysqli server";
    }
  }


  if ($s->flag == 0) {
    $s->error = "Fund added successfully.";
  } else {
    $s->error .= ".";
  }
  $response = json_encode($s);
  echo $response;
}
?>