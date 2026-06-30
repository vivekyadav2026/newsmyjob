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

    // Helper Toast function
    function showToast(message, type) {
        const $toast = $('<div class="alert alert-' + type + ' position-fixed bottom-0 end-0 m-3 shadow" style="z-index:9999">' + message + '</div>');
        $('body').append($toast);
        setTimeout(function() { $toast.fadeOut(function() { $(this).remove(); }); }, 3000);
    }

    // Bookmark toggle global
    $(document).on('click', '.btn-bookmark', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const newsId = $btn.data('news-id');
        if (!newsId) return;

        if ($btn.hasClass('disabled')) return;
        $btn.addClass('disabled');

        $.post(BASE_URL + '/ajax/bookmark.php', {
            news_id: newsId,
            _csrf_token: typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : ''
        })
        .done(function(res) {
            if (res.success) {
                const $icon = $btn.find('i');
                if (res.bookmarked) {
                    $btn.removeClass('text-muted').addClass('text-primary');
                    $icon.removeClass('bi-bookmark').addClass('bi-bookmark-fill');
                    if($btn.find('span').length) $btn.find('span').text('Saved');
                } else {
                    $btn.removeClass('text-primary').addClass('text-muted');
                    $icon.removeClass('bi-bookmark-fill').addClass('bi-bookmark');
                    if($btn.find('span').length) $btn.find('span').text('Save');
                }
                showToast(res.message, 'success');
            }
        })
        .fail(function(xhr) {
            const res = xhr.responseJSON;
            if (res && res.login_required) {
                window.location.href = BASE_URL + '/login.php';
            } else {
                showToast(res?.message || 'Bookmark failed.', 'danger');
            }
        })
        .always(function() {
            $btn.removeClass('disabled');
        });
    });

    // Share logic global
    $(document).on('click', '.btn-share', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const url = $btn.data('share-url');
        const title = $btn.data('share-title');

        if (!url || !title) return;

        if (navigator.share) {
            navigator.share({
                title: title,
                url: url
            }).catch(console.error);
        } else {
            $('#shareFacebook').attr('href', 'https://facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url));
            $('#shareTwitter').attr('href', 'https://twitter.com/intent/tweet?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(title));
            $('#shareWhatsapp').attr('href', 'https://wa.me/?text=' + encodeURIComponent(title) + '%20' + encodeURIComponent(url));
            $('#shareTelegram').attr('href', 'https://t.me/share/url?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(title));
            $('#shareLinkedin').attr('href', 'https://www.linkedin.com/shareArticle?mini=true&url=' + encodeURIComponent(url) + '&title=' + encodeURIComponent(title));
            $('#shareLinkInput').val(url);
            $('#shareModal').modal('show');
        }
    });

    $('#copyShareLink').on('click', function() {
        const input = document.getElementById('shareLinkInput');
        input.select();
        input.setSelectionRange(0, 99999);
        document.execCommand("copy");
        showToast('Link copied to clipboard!', 'success');
    });

    // Load More functionality
    let currentPage = 1;
    $('#loadMoreLatest').on('click', function() {
        const $btn = $(this);
        if ($btn.hasClass('disabled')) return;
        $btn.addClass('disabled');
        $btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Loading...');

        currentPage++;
        
        $.getJSON(BASE_URL + '/ajax/load_more.php', { page: currentPage })
        .done(function(res) {
            if (res.success && res.data.length > 0) {
                let html = '';
                res.data.forEach(function(item) {
                    html += `
                    <div class="news-card-hz" style="display:none;">
                        <div class="img-wrapper">
                            <a href="${item.url}">
                                <img src="${item.featured_image}" alt="${item.title}" loading="lazy">
                            </a>
                        </div>
                        <div class="content">
                            ${item.category_name ? `<div class="category">${item.category_name}</div>` : ''}
                            <h5><a href="${item.url}">${item.title}</a></h5>
                            <p class="text-muted small mb-2 d-none d-md-block">${item.excerpt}</p>
                            <div class="meta d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-clock me-1"></i>${item.published_at}
                                    <span class="mx-2 text-muted">|</span>
                                    <i class="bi bi-eye me-1"></i>${item.views}
                                </div>
                                <div class="actions">
                                    <a href="#" class="text-muted me-2 btn-share" data-share-url="${item.url}" data-share-title="${item.title}" title="Share"><i class="bi bi-share"></i></a>
                                    <a href="#" class="text-muted btn-bookmark" data-news-id="${item.id}" title="Save">
                                        <i class="bi bi-bookmark"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>`;
                });
                const $newItems = $(html);
                $btn.parent().before($newItems);
                $newItems.fadeIn(400);

                if (!res.hasMore) {
                    $btn.parent().fadeOut();
                }
            } else {
                $btn.parent().fadeOut();
            }
        })
        .fail(function() {
            showToast('Failed to load more news.', 'danger');
            currentPage--;
        })
        .always(function() {
            $btn.removeClass('disabled');
            $btn.html('<i class="bi bi-arrow-clockwise me-1"></i> Load More');
        });
    });
});
