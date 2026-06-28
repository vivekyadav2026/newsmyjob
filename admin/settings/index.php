<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

Auth::requirePermission('settings');

$pageTitle = 'Website Settings';
$currentPage = 'settings';
$settingsModel = new SettingsModel();
$tab = $_GET['tab'] ?? 'general';

$tabs = [
    'general'    => ['site_name', 'site_tagline', 'site_logo', 'site_favicon', 'footer_text', 'maintenance_mode', 'posts_per_page', 'enable_comments', 'enable_newsletter', 'enable_dark_mode'],
    'contact'    => ['site_email', 'site_phone', 'site_address'],
    'social'     => ['facebook_url', 'twitter_url', 'instagram_url', 'youtube_url', 'linkedin_url'],
    'homepage'   => ['homepage_featured_count', 'homepage_trending_count', 'homepage_breaking_enabled', 'homepage_video_section', 'homepage_category_sections'],
    'appearance' => ['theme_color', 'header_code', 'footer_code'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $activeTab = $_POST['tab'] ?? 'general';
    $group = $activeTab;
    $fields = $tabs[$activeTab] ?? [];

    foreach ($fields as $key) {
        if (in_array($key, ['maintenance_mode', 'enable_comments', 'enable_newsletter', 'enable_dark_mode', 'homepage_breaking_enabled', 'homepage_video_section'], true)) {
            $settingsModel->set($key, isset($_POST[$key]) ? '1' : '0', $group);
        } elseif (in_array($key, ['site_logo', 'site_favicon'], true) && !empty($_FILES[$key]['name'])) {
            $upload = uploadFile($_FILES[$key], 'settings', ALLOWED_IMAGE_TYPES, MAX_IMAGE_SIZE);
            if ($upload['success']) {
                $old = $settingsModel->get($key);
                if ($old) deleteUploadedFile($old);
                $settingsModel->set($key, $upload['path'], $group);
            }
        } elseif (isset($_POST[$key])) {
            $settingsModel->set($key, trim($_POST[$key]), $group);
        }
    }

    logActivity('update', 'settings', null, 'Updated ' . $activeTab . ' settings');
    Session::flash('success', 'Settings saved successfully.');
    redirect(adminUrl('settings/index.php?tab=' . $activeTab));
}

$settings = $settingsModel->getAllAsArray();

require APP_ROOT . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Website Settings</h1>
</div>

<?= renderFlash() ?>

<ul class="nav nav-tabs mb-4">
    <?php foreach (array_keys($tabs) as $t): ?>
    <li class="nav-item"><a class="nav-link <?= $tab === $t ? 'active' : '' ?>" href="<?= adminUrl('settings/index.php?tab=' . $t) ?>"><?= ucfirst($t) ?></a></li>
    <?php endforeach; ?>
</ul>

<div class="card shadow-sm"><div class="card-body">
<form method="POST" enctype="multipart/form-data"><?= csrfField() ?>
<input type="hidden" name="tab" value="<?= e($tab) ?>">

<?php if ($tab === 'general'): ?>
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Site Name</label><input type="text" name="site_name" class="form-control" value="<?= e($settings['site_name'] ?? '') ?>"></div>
    <div class="col-md-6"><label class="form-label">Tagline</label><input type="text" name="site_tagline" class="form-control" value="<?= e($settings['site_tagline'] ?? '') ?>"></div>
    <div class="col-md-6"><label class="form-label">Logo</label><?php if (!empty($settings['site_logo'])): ?><img src="<?= uploadUrl($settings['site_logo']) ?>" class="d-block mb-2" height="40"><?php endif; ?><input type="file" name="site_logo" class="form-control" accept="image/*"></div>
    <div class="col-md-6"><label class="form-label">Favicon</label><?php if (!empty($settings['site_favicon'])): ?><img src="<?= uploadUrl($settings['site_favicon']) ?>" class="d-block mb-2" height="32"><?php endif; ?><input type="file" name="site_favicon" class="form-control" accept="image/*"></div>
    <div class="col-12"><label class="form-label">Footer Text</label><input type="text" name="footer_text" class="form-control" value="<?= e($settings['footer_text'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">Posts Per Page</label><input type="number" name="posts_per_page" class="form-control" value="<?= e($settings['posts_per_page'] ?? '12') ?>"></div>
    <div class="col-md-8 d-flex align-items-end gap-3">
        <div class="form-check"><input type="checkbox" name="maintenance_mode" class="form-check-input" id="mm" <?= ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : '' ?>><label class="form-check-label" for="mm">Maintenance Mode</label></div>
        <div class="form-check"><input type="checkbox" name="enable_comments" class="form-check-input" id="ec" <?= ($settings['enable_comments'] ?? '1') === '1' ? 'checked' : '' ?>><label class="form-check-label" for="ec">Comments</label></div>
        <div class="form-check"><input type="checkbox" name="enable_newsletter" class="form-check-input" id="en" <?= ($settings['enable_newsletter'] ?? '1') === '1' ? 'checked' : '' ?>><label class="form-check-label" for="en">Newsletter</label></div>
        <div class="form-check"><input type="checkbox" name="enable_dark_mode" class="form-check-input" id="edm" <?= ($settings['enable_dark_mode'] ?? '1') === '1' ? 'checked' : '' ?>><label class="form-check-label" for="edm">Dark Mode</label></div>
    </div>
</div>

<?php elseif ($tab === 'contact'): ?>
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="site_email" class="form-control" value="<?= e($settings['site_email'] ?? '') ?>"></div>
    <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="site_phone" class="form-control" value="<?= e($settings['site_phone'] ?? '') ?>"></div>
    <div class="col-12"><label class="form-label">Address</label><textarea name="site_address" class="form-control" rows="3"><?= e($settings['site_address'] ?? '') ?></textarea></div>
</div>

<?php elseif ($tab === 'social'): ?>
<div class="row g-3">
    <?php foreach (['facebook_url' => 'Facebook', 'twitter_url' => 'Twitter/X', 'instagram_url' => 'Instagram', 'youtube_url' => 'YouTube', 'linkedin_url' => 'LinkedIn'] as $key => $label): ?>
    <div class="col-md-6"><label class="form-label"><?= $label ?> URL</label><input type="url" name="<?= $key ?>" class="form-control" value="<?= e($settings[$key] ?? '') ?>"></div>
    <?php endforeach; ?>
</div>

<?php elseif ($tab === 'homepage'): ?>
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Featured News Count</label><input type="number" name="homepage_featured_count" class="form-control" value="<?= e($settings['homepage_featured_count'] ?? '5') ?>" min="1"></div>
    <div class="col-md-6"><label class="form-label">Trending News Count</label><input type="number" name="homepage_trending_count" class="form-control" value="<?= e($settings['homepage_trending_count'] ?? '8') ?>" min="1"></div>
    <div class="col-md-6"><label class="form-label">Category Sections Count</label><input type="number" name="homepage_category_sections" class="form-control" value="<?= e($settings['homepage_category_sections'] ?? '4') ?>" min="1"></div>
    <div class="col-md-6 d-flex align-items-end gap-3">
        <div class="form-check"><input type="checkbox" name="homepage_breaking_enabled" class="form-check-input" id="hbe" <?= ($settings['homepage_breaking_enabled'] ?? '1') === '1' ? 'checked' : '' ?>><label class="form-check-label" for="hbe">Show Breaking News</label></div>
        <div class="form-check"><input type="checkbox" name="homepage_video_section" class="form-check-input" id="hvs" <?= ($settings['homepage_video_section'] ?? '1') === '1' ? 'checked' : '' ?>><label class="form-check-label" for="hvs">Video Section</label></div>
    </div>
</div>

<?php elseif ($tab === 'appearance'): ?>
<div class="row g-3">
    <div class="col-md-4"><label class="form-label">Theme Color</label><input type="color" name="theme_color" class="form-control form-control-color" value="<?= e($settings['theme_color'] ?? '#dc3545') ?>"></div>
    <div class="col-12"><label class="form-label">Header Code (analytics, etc.)</label><textarea name="header_code" class="form-control font-monospace" rows="4"><?= e($settings['header_code'] ?? '') ?></textarea></div>
    <div class="col-12"><label class="form-label">Footer Code</label><textarea name="footer_code" class="form-control font-monospace" rows="4"><?= e($settings['footer_code'] ?? '') ?></textarea></div>
</div>
<?php endif; ?>

<button type="submit" class="btn btn-danger mt-4">Save Settings</button>
</form></div></div>

<?php require APP_ROOT . '/includes/footer.php'; ?>
