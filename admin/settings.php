<?php
/**
 * Admin - Site Settings
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
Auth::requireAuth();
Auth::requirePermission('settings');

$pageTitle = 'Settings';
$settingsModel = new SettingsModel();
$tab = $_GET['tab'] ?? 'general';
$validTabs = ['general', 'contact', 'social', 'seo', 'appearance', 'homepage'];
if (!in_array($tab, $validTabs, true)) {
    $tab = 'general';
}

$tabGroups = [
    'general'    => 'general',
    'contact'    => 'contact',
    'social'     => 'social',
    'seo'        => 'seo',
    'appearance' => 'appearance',
    'homepage'   => 'homepage',
];

$tabFields = [
    'general' => [
        'site_name' => ['label' => 'Site Name', 'type' => 'text'],
        'site_tagline' => ['label' => 'Tagline', 'type' => 'text'],
        'site_logo' => ['label' => 'Logo', 'type' => 'file'],
        'site_favicon' => ['label' => 'Favicon', 'type' => 'file'],
        'maintenance_mode' => ['label' => 'Maintenance Mode', 'type' => 'checkbox'],
        'maintenance_message' => ['label' => 'Maintenance Message', 'type' => 'textarea'],
        'comments_enabled' => ['label' => 'Enable Comments', 'type' => 'checkbox'],
        'posts_per_page' => ['label' => 'Posts Per Page', 'type' => 'number'],
    ],
    'contact' => [
        'site_email' => ['label' => 'Email', 'type' => 'email'],
        'site_phone' => ['label' => 'Phone', 'type' => 'text'],
        'site_address' => ['label' => 'Address', 'type' => 'textarea'],
    ],
    'social' => [
        'facebook_url' => ['label' => 'Facebook', 'type' => 'url'],
        'twitter_url' => ['label' => 'Twitter/X', 'type' => 'url'],
        'instagram_url' => ['label' => 'Instagram', 'type' => 'url'],
        'youtube_url' => ['label' => 'YouTube', 'type' => 'url'],
        'linkedin_url' => ['label' => 'LinkedIn', 'type' => 'url'],
    ],
    'seo' => [
        'meta_title' => ['label' => 'Default Meta Title', 'type' => 'text'],
        'meta_description' => ['label' => 'Default Meta Description', 'type' => 'textarea'],
        'meta_keywords' => ['label' => 'Default Meta Keywords', 'type' => 'text'],
        'google_analytics' => ['label' => 'Google Analytics ID', 'type' => 'text'],
    ],
    'appearance' => [
        'theme_color' => ['label' => 'Theme Color', 'type' => 'color'],
        'dark_mode_enabled' => ['label' => 'Enable Dark Mode (Frontend)', 'type' => 'checkbox'],
    ],
    'homepage' => [
        'homepage_hero_enabled' => ['label' => 'Hero Section', 'type' => 'checkbox'],
        'homepage_breaking_enabled' => ['label' => 'Breaking News Ticker', 'type' => 'checkbox'],
        'homepage_trending_enabled' => ['label' => 'Trending Section', 'type' => 'checkbox'],
        'homepage_featured_enabled' => ['label' => 'Featured Section', 'type' => 'checkbox'],
        'homepage_newsletter_enabled' => ['label' => 'Newsletter Section', 'type' => 'checkbox'],
    ],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCsrf()) {
        Session::flash('error', 'Invalid security token.');
        redirect(ADMIN_URL . '/settings.php?tab=' . $tab);
    }

    $postTab = $_POST['tab'] ?? 'general';
    if (!in_array($postTab, $validTabs, true)) {
        $postTab = 'general';
    }

    $group = $tabGroups[$postTab];
    $fields = $tabFields[$postTab];
    $settings = [];

    foreach ($fields as $key => $field) {
        if ($field['type'] === 'file') {
            if (!empty($_FILES[$key]['name'])) {
                $upload = uploadFile($_FILES[$key], 'settings', ALLOWED_IMAGE_TYPES, MAX_IMAGE_SIZE);
                if ($upload['success']) {
                    $old = $settingsModel->get($key);
                    if ($old) {
                        deleteUploadedFile($old);
                    }
                    $settings[$key] = $upload['path'];
                }
            }
        } elseif ($field['type'] === 'checkbox') {
            $settings[$key] = isset($_POST[$key]) ? '1' : '0';
        } else {
            $settings[$key] = $_POST[$key] ?? '';
        }
    }

    if ($settings) {
        $settingsModel->setMultiple($settings, $group);
        ActivityLogModel::log(Auth::id(), 'update', 'settings', 'Updated ' . ucfirst($postTab) . ' settings');
        Session::flash('success', 'Settings saved successfully.');
    }

    redirect(ADMIN_URL . '/settings.php?tab=' . $postTab);
}

$currentSettings = $settingsModel->getByGroup($tabGroups[$tab]);
$allSettings = $settingsModel->getAllAsArray();
$currentSettings = array_merge($allSettings, $currentSettings);

require VIEWS_PATH . '/admin/includes/header.php';
require VIEWS_PATH . '/admin/includes/sidebar.php';
?>
<div class="admin-content">
    <?php require VIEWS_PATH . '/admin/includes/navbar.php'; ?>
    <div class="p-4">
        <?php require VIEWS_PATH . '/admin/includes/alerts.php'; ?>

        <h4 class="mb-4">Site Settings</h4>

        <ul class="nav nav-tabs mb-4">
            <?php foreach ($validTabs as $t): ?>
            <li class="nav-item">
                <a class="nav-link <?= $tab === $t ? 'active' : '' ?>" href="<?= ADMIN_URL ?>/settings.php?tab=<?= $t ?>"><?= ucfirst($t) ?></a>
            </li>
            <?php endforeach; ?>
        </ul>

        <div class="content-card">
            <form method="POST" enctype="multipart/form-data">
                <?= Security::csrfField() ?>
                <input type="hidden" name="tab" value="<?= e($tab) ?>">

                <?php foreach ($tabFields[$tab] as $key => $field): ?>
                <div class="mb-3">
                    <label class="form-label"><?= e($field['label']) ?></label>
                    <?php if ($field['type'] === 'textarea'): ?>
                    <textarea name="<?= $key ?>" class="form-control" rows="3"><?= e($currentSettings[$key] ?? '') ?></textarea>
                    <?php elseif ($field['type'] === 'checkbox'): ?>
                    <div class="form-check">
                        <input type="checkbox" name="<?= $key ?>" class="form-check-input" id="<?= $key ?>" value="1" <?= ($currentSettings[$key] ?? '0') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="<?= $key ?>">Enabled</label>
                    </div>
                    <?php elseif ($field['type'] === 'file'): ?>
                    <?php if (!empty($currentSettings[$key])): ?>
                    <div class="mb-2"><img src="<?= uploadUrl($currentSettings[$key]) ?>" alt="" class="img-thumbnail" style="max-height:60px;"></div>
                    <?php endif; ?>
                    <input type="file" name="<?= $key ?>" class="form-control" accept="image/*">
                    <?php else: ?>
                    <input type="<?= $field['type'] ?>" name="<?= $key ?>" class="form-control" value="<?= e($currentSettings[$key] ?? '') ?>">
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

                <button type="submit" class="btn btn-danger"><i class="bi bi-check-lg me-1"></i> Save Settings</button>
            </form>
        </div>
    </div>
</div>
<?php require VIEWS_PATH . '/admin/includes/footer.php'; ?>
