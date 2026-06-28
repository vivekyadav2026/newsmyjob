<?php
/**
 * Admin Navbar Include
 */
$user = Auth::user();
?>
<nav class="admin-navbar navbar navbar-expand-lg navbar-light bg-white border-bottom px-4 py-2">
    <button class="btn btn-link d-lg-none" id="sidebarToggle"><i class="bi bi-list fs-4"></i></button>
    <div class="ms-auto d-flex align-items-center gap-3">
        <button class="btn btn-sm btn-outline-secondary" id="darkModeToggle" title="Toggle Dark Mode">
            <i class="bi bi-moon"></i>
        </button>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                <div class="avatar-circle bg-primary text-white me-2"><?= strtoupper(substr($user['name'] ?? 'A', 0, 1)) ?></div>
                <span class="d-none d-md-inline text-dark"><?= e($user['name'] ?? '') ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><span class="dropdown-item-text text-muted small"><?= e(ucfirst(str_replace('_', ' ', $user['role'] ?? ''))) ?></span></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?= ADMIN_URL ?>/change-password.php"><i class="bi bi-key me-2"></i>Change Password</a></li>
                <li><a class="dropdown-item text-danger" href="<?= ADMIN_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
