<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $s = new stdClass();
  $s->error = "Login failed: ";
  $s->token_id = "";
  $s->flag = 0;

  $data = json_decode(file_get_contents('php://input'), true);
  $username = $data['username'];
  $password = $data['password'];

  $myphpadmin_host = "127.0.0.1:3306";
  $myphpadmin_username = "root";
  $myphpadmin_password = "";
  $myphpadmin_dbname = "fun_fair";

  $conn = new mysqli($myphpadmin_host, $myphpadmin_username, $myphpadmin_password, $myphpadmin_dbname);

  if (!$conn->connect_error) {
    $stmt = $conn->prepare("SELECT * FROM account WHERE username = ? AND BINARY password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result1 = $stmt->get_result();
    
    if ($result1->num_rows > 0) {
      while($row = $result1->fetch_assoc()) {
        $s->token_id = $row["token_id"];
      }
    }
    else
    {
      $s->flag = 1;
      $s->error .= "invalid usernname or password";
    }
    $stmt->close();
  } else {
    $s->flag = 1;
    $s->error .= "could not connect to mysqli server";
  }
}


if ($s->flag) {
  $s->error .= ".";
}
$response = json_encode($s);
echo $response;
?>