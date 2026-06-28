        </main>
        <footer class="admin-footer text-center py-3 text-muted">
            <small>&copy; <?= date('Y') ?> <?= e(getSetting('site_name', 'NewsMyJob')) ?>. All rights reserved.</small>
        </footer>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="<?= asset('js/admin.js') ?>"></script>
<?php if (!empty($extraScripts)): ?>
    <?= $extraScripts ?>
<?php endif; ?>
</body>
</html>
