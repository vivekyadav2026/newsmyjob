<?php
$user = currentUser();
?>
<nav class="admin-navbar navbar navbar-expand-lg">
    <div class="container-fluid">
        <button class="btn btn-link sidebar-toggle d-lg-none" type="button" id="sidebarToggle">
            <i class="bi bi-list fs-4"></i>
        </button>
        <div class="ms-auto d-flex align-items-center gap-3">
            <a href="<?= url() ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-box-arrow-up-right"></i> View Site
            </a>
            <div class="dropdown">
                <button class="btn btn-link dropdown-toggle text-dark text-decoration-none" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle fs-5"></i>
                    <?= e($user['name'] ?? '') ?>
                    <span class="badge bg-secondary"><?= e(ucfirst(str_replace('_', ' ', $user['role']))) ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?= adminUrl('profile.php') ?>"><i class="bi bi-person"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="<?= adminUrl('change-password.php') ?>"><i class="bi bi-key"></i> Change Password</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= adminUrl('logout.php') ?>"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>
