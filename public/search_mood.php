<?php

// Conexión BBDD
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "moodplanned";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo json_encode([
        "error" => "Error de conexión con la base de datos"
    ]);
    exit;
}

// Recibir emoción (categoría)
$category = $_GET["emotion"] ?? "all";
$limit = intval($_GET["limit"] ?? 8);   // Número de planes por "página"
$offset = intval($_GET["offset"] ?? 0); // Desde dónde empezar

$sql = "
    SELECT 
        p.id,
        p.title,
        p.description,
        p.category,
        p.image,
        IFNULL(AVG(r.rating), 0) AS rating
    FROM plans p
    LEFT JOIN reviews r ON p.id = r.plan_id
";

if ($category !== "all") {
    $sql .= " WHERE p.category = ? ";
}

$sql .= " GROUP BY p.id ORDER BY rating DESC LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);

if ($category !== "all") {
    $stmt->bind_param("sii", $category, $limit, $offset);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

$planes = [];
while ($row = $result->fetch_assoc()) {
    $planes[] = $row;
}

echo json_encode($planes, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);


?>
