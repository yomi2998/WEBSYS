<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $s = new stdClass();
  $s->error = "Error: ";
  $s->flag = 0;

  $data = json_decode(file_get_contents('php://input'), true);
  $username = $data['username'];
  $password = $data['password'];
  $birthdate = $data['birthdate'];
  $email = $data['email'];
  $gender = $data['gender'];

  if (!preg_match('/^[A-Za-z0-9]{3,16}$/', $username)) {
    $s->error .= "username must alphanumeric, shorter than 16 character and not shorter than 3 characters";
    $s->flag = 1;
  }

  if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,16}$/', $password)) {
    if (strlen($s->error) > 10) {
      $s->error .= ", ";
    }
    $s->error .= "password must contains alphanumeric, lowercase and uppercase, symbols, shorter than 16 characters and not shorter than 8 characters";
    $s->flag = 1;
  }

  $date = date_create_from_format('Y-m-d', $birthdate);
  $diff = date_diff(date_create(), $date);
  if (
    $diff->y < 18 ||
    ($diff->y == 18 && (($diff->m < 0) || ($diff->m == 0 && $diff->d < 0)))
  ) {
    if (strlen($s->error) > 10) {
      $s->error .= ", ";
    }
    $s->error .= "you must be at least 18 years old to register this account";
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

      $stmt2 = $conn->prepare("SELECT * FROM account WHERE username = ?");
      $stmt2->bind_param("s", $username);
      $stmt2->execute();
      $result2 = $stmt2->get_result();

      if ($result2->num_rows > 0) {
        $s->flag = 1;
        $s->error = "username is already taken";
      }

      if ($result1->num_rows > 0) {
        if (strlen($s->error) > 10) {
          $s->error .= ", ";
        }
        $s->flag = 1;
        $s->error = "e-mail is already taken";
      }
      $stmt->close();
      $stmt2->close();

      if (!$s->flag) {
        $lth = 16;
        $bytes = openssl_random_pseudo_bytes($lth);
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz!@#$%^&*()_-=+[]{}|;:,.<>?';
        $token = '';

        for ($i = 0; $i < $lth; $i++) {
          $index = ord($bytes[$i]) % strlen($chars);
          $token .= substr($chars, $index, 1);
        }
        $stmt3 = $conn->prepare("INSERT INTO account (username, password, gender, email, birthdate, is_admin, token_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $is_admin = 0;
        $stmt3->bind_param("sssssis", $username, $password, $gender, $email, $birthdate, $is_admin, $token);
        $stmt3->execute();

        if (!$stmt3->affected_rows) {
          $s->flag = 1;
          $s->error .= "failed to create account from database";
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
    $s->error = "Account has been registered successfully, please log in on the page.";
  } else {
    $s->error .= ".";
  }
  $response = json_encode($s);
  echo $response;
}
?>