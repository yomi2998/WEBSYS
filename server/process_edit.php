<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $s = new stdClass();
  $s->error = "Error: ";
  $s->flag = 0;

  $data = json_decode(file_get_contents('php://input'), true);
  $token = $data['token_id'];
  $birthdate = $data['birthdate'];
  $email = $data['email'];
  $gender = $data['gender'];

  $date = date_create_from_format('Y-m-d', $birthdate);
  $diff = date_diff(date_create(), $date);
  if (
    $diff->y < 18 ||
    ($diff->y == 18 && (($diff->m < 0) || ($diff->m == 0 && $diff->d < 0)))
  ) {
    if (strlen($s->error) > 10) {
      $s->error .= ", ";
    }
    $s->error .= "invalid birth date, you need to be at least 18 years old";
    $s->flag = 1;
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    if (strlen($s->error) > 10) {
      $s->error .= ", ";
    }
    $s->error .= "format of the e-mail should be: *@*.*, for example: john@gmail.com";
    $s->flag = 1;
  }

  if (!$s->flag) {
    $myphpadmin_host = "127.0.0.1:3306";
    $myphpadmin_username = "root";
    $myphpadmin_password = "";
    $myphpadmin_dbname = "fun_fair";

    $conn = new mysqli($myphpadmin_host, $myphpadmin_username, $myphpadmin_password, $myphpadmin_dbname);

    if (!$conn->connect_error) {
      $stmt = $conn->prepare("SELECT * FROM account WHERE email = ?");
      $stmt->bind_param("s", $email);
      $stmt->execute();
      $result1 = $stmt->get_result();

      if ($result1->num_rows > 0) {
        if (strlen($s->error) > 10) {
          $s->error .= ", ";
        }
        $s->flag = 1;
        $s->error = "e-mail is already taken";
      }
      $stmt->close();

      if (!$s->flag) {
        $stmt3 = $conn->prepare("UPDATE account SET gender = ?, email = ?, birthdate = ? WHERE token_id = ?");
        $is_admin = 0;
        $stmt3->bind_param("ssss", $gender, $email, $birthdate, $token);
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
    $s->error = "Account has been edited successfully.";
  } else {
    $s->error .= ".";
  }
  $response = json_encode($s);
  echo $response;
}
?>