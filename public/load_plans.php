<?php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'moodplanned';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode([]);
    exit;
}

$limit = intval($_GET['limit'] ?? 8);
$offset = intval($_GET['offset'] ?? 0);

$sql = "
    SELECT p.*, 
           IFNULL(AVG(r.rating),0) AS rating 
    FROM plans p
    LEFT JOIN reviews r ON p.id = r.plan_id
    GROUP BY p.id
    ORDER BY rating DESC
    LIMIT :limit OFFSET :offset
";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$planes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($planes, JSON_UNESCAPED_UNICODE);
