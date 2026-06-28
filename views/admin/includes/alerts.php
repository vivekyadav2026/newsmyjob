<?php
/**
 * Admin Panel Flash Messages
 */
$success = Session::getFlash('success');
$error = Session::getFlash('error');
$warning = Session::getFlash('warning');
?>
<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= e($success) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= e($error) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if ($warning): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <?= e($warning) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
