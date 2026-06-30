<?php
/**
 * Admin Sidebar Include
 */
$currentPage = basename($_SERVER['PHP_SELF']);
$user = Auth::user();

$menuItems = [
    ['url' => 'dashboard.php', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard', 'perm' => 'dashboard'],
    ['url' => 'news.php', 'icon' => 'bi-newspaper', 'label' => 'News', 'perm' => 'news'],
    ['url' => 'categories.php', 'icon' => 'bi-folder', 'label' => 'Categories', 'perm' => 'categories'],
    ['url' => 'subcategories.php', 'icon' => 'bi-folder2-open', 'label' => 'Sub Categories', 'perm' => 'subcategories'],
    ['url' => 'breaking-news.php', 'icon' => 'bi-lightning', 'label' => 'Breaking News', 'perm' => 'breaking'],
    ['url' => 'media.php', 'icon' => 'bi-images', 'label' => 'Media Library', 'perm' => 'media'],
    ['url' => 'users.php', 'icon' => 'bi-people', 'label' => 'Users', 'perm' => 'users'],
    ['url' => 'menus.php', 'icon' => 'bi-list', 'label' => 'Menus', 'perm' => 'menus'],
    ['url' => 'advertisements.php', 'icon' => 'bi-badge-ad', 'label' => 'Advertisements', 'perm' => 'ads'],
    ['url' => 'comments.php', 'icon' => 'bi-chat-dots', 'label' => 'Comments', 'perm' => 'comments'],
    ['url' => 'newsletter.php', 'icon' => 'bi-envelope', 'label' => 'Newsletter', 'perm' => 'newsletter'],
    ['url' => 'contacts.php', 'icon' => 'bi-mailbox', 'label' => 'Contact Messages', 'perm' => 'contacts'],
    ['url' => 'reports.php', 'icon' => 'bi-graph-up', 'label' => 'Reports', 'perm' => 'reports'],
    ['url' => 'settings.php', 'icon' => 'bi-gear', 'label' => 'Settings', 'perm' => 'settings'],
    ['url' => 'seo.php', 'icon' => 'bi-search', 'label' => 'SEO & Sitemap', 'perm' => 'seo'],
    ['url' => 'backup.php', 'icon' => 'bi-database', 'label' => 'Backup & Restore', 'perm' => 'settings'],
    ['url' => 'activity-logs.php', 'icon' => 'bi-clock-history', 'label' => 'Activity Logs', 'perm' => 'reports'],
];
?>
<aside class="admin-sidebar bg-dark text-white">
    <div class="sidebar-brand p-3 border-bottom border-secondary">
        <a href="<?= ADMIN_URL ?>/dashboard.php" class="text-white text-decoration-none d-flex align-items-center gap-2">
            <i class="bi bi-newspaper fs-4"></i>
            <span class="fw-bold"><?= e(setting('site_name', 'MyJobHub')) ?></span>
        </a>
    </div>
    <nav class="sidebar-nav p-2">
        <ul class="nav flex-column">
            <?php foreach ($menuItems as $item): ?>
                <?php if (Auth::can($item['perm'])): ?>
                <li class="nav-item">
                    <a href="<?= ADMIN_URL ?>/<?= $item['url'] ?>" class="nav-link text-white-50 <?= $currentPage === $item['url'] ? 'active bg-primary text-white' : '' ?>">
                        <i class="bi <?= $item['icon'] ?> me-2"></i><?= e($item['label']) ?>
                    </a>
                </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </nav>
    <div class="sidebar-footer p-3 border-top border-secondary mt-auto">
        <a href="<?= BASE_URL ?>" target="_blank" class="btn btn-outline-light btn-sm w-100">
            <i class="bi bi-box-arrow-up-right me-1"></i> View Site
        </a>
    </div>
</aside>
