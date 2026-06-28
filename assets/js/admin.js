/**
 * Admin Panel JavaScript
 */
$(document).ready(function() {
    // Sidebar toggle for mobile
    $('#sidebarToggle').on('click', function() {
        $('.admin-sidebar').toggleClass('show');
    });

    // Dark mode toggle
    const darkModeToggle = $('#darkModeToggle');
    const html = $('html');
    const savedTheme = localStorage.getItem('admin-theme') || 'light';
    html.attr('data-bs-theme', savedTheme);
    updateDarkModeIcon(savedTheme);

    darkModeToggle.on('click', function() {
        const current = html.attr('data-bs-theme');
        const next = current === 'dark' ? 'light' : 'dark';
        html.attr('data-bs-theme', next);
        localStorage.setItem('admin-theme', next);
        updateDarkModeIcon(next);
    });

    function updateDarkModeIcon(theme) {
        darkModeToggle.find('i').attr('class', theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon');
    }

    // DataTables init
    if ($('.datatable').length) {
        $('.datatable').DataTable({
            responsive: true,
            pageLength: 15,
            order: [[0, 'desc']]
        });
    }

    // Confirm delete
    $(document).on('click', '.btn-delete', function(e) {
        if (!confirm('Are you sure you want to delete this item?')) {
            e.preventDefault();
        }
    });

    // Auto-generate slug
    $('#title').on('blur', function() {
        const slugField = $('#slug');
        if (slugField.length && !slugField.data('manual')) {
            const slug = $(this).val().toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/[\s-]+/g, '-')
                .replace(/^-|-$/g, '');
            slugField.val(slug);
        }
    });

    $('#slug').on('input', function() {
        $(this).data('manual', true);
    });

    // CKEditor init
    if ($('#content').length && typeof ClassicEditor !== 'undefined') {
        ClassicEditor.create(document.querySelector('#content'), {
            toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'insertTable', 'mediaEmbed', 'undo', 'redo']
        }).catch(function(error) { console.error(error); });
    }

    // AJAX setup with CSRF
    $.ajaxSetup({
        headers: { 'X-CSRF-Token': typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : '' }
    });

    // Auto dismiss alerts
    setTimeout(function() {
        $('.alert-dismissible').fadeOut('slow');
    }, 5000);
});
