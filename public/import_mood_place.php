<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/api_places.php';
require_login();

$place = json_decode($_POST['place'] ?? '', true);

if ($place && import_place_as_plan($pdo, $place, $_SESSION['user_id'])) {
    $msg = "¡Plan importado! +10 puntos";
    $type = "success";
} else {
    $msg = "Error al importar";
    $type = "error";
}

header("Location:./search_mood.php?type=$type&msg=" . urlencode($msg));
exit;