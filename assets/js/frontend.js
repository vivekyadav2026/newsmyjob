/**
 * Frontend JavaScript - NewsMyJob CMS
 */
(function($) {
    'use strict';

    const AJAX_BASE = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/ajax';

    // Dark mode toggle
    function initDarkMode() {
        const toggle = $('#darkModeToggle');
        const html = $('html');
        const saved = localStorage.getItem('frontend-theme') || 'light';

        if (getSettingEnabled('dark_mode_enabled')) {
            html.attr('data-theme', saved);
            updateDarkIcon(saved);
        }

        toggle.on('click', function() {
            const current = html.attr('data-theme') || 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            html.attr('data-theme', next);
            localStorage.setItem('frontend-theme', next);
            updateDarkIcon(next);
        });

        function updateDarkIcon(theme) {
            toggle.find('i').attr('class', theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon');
        }
    }

    function getSettingEnabled(key) {
        return true;
    }

    // Live search
    function initLiveSearch() {
        let timer = null;

        function bindSearch(inputSelector, resultsSelector) {
            const $input = $(inputSelector);
            const $results = $(resultsSelector);
            if (!$input.length) return;

            const $wrapper = $input.closest('.input-group, .search-form, form, .d-flex');
            if ($wrapper.length && !$wrapper.hasClass('search-form')) {
                $wrapper.css('position', 'relative');
            }

            $input.on('input', function() {
                const q = $(this).val().trim();
                clearTimeout(timer);

                if (q.length < 2) {
                    $results.removeClass('show').empty();
                    return;
                }

                timer = setTimeout(function() {
                    $.getJSON(AJAX_BASE + '/search.php', { q: q })
                        .done(function(res) {
                            if (!res.success || !res.results.length) {
                                $results.html('<div class="p-3 text-muted small">No results found</div>').addClass('show');
                                return;
                            }

                            let html = '';
                            res.results.forEach(function(item) {
                                html += '<a href="' + item.url + '" class="live-search-item">' +
                                    '<img src="' + item.image + '" alt="">' +
                                    '<div><strong>' + escapeHtml(item.title) + '</strong>' +
                                    '<br><small class="text-muted">' + escapeHtml(item.category) + ' &middot; ' + item.date + '</small></div></a>';
                            });
                            html += '<a href="' + BASE_URL + '/search?q=' + encodeURIComponent(q) + '" class="live-search-item text-center text-danger"><strong>View all results</strong></a>';
                            $results.html(html).addClass('show');
                        })
                        .fail(function() {
                            $results.removeClass('show').empty();
                        });
                }, 300);
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest(inputSelector).length && !$(e.target).closest(resultsSelector).length) {
                    $results.removeClass('show');
                }
            });
        }

        bindSearch('#liveSearchInput', '#liveSearchResults');
        bindSearch('#searchPageInput', '#liveSearchResults');
    }

    // Newsletter AJAX
    function initNewsletter() {
        function handleSubmit(e) {
            e.preventDefault();
            const $form = $(this);
            const $msg = $form.siblings('[id$="NewsletterMessage"], #newsletterMessage').first();
            const $btn = $form.find('button[type="submit"]');

            $btn.prop('disabled', true);
            $.post(AJAX_BASE + '/newsletter.php', $form.serialize())
                .done(function(res) {
                    $msg.html('<span class="' + (res.success ? 'text-success' : 'text-danger') + '">' + escapeHtml(res.message) + '</span>');
                    if (res.success) $form[0].reset();
                })
                .fail(function(xhr) {
                    const res = xhr.responseJSON;
                    $msg.html('<span class="text-danger">' + escapeHtml(res?.message || 'Subscription failed.') + '</span>');
                })
                .always(function() {
                    $btn.prop('disabled', false);
                });
        }

        $('#newsletterForm, #sidebarNewsletterForm').on('submit', handleSubmit);
    }

    // Comment AJAX
    function initComments() {
        $('#commentForm').on('submit', function(e) {
            e.preventDefault();
            const $form = $(this);
            const $msg = $('#commentMessage');
            const $btn = $form.find('button[type="submit"]');

            $btn.prop('disabled', true);
            $.post(AJAX_BASE + '/comment.php', $form.serialize())
                .done(function(res) {
                    $msg.html('<span class="' + (res.success ? 'text-success' : 'text-danger') + '">' + escapeHtml(res.message) + '</span>');
                    if (res.success) {
                        $form.find('textarea[name="comment"]').val('');
                    }
                })
                .fail(function(xhr) {
                    const res = xhr.responseJSON;
                    $msg.html('<span class="text-danger">' + escapeHtml(res?.message || 'Failed to post comment.') + '</span>');
                })
                .always(function() {
                    $btn.prop('disabled', false);
                });
        });
    }

    // Bookmark toggle
    function initBookmark() {
        $('#bookmarkBtn').on('click', function() {
            const $btn = $(this);
            const newsId = $btn.data('news-id');

            $.post(AJAX_BASE + '/bookmark.php', {
                news_id: newsId,
                _csrf_token: typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : ''
            })
                .done(function(res) {
                    if (res.success) {
                        $btn.toggleClass('active', res.bookmarked);
                        $btn.find('i').attr('class', res.bookmarked ? 'bi bi-bookmark-fill' : 'bi bi-bookmark');
                        showToast(res.message, res.success ? 'success' : 'danger');
                    }
                })
                .fail(function(xhr) {
                    const res = xhr.responseJSON;
                    showToast(res?.message || 'Bookmark failed.', 'danger');
                });
        });
    }

    // Print helper (also available via onclick)
    window.printArticle = function() {
        window.print();
    };

    function showToast(message, type) {
        const $toast = $('<div class="alert alert-' + type + ' position-fixed bottom-0 end-0 m-3 shadow" style="z-index:9999">' + escapeHtml(message) + '</div>');
        $('body').append($toast);
        setTimeout(function() { $toast.fadeOut(function() { $(this).remove(); }); }, 3000);
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    $(document).ready(function() {
        initDarkMode();
        initLiveSearch();
        initNewsletter();
        initComments();
        initBookmark();
    });

})(jQuery);
