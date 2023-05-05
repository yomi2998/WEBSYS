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
    $event_id = $data['event_id'];
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
    $timestamp = (int) strtotime($event_date_time);
    date_default_timezone_set('Asia/Singapore');
    $curtimestamp = (int) strtotime(date('Y-m-d H:i:s'));
    if ($timestamp < $curtimestamp + 604800) {
        $s->flag = 1;
        $s->error = "Error: Event date and time cannot be earlier than 7 days before current date and time.";
    }
    if ($s->flag != 1) {
        $conn = new mysqli($myphpadmin_host, $myphpadmin_username, $myphpadmin_password, $myphpadmin_dbname);

        $sql = "SELECT * FROM events WHERE event_id = ";
        $sql .= '"';
        $sql .= $data['event_id'];
        $sql .= '"';
        $result = $conn->query($sql);
        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        if (count($rows) >= 1) {
            $s->flag = 1;
            $s->error = "Error: Event ID already exists.";
        } else {
            $sql = "INSERT INTO events (event_id, event_name, event_description, event_location, event_price, event_ticket_limit, event_ticket_purchased, event_date_time, image_link) VALUES (";
            $sql .= "'$event_id', '$event_name', '$event_description', '$event_location', '$event_price', '$event_ticket_limit', '0', '$event_date_time', '$image_link')";
            $result = $conn->query($sql);
            if (!$result) {
                $s->flag = 1;
                $s->error = "Error: Failed to add event.";
            }
        }
        $conn->close();
        if ($s->flag == 0)
            $s->error = "Event successfully added.";
    }

    $json = json_encode($s);
    header('Content-Type: application/json');
    echo $json;
}
?>