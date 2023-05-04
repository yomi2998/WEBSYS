<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $s = new stdClass();
  $s->flag = 0;
  $s->admin = 0;
  $data = json_decode(file_get_contents('php://input'), true);
  $token = $data['token_id'];

  $myphpadmin_host = "127.0.0.1:3306";
  $myphpadmin_username = "root";
  $myphpadmin_password = "";
  $myphpadmin_dbname = "fun_fair";

  $conn = new mysqli($myphpadmin_host, $myphpadmin_username, $myphpadmin_password, $myphpadmin_dbname);

  if (!$conn->connect_error) {
    $stmt = $conn->prepare("SELECT * FROM account WHERE token_id = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result1 = $stmt->get_result();
    
    if (!$result1->num_rows) {
      $s->flag = 1;
    }
    $row = $result1->fetch_assoc();
    $s->admin = $row["is_admin"];
    $stmt->close();
  } else {
    $s->flag = 1;
  }
}
$response = json_encode($s);
echo $response;
?>