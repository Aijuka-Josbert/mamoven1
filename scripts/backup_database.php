<?php
/**
 * Automated database backup.
 *
 * Run manually:
 *   php scripts/backup_database.php
 *
 * Run automatically via cron (daily at 2am, keeping the last 14 backups):
 *   0 2 * * * php /full/path/to/mamoven1/scripts/backup_database.php >> /full/path/to/mamoven1/backups/backup.log 2>&1
 *
 * On shared hosting without shell/cron access (e.g. InfinityFree free tier),
 * use a free external cron service (cron-job.org) to hit a protected URL
 * that runs this script instead — see README for the exact setup.
 *
 * Pure PHP, no dependency on the `mysqldump` binary being available or
 * whitelisted on shared hosting.
 */

require_once __DIR__ . '/../config/database.php';

define('BACKUP_RETENTION_DAYS', 14);
$backupDir = __DIR__ . '/../backups';

if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

function backup_log(string $message): void
{
    echo '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
}

try {
    $tablesStmt = $pdo->query('SHOW TABLES');
    $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

    $timestamp = date('Y-m-d_His');
    $filename = $backupDir . '/backup_' . $timestamp . '.sql';
    $handle = fopen($filename, 'w');

    if (!$handle) {
        throw new RuntimeException('Could not open backup file for writing: ' . $filename);
    }

    fwrite($handle, "-- Mama's Oven database backup\n-- Generated: " . date('Y-m-d H:i:s') . "\n\n");
    fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

    foreach ($tables as $table) {
        backup_log("Backing up table: $table");

        // Schema
        $createStmt = $pdo->query("SHOW CREATE TABLE `$table`")->fetch();
        fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n");
        fwrite($handle, $createStmt['Create Table'] . ";\n\n");

        // Data, in chunks so large tables don't exhaust memory
        $rowCount = (int)$pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        if ($rowCount === 0) {
            continue;
        }

        $chunkSize = 500;
        for ($offset = 0; $offset < $rowCount; $offset += $chunkSize) {
            $rows = $pdo->query("SELECT * FROM `$table` LIMIT $chunkSize OFFSET $offset")->fetchAll(PDO::FETCH_ASSOC);
            if (empty($rows)) {
                break;
            }

            $columns = array_keys($rows[0]);
            $columnList = '`' . implode('`, `', $columns) . '`';
            $valueRows = [];

            foreach ($rows as $row) {
                $escaped = array_map(function ($value) use ($pdo) {
                    return $value === null ? 'NULL' : $pdo->quote((string)$value);
                }, $row);
                $valueRows[] = '(' . implode(', ', $escaped) . ')';
            }

            fwrite($handle, "INSERT INTO `$table` ($columnList) VALUES\n" . implode(",\n", $valueRows) . ";\n\n");
        }
    }

    fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
    fclose($handle);

    $sizeKb = round(filesize($filename) / 1024, 1);
    backup_log("Backup complete: $filename ({$sizeKb} KB)");

    // Rotate: delete backups older than the retention window.
    $cutoff = time() - (BACKUP_RETENTION_DAYS * 86400);
    foreach (glob($backupDir . '/backup_*.sql') as $oldFile) {
        if (filemtime($oldFile) < $cutoff) {
            unlink($oldFile);
            backup_log('Removed old backup: ' . basename($oldFile));
        }
    }
} catch (Throwable $e) {
    backup_log('BACKUP FAILED: ' . $e->getMessage());
    error_log('Database backup failed: ' . $e->getMessage());
    exit(1);
}
