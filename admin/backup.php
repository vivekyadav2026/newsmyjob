<?php
/**
 * Admin - Database Backup & Restore
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
Auth::requireAuth();
Auth::requirePermission('settings');

$pageTitle = 'Backup & Restore';
$dbConfig = require APP_ROOT . '/config/database.php';
$backupDir = APP_ROOT . '/backups';

if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

/**
 * Export database to SQL string
 */
function exportDatabaseSql(array $config): string
{
    $pdo = Database::getInstance();
    $sql = "-- NewsMyJob Database Backup\n";
    $sql .= '-- Generated: ' . date('Y-m-d H:i:s') . "\n\n";
    $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
        $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
        $sql .= $createStmt['Create Table'] . ";\n\n";

        $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) {
            foreach ($rows as $row) {
                $cols = '`' . implode('`, `', array_keys($row)) . '`';
                $values = array_map(function ($val) use ($pdo) {
                    if ($val === null) {
                        return 'NULL';
                    }
                    return $pdo->quote((string) $val);
                }, array_values($row));
                $sql .= "INSERT INTO `{$table}` ({$cols}) VALUES (" . implode(', ', $values) . ");\n";
            }
            $sql .= "\n";
        }
    }

    $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
    return $sql;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCsrf()) {
        Session::flash('error', 'Invalid security token.');
        redirect(ADMIN_URL . '/backup.php');
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'download') {
        $sql = exportDatabaseSql($dbConfig);
        $filename = 'newsmyjob_backup_' . date('Y-m-d_His') . '.sql';
        file_put_contents($backupDir . '/' . $filename, $sql);

        ActivityLogModel::log(Auth::id(), 'create', 'backup', 'Downloaded database backup: ' . $filename);

        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($sql));
        echo $sql;
        exit;
    }

    if ($action === 'restore' && !empty($_FILES['backup_file']['name'])) {
        $file = $_FILES['backup_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Upload failed.');
            redirect(ADMIN_URL . '/backup.php');
        }

        if ($file['size'] > 50 * 1024 * 1024) {
            Session::flash('error', 'Backup file too large (max 50MB).');
            redirect(ADMIN_URL . '/backup.php');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'sql') {
            Session::flash('error', 'Only .sql files are allowed.');
            redirect(ADMIN_URL . '/backup.php');
        }

        $sqlContent = file_get_contents($file['tmp_name']);
        if (empty($sqlContent)) {
            Session::flash('error', 'Backup file is empty.');
            redirect(ADMIN_URL . '/backup.php');
        }

        try {
            $pdo = Database::getInstance();
            $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
            $pdo->exec($sqlContent);
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1');

            ActivityLogModel::log(Auth::id(), 'update', 'backup', 'Restored database from: ' . $file['name']);
            Session::flash('success', 'Database restored successfully.');
        } catch (Exception $e) {
            Session::flash('error', 'Restore failed: ' . $e->getMessage());
        }
        redirect(ADMIN_URL . '/backup.php');
    }

    if ($action === 'delete_backup') {
        $file = basename($_POST['file'] ?? '');
        $path = $backupDir . '/' . $file;
        if ($file && file_exists($path) && str_ends_with($file, '.sql')) {
            unlink($path);
            ActivityLogModel::log(Auth::id(), 'delete', 'backup', 'Deleted backup: ' . $file);
            Session::flash('success', 'Backup file deleted.');
        }
        redirect(ADMIN_URL . '/backup.php');
    }
}

$backups = [];
if (is_dir($backupDir)) {
    foreach (glob($backupDir . '/*.sql') as $file) {
        $backups[] = [
            'name' => basename($file),
            'size' => filesize($file),
            'date' => filemtime($file),
        ];
    }
    usort($backups, fn($a, $b) => $b['date'] <=> $a['date']);
}

require VIEWS_PATH . '/admin/includes/header.php';
require VIEWS_PATH . '/admin/includes/sidebar.php';
?>
<div class="admin-content">
    <?php require VIEWS_PATH . '/admin/includes/navbar.php'; ?>
    <div class="p-4">
        <?php require VIEWS_PATH . '/admin/includes/alerts.php'; ?>

        <h4 class="mb-4">Backup & Restore</h4>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="content-card mb-4">
                    <h5 class="mb-3"><i class="bi bi-download me-2"></i>Download Backup</h5>
                    <p class="text-muted">Export the entire database as a SQL file. A copy is also saved server-side in the backups folder.</p>
                    <form method="POST">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="action" value="download">
                        <button type="submit" class="btn btn-danger"><i class="bi bi-database-down me-1"></i> Download Database Backup</button>
                    </form>
                </div>

                <div class="content-card">
                    <h5 class="mb-3"><i class="bi bi-upload me-2"></i>Restore Backup</h5>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <strong>Warning:</strong> Restoring will overwrite all current data. Create a backup first.
                    </div>
                    <form method="POST" enctype="multipart/form-data">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="action" value="restore">
                        <div class="mb-3">
                            <input type="file" name="backup_file" class="form-control" accept=".sql" required>
                        </div>
                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('This will overwrite all data. Are you sure?');">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Restore Database
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="content-card">
                    <h5 class="mb-3"><i class="bi bi-archive me-2"></i>Server Backups</h5>
                    <?php if ($backups): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>File</th><th>Size</th><th>Date</th><th></th></tr></thead>
                            <tbody>
                                <?php foreach ($backups as $b): ?>
                                <tr>
                                    <td><code><?= e($b['name']) ?></code></td>
                                    <td><?= formatFileSize((int) $b['size']) ?></td>
                                    <td><?= formatDateTime(date('Y-m-d H:i:s', $b['date'])) ?></td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <?= Security::csrfField() ?>
                                            <input type="hidden" name="action" value="delete_backup">
                                            <input type="hidden" name="file" value="<?= e($b['name']) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger btn-delete"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-muted mb-0">No server-side backups yet. Download a backup to create one.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require VIEWS_PATH . '/admin/includes/footer.php'; ?>
