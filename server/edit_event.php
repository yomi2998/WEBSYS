<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $s = new stdClass();
    $s->flag = 0;
    $data = json_decode(file_get_contents('php://input'), true);
    $myphpadmin_host = "127.0.0.1:3306";
    $myphpadmin_username = "root";
    $myphpadmin_password = "";
    $myphpadmin_dbname = "fun_fair";
    $event_name = $data['event_name'];
    $event_description = $data['event_description'];
    $event_location = $data['event_location'];
    $event_price = $data['event_price'];
    $event_ticket_limit = $data['event_ticket_limit'];
    $event_ticket_purchased = $data['event_ticket_purchased'];
    $image_link = $data['image_link'];
    $event_date_time = $data['event_date_time'];

    $headers = get_headers($image_link);
    if (strpos($headers[0], '200') !== false) {
        $image_info = getimagesize($image_link);
        if ($image_info == false) {
            $s->flag = 1;
            $s->error = "Error: Invalid image.";
        }
    } else {
        $s->flag = 1;
        $s->error = "Error: Image not found.";
    }
    if ($s->flag != 1) {
        $conn = new mysqli($myphpadmin_host, $myphpadmin_username, $myphpadmin_password, $myphpadmin_dbname);
        $event_description_escaped = str_replace("'", "''", $event_description);
        $sql = "UPDATE events SET event_name = '$event_name', event_description = '$event_description_escaped', event_location = '$event_location', event_price = '$event_price', event_ticket_limit = '$event_ticket_limit', event_ticket_purchased = '$event_ticket_purchased', event_date_time = '$event_date_time', image_link = '$image_link' WHERE event_name = '$event_name'";
        $result = $conn->query($sql);
        if (!$result) {
            $s->flag = 1;
            $s->error = "Error: Failed to edit event.";
        }
        $conn->close();
        if ($s->flag == 0)
            $s->error = "Event successfully edited.";
    }

    $json = json_encode($s);
    header('Content-Type: application/json');
    echo $json;
}
?>