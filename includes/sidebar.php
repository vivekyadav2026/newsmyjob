<?php
/**
 * Admin Sidebar
 */
$user = currentUser();
?>
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
        <a href="<?= adminUrl('dashboard.php') ?>">
            <i class="bi bi-newspaper"></i>
            <span><?= e(getSetting('site_name', 'NewsMyJob')) ?></span>
        </a>
    </div>
    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            <?php if (hasPermission('dashboard')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>" href="<?= adminUrl('dashboard.php') ?>">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <?php endif; ?>

            <?php if (hasPermission('news')): ?>
            <li class="nav-item">
                <a class="nav-link <?= str_starts_with($currentPage, 'news') ? 'active' : '' ?>" href="<?= adminUrl('news/index.php') ?>">
                    <i class="bi bi-file-earmark-text"></i> News
                </a>
            </li>
            <?php endif; ?>

            <?php if (hasPermission('categories')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'categories' ? 'active' : '' ?>" href="<?= adminUrl('categories/index.php') ?>">
                    <i class="bi bi-folder"></i> Categories
                </a>
            </li>
            <?php endif; ?>

            <?php if (hasPermission('subcategories')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'subcategories' ? 'active' : '' ?>" href="<?= adminUrl('subcategories/index.php') ?>">
                    <i class="bi bi-folder2-open"></i> Sub Categories
                </a>
            </li>
            <?php endif; ?>

            <?php if (hasPermission('breaking')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'breaking' ? 'active' : '' ?>" href="<?= adminUrl('breaking/index.php') ?>">
                    <i class="bi bi-lightning"></i> Breaking News
                </a>
            </li>
            <?php endif; ?>

            <?php if (hasPermission('featured')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'featured' ? 'active' : '' ?>" href="<?= adminUrl('featured/index.php') ?>">
                    <i class="bi bi-star"></i> Featured News
                </a>
            </li>
            <?php endif; ?>

            <?php if (hasPermission('trending')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'trending' ? 'active' : '' ?>" href="<?= adminUrl('trending/index.php') ?>">
                    <i class="bi bi-graph-up-arrow"></i> Trending News
                </a>
            </li>
            <?php endif; ?>

            <?php if (hasPermission('media')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'media' ? 'active' : '' ?>" href="<?= adminUrl('media/index.php') ?>">
                    <i class="bi bi-images"></i> Media Library
                </a>
            </li>
            <?php endif; ?>

            <?php if (hasPermission('comments')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'comments' ? 'active' : '' ?>" href="<?= adminUrl('comments/index.php') ?>">
                    <i class="bi bi-chat-dots"></i> Comments
                </a>
            </li>
            <?php endif; ?>

            <?php if (hasPermission('users')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'users' ? 'active' : '' ?>" href="<?= adminUrl('users/index.php') ?>">
                    <i class="bi bi-people"></i> Users
                </a>
            </li>
            <?php endif; ?>

            <?php if (hasPermission('ads')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'ads' ? 'active' : '' ?>" href="<?= adminUrl('ads/index.php') ?>">
                    <i class="bi bi-badge-ad"></i> Advertisements
                </a>
            </li>
            <?php endif; ?>

            <?php if (hasPermission('settings')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'settings' ? 'active' : '' ?>" href="<?= adminUrl('settings/index.php') ?>">
                    <i class="bi bi-gear"></i> Settings
                </a>
            </li>
            <?php endif; ?>

            <?php if (hasPermission('seo')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'seo' ? 'active' : '' ?>" href="<?= adminUrl('seo/index.php') ?>">
                    <i class="bi bi-search"></i> SEO
                </a>
            </li>
            <?php endif; ?>

            <?php if (hasPermission('reports')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'reports' ? 'active' : '' ?>" href="<?= adminUrl('reports/index.php') ?>">
                    <i class="bi bi-bar-chart"></i> Reports
                </a>
            </li>
            <?php endif; ?>

            <?php if (hasPermission('backup')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'backup' ? 'active' : '' ?>" href="<?= adminUrl('backup/index.php') ?>">
                    <i class="bi bi-database"></i> Backup
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
</aside>
