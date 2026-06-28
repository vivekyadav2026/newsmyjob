/**
 * Frontend JavaScript
 */
$(document).ready(function() {
    // Dark mode
    const theme = localStorage.getItem('site-theme') || 'light';
    if (theme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
        $('#darkModeBtn i').removeClass('bi-moon').addClass('bi-sun');
    }

    $('#darkModeBtn').on('click', function() {
        const current = document.documentElement.getAttribute('data-theme');
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('site-theme', next);
        $(this).find('i').toggleClass('bi-moon bi-sun');
    });

    // AJAX Live Search
    let searchTimeout;
    $('#liveSearch').on('input', function() {
        const query = $(this).val().trim();
        const dropdown = $('#searchResults');

        clearTimeout(searchTimeout);
        if (query.length < 2) {
            dropdown.hide().empty();
            return;
        }

        searchTimeout = setTimeout(function() {
            $.get(BASE_URL + '/ajax/search.php', { q: query }, function(data) {
                dropdown.empty();
                if (data.results && data.results.length) {
                    data.results.forEach(function(item) {
                        dropdown.append(
                            '<a href="' + item.url + '" class="search-result-item d-block text-decoration-none text-dark">' +
                            '<strong>' + item.title + '</strong>' +
                            '<small class="text-muted d-block">' + item.category + ' · ' + item.date + '</small></a>'
                        );
                    });
                    dropdown.show();
                } else {
                    dropdown.html('<div class="p-3 text-muted">No results found</div>').show();
                }
            }, 'json');
        }, 300);
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.search-wrapper').length) {
            $('#searchResults').hide();
        }
    });

    // Newsletter subscribe
    $('#newsletterForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        $.post(BASE_URL + '/ajax/newsletter.php', form.serialize(), function(data) {
            if (data.success) {
                form.find('.alert').removeClass('alert-danger').addClass('alert-success').text(data.message).show();
                form[0].reset();
            } else {
                form.find('.alert').removeClass('alert-success').addClass('alert-danger').text(data.message).show();
            }
        }, 'json');
    });

    // Contact form
    $('#contactForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        $.post(BASE_URL + '/ajax/contact.php', form.serialize(), function(data) {
            const alert = form.find('.form-alert');
            alert.removeClass('alert-danger alert-success')
                 .addClass(data.success ? 'alert-success' : 'alert-danger')
                 .text(data.message).show();
            if (data.success) form[0].reset();
        }, 'json');
    });

    // Comment form
    $('#commentForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        $.post(BASE_URL + '/ajax/comment.php', form.serialize(), function(data) {
            alert(data.message);
            if (data.success) form[0].reset();
        }, 'json');
    });
});
