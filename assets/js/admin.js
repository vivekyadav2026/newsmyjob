/**
 * Admin Panel JavaScript
 */
$(document).ready(function() {
    // Sidebar toggle for mobile
    $('#sidebarToggle').on('click', function(e) {
        e.stopPropagation();
        $('.admin-sidebar').toggleClass('show');
        toggleBackdrop();
    });

    function toggleBackdrop() {
        if ($('.admin-sidebar').hasClass('show') && $(window).width() < 992) {
            if (!$('.sidebar-backdrop').length) {
                $('body').append('<div class="sidebar-backdrop"></div>');
                $('.sidebar-backdrop').fadeIn(200);
            }
        } else {
            $('.sidebar-backdrop').fadeOut(200, function() {
                $(this).remove();
            });
        }
    }

    // Close sidebar when clicking backdrop
    $(document).on('click', '.sidebar-backdrop', function() {
        $('.admin-sidebar').removeClass('show');
        toggleBackdrop();
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
    if (typeof ClassicEditor !== 'undefined') {
        $('.editor').each(function() {
            ClassicEditor.create(this, {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'insertTable', 'mediaEmbed', 'undo', 'redo']
            }).catch(function(error) { console.error(error); });
        });
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
