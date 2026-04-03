<?php

$settings = require __DIR__ . '/config/settings.php';
$db = $settings['db'];

try {
    $pdo = new PDO(
        "mysql:host={$db['host']};port={$db['port']};charset={$db['charset']}",
        $db['username'],
        $db['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$db['database']}`");

    // Ensure _migrations table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS _migrations (
        filename VARCHAR(255) PRIMARY KEY,
        applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $migrationsDir = __DIR__ . '/migrations';
    $files = glob($migrationsDir . '/*.sql');
    sort($files);

    foreach ($files as $file) {
        $filename = basename($file);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM _migrations WHERE filename = ?");
        $stmt->execute([$filename]);

        if ((int)$stmt->fetchColumn() > 0) {
            echo "Skipping (already applied): {$filename}\n";
            continue;
        }

        echo "Applying: {$filename}... ";
        $sql = file_get_contents($file);

        // Remove the _migrations CREATE TABLE from the migration file to avoid conflict
        $sql = preg_replace('/CREATE TABLE IF NOT EXISTS _migrations.*?;/s', '', $sql);

        $pdo->exec($sql);
        $pdo->prepare("INSERT INTO _migrations (filename) VALUES (?)")->execute([$filename]);
        echo "Done.\n";
    }

    echo "\nAll migrations applied successfully.\n";
} catch (PDOException $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
    exit(1);
}
