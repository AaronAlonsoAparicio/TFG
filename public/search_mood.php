<?php
session_start();

// Conexión BBDD
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "moodplanned";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    echo json_encode(["error" => "Error de conexión con la base de datos"]);
    exit;
}

// Usuario actual
$current_user_id = $_SESSION['user_id'] ?? 0;

// Recibir emoción (categoría)
$category = $_GET["emotion"] ?? "all";

// Consulta SQL
$sql = " SELECT p.id, p.title, p.description, p.category, p.direccion ,p.image, p.created_by, IFNULL(AVG(r.rating), 0) AS rating
        FROM plans p
        LEFT JOIN reviews r ON p.id = r.plan_id ";

if ($category !== "all") {
    $sql .= " WHERE p.category = ? ";
}

$sql .= " GROUP BY p.id ORDER BY rating DESC ";

$stmt = $conn->prepare($sql);
if ($category !== "all") {
    $stmt->bind_param("s", $category);
}

$stmt->execute();
$result = $stmt->get_result();

$planes = [];

while ($row = $result->fetch_assoc()) {
    // Comprobar si está en favoritos
    $favStmt = $conn->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND plan_id = ?");
    $favStmt->bind_param("ii", $current_user_id, $row['id']);
    $favStmt->execute();
    $favRow = $favStmt->get_result()->fetch_assoc();
    $row['is_favorite'] = $favRow ? true : false;

    // Comprobar si está guardado
    $saveStmt = $conn->prepare("SELECT 1 FROM saved_plans WHERE user_id = ? AND plan_id = ?");
    $saveStmt->bind_param("ii", $current_user_id, $row['id']);
    $saveStmt->execute();
    $saveRow = $saveStmt->get_result()->fetch_assoc();
    $row['is_saved'] = $saveRow ? true : false;

    $planes[] = $row;
}

// Devolver JSON
header('Content-Type: application/json');
echo json_encode($planes, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>

