<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $s = new stdClass();
  $s->error = "Error: ";
  $s->flag = 0;

  $data = json_decode(file_get_contents('php://input'), true);
  $password = $data['new_password'];
  $token = $data['token_id'];

  if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,16}$/', $password)) {
    if (strlen($s->error) > 10) {
      $s->error .= ", ";
    }
    $s->error .= "password must contains alphanumeric, lowercase and uppercase, symbols, shorter than 16 characters and not shorter than 8 characters";
    $s->flag = 1;
  }

  if (!$s->flag) {
    $myphpadmin_host = "127.0.0.1:3306";
    $myphpadmin_username = "root";
    $myphpadmin_password = "";
    $myphpadmin_dbname = "fun_fair";

    $conn = new mysqli($myphpadmin_host, $myphpadmin_username, $myphpadmin_password, $myphpadmin_dbname);

    if (!$conn->connect_error) {
      if (!$s->flag) {
        $stmt3 = $conn->prepare("UPDATE account SET password = ? WHERE token_id = ?");
        $is_admin = 0;
        $stmt3->bind_param("ss", $password, $token);
        $stmt3->execute();

        if (!$stmt3->affected_rows) {
          $s->flag = 1;
          $s->error .= "failed to edit account details from database";
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
    $s->error = "Password changed successfully.";
  } else {
    $s->error .= ".";
  }
  $response = json_encode($s);
  echo $response;
}
?>