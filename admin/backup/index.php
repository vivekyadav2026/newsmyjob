<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('backup');

$pageTitle = 'Database Backup';
$currentPage = 'backup';

$backupDir = APP_ROOT . '/backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}
$backups = glob($backupDir . '/*.sql') ?: [];
usort($backups, fn($a, $b) => filemtime($b) - filemtime($a));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'backup') {
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $backupDir . '/' . $filename;

        try {
            $pdo = Database::getInstance();
            $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
            $sql = "-- NewsMyJob Backup\n-- Generated: " . date('Y-m-d H:i:s') . "\n\nSET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                $create = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_NUM);
                $sql .= "DROP TABLE IF EXISTS `{$table}`;\n{$create[1]};\n\n";

                $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
                if ($rows) {
                    foreach ($rows as $row) {
                        $values = array_map(function ($val) use ($pdo) {
                            return $val === null ? 'NULL' : $pdo->quote((string) $val);
                        }, array_values($row));
                        $sql .= "INSERT INTO `{$table}` VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $sql .= "\n";
                }
            }
            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

            file_put_contents($filepath, $sql);
            logActivity('backup', 'backup', null, 'Created backup: ' . $filename);
            Session::flash('success', 'Database backup created: ' . $filename);
        } catch (Exception $e) {
            Session::flash('error', 'Backup failed: ' . $e->getMessage());
        }
        redirect(adminUrl('backup/index.php'));
    }

    if ($action === 'restore') {
        $file = basename($_POST['backup_file'] ?? '');
        $filepath = $backupDir . '/' . $file;

        if (!$file || !preg_match('/^backup_[\d\-_]+\.sql$/', $file) || !file_exists($filepath)) {
            Session::flash('error', 'Invalid backup file.');
            redirect(adminUrl('backup/index.php'));
        }

        try {
            $pdo = Database::getInstance();
            $sql = file_get_contents($filepath);
            $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
            $pdo->exec($sql);
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
            logActivity('restore', 'backup', null, 'Restored from: ' . $file);
            Session::flash('success', 'Database restored from ' . $file);
        } catch (Exception $e) {
            Session::flash('error', 'Restore failed: ' . $e->getMessage());
        }
        redirect(adminUrl('backup/index.php'));
    }

    if ($action === 'delete') {
        $file = basename($_POST['backup_file'] ?? '');
        $filepath = $backupDir . '/' . $file;
        if ($file && preg_match('/^backup_[\d\-_]+\.sql$/', $file) && file_exists($filepath)) {
            unlink($filepath);
            logActivity('delete', 'backup', null, 'Deleted backup: ' . $file);
            Session::flash('success', 'Backup deleted.');
        }
        redirect(adminUrl('backup/index.php'));
    }

    if ($action === 'upload' && !empty($_FILES['sql_file']['name'])) {
        $ext = strtolower(pathinfo($_FILES['sql_file']['name'], PATHINFO_EXTENSION));
        if ($ext !== 'sql') {
            Session::flash('error', 'Only .sql files allowed.');
        } else {
            $filename = 'upload_' . date('Y-m-d_H-i-s') . '.sql';
            if (move_uploaded_file($_FILES['sql_file']['tmp_name'], $backupDir . '/' . $filename)) {
                Session::flash('success', 'SQL file uploaded as ' . $filename);
            } else {
                Session::flash('error', 'Upload failed.');
            }
        }
        redirect(adminUrl('backup/index.php'));
    }
}

require APP_ROOT . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Database Backup & Restore</h1>
</div>

<?= renderFlash() ?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white"><h5 class="mb-0">Create Backup</h5></div>
            <div class="card-body">
                <p class="text-muted small">Export all database tables to a SQL file.</p>
                <form method="POST"><?= csrfField() ?>
                    <input type="hidden" name="action" value="backup">
                    <button type="submit" class="btn btn-danger w-100"><i class="bi bi-download"></i> Create Backup Now</button>
                </form>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h5 class="mb-0">Upload SQL File</h5></div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data"><?= csrfField() ?>
                    <input type="hidden" name="action" value="upload">
                    <input type="file" name="sql_file" class="form-control mb-3" accept=".sql" required>
                    <button type="submit" class="btn btn-outline-primary w-100">Upload</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h5 class="mb-0">Available Backups</h5></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Filename</th><th>Size</th><th>Date</th><th width="200">Actions</th></tr></thead>
                    <tbody>
                        <?php if (empty($backups)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">No backups yet</td></tr>
                        <?php else: foreach ($backups as $file): ?>
                        <?php $name = basename($file); ?>
                        <tr>
                            <td><code><?= e($name) ?></code></td>
                            <td><?= formatFileSize(filesize($file)) ?></td>
                            <td><?= date('M d, Y H:i', filemtime($file)) ?></td>
                            <td>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Restore will overwrite current data. Continue?')">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="restore">
                                    <input type="hidden" name="backup_file" value="<?= e($name) ?>">
                                    <button type="submit" class="btn btn-sm btn-warning">Restore</button>
                                </form>
                                <a href="<?= url('backups/' . rawurlencode($name)) ?>" class="btn btn-sm btn-outline-primary" download>Download</a>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete backup?')">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="backup_file" value="<?= e($name) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="alert alert-warning mt-3 small"><i class="bi bi-exclamation-triangle"></i> Always create a fresh backup before restoring. Restore will replace all current database data.</div>
    </div>
</div>

<?php require APP_ROOT . '/includes/footer.php'; ?>
