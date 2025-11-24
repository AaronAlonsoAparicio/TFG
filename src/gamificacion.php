<?php
// src/gamification.php
require_once __DIR__ . '/config.php'; // toma $pdo definido ahí

/**
 * Añade puntos y registra historial
 */
function award_points(PDO $pdo, int $userId, int $points, string $reason) {
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("UPDATE users SET points = points + :p WHERE id = :id");
        $stmt->execute([':p' => $points, ':id' => $userId]);

        $stmt = $pdo->prepare("INSERT INTO points_history (user_id, points_delta, reason) VALUES (:uid, :delta, :reason)");
        $stmt->execute([':uid' => $userId, ':delta' => $points, ':reason' => $reason]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Comprueba badges por puntos y asigna
 */
function check_and_award_badges(PDO $pdo, int $userId) {
    $stmt = $pdo->prepare("SELECT points FROM users WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    $points = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT id FROM badges WHERE required_points <= :p");
    $stmt->execute([':p' => $points]);
    $badgeIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($badgeIds as $bid) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO user_badges (user_id, badge_id) VALUES (:uid, :bid)");
        $stmt->execute([':uid' => $userId, ':bid' => $bid]);
    }
}

/**
 * Otorga achievement por trigger_event
 */
function award_achievement_if_not(PDO $pdo, int $userId, string $trigger_event) {
    $stmt = $pdo->prepare("SELECT id FROM achievements WHERE trigger_event = :evt LIMIT 1");
    $stmt->execute([':evt' => $trigger_event]);
    $aid = $stmt->fetchColumn();
    if (!$aid) return;

    $stmt = $pdo->prepare("INSERT IGNORE INTO user_achievements (user_id, achievement_id) VALUES (:u, :a)");
    $stmt->execute([':u' => $userId, ':a' => $aid]);
}

/**
 * Devuelve achievements del usuario con estado
 */
function get_user_achievements(PDO $pdo, int $userId) {
    $sql = "SELECT a.id, a.name, a.description, a.icon,
                   (ua.id IS NOT NULL) AS unlocked, ua.earned_at
            FROM achievements a
            LEFT JOIN user_achievements ua ON a.id = ua.achievement_id AND ua.user_id = :uid
            ORDER BY a.id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':uid' => $userId]);
    return $stmt->fetchAll();
}

/**
 * Devuelve badges del usuario
 */
function get_user_badges(PDO $pdo, int $userId) {
    $sql = "SELECT b.id, b.name, b.description, b.icon, b.required_points, ub.earned_at
            FROM badges b
            LEFT JOIN user_badges ub ON ub.badge_id = b.id AND ub.user_id = :uid
            ORDER BY b.required_points";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':uid' => $userId]);
    return $stmt->fetchAll();
}

/**
 * Registrar actividad diaria
 */
function register_daily_activity(PDO $pdo, int $userId, string $type) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO user_activity (user_id, activity_date, activity_type) VALUES (:u, CURDATE(), :t)");
    $stmt->execute([':u' => $userId, ':t' => $type]);
}

/**
 * Calcula streak (días consecutivos)
 */
function get_current_streak(PDO $pdo, int $userId) {
    $days = 0;
    $date = new DateTime();
    while (true) {
        $d = $date->format('Y-m-d');
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_activity WHERE user_id = :u AND activity_date = :d");
        $stmt->execute([':u' => $userId, ':d' => $d]);
        if ((int)$stmt->fetchColumn() > 0) {
            $days++;
            $date->modify('-1 day');
            if ($days > 365) break;
        } else break;
    }
    return $days;
}

/**
 * Evalúa logros globales (streak, 3 moods distinct)
 */
function evaluate_and_award_cross_achievements(PDO $pdo, int $userId) {
    if (get_current_streak($pdo, $userId) >= 7) {
        award_achievement_if_not($pdo, $userId, '7_day_streak');
    }

    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT mood) FROM moods WHERE user_id = :u");
    $stmt->execute([':u' => $userId]);
    if ((int)$stmt->fetchColumn() >= 3) {
        award_achievement_if_not($pdo, $userId, '3_distinct_moods');
    }
}
