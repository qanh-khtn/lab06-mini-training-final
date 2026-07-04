(function () {
    var saved      = localStorage.getItem('theme');
    var preferDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    document.documentElement.setAttribute('data-theme', saved || (preferDark ? 'dark' : 'light'));
}());

document.addEventListener('DOMContentLoaded', function () {
    /* --- Toast auto-dismiss --- */
    var TOAST_DURATION = 5000;

    document.querySelectorAll('.toast').forEach(function (toast) {
        toast.style.setProperty('--toast-duration', TOAST_DURATION + 'ms');

        function dismiss() {
            toast.classList.add('toast-hiding');
            toast.addEventListener('animationend', function () { toast.remove(); }, { once: true });
        }

        var timer = setTimeout(dismiss, TOAST_DURATION);

        var closeBtn = toast.querySelector('.toast-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                clearTimeout(timer);
                dismiss();
            });
        }
    });

    /* --- Theme toggle (works on all pages) --- */
    var root = document.documentElement;
    var btn  = document.getElementById('theme-toggle');

    if (btn) {
        function updateThemeTitle() {
            btn.title = root.getAttribute('data-theme') === 'dark'
                ? 'Chuyển sang chế độ sáng'
                : 'Chuyển sang chế độ tối';
        }
        updateThemeTitle();

        btn.addEventListener('click', function () {
            var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            document.body.classList.add('theme-transitioning');
            root.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
            updateThemeTitle();
            setTimeout(function () { document.body.classList.remove('theme-transitioning'); }, 400);
        });
    }

    /* --- Mobile Sidebar Toggle (independent of theme toggle) --- */
    var menuToggle = document.getElementById('menu-toggle');
    var backdrop   = document.getElementById('sidebar-backdrop');

    if (menuToggle && backdrop) {
        menuToggle.addEventListener('click', function () {
            document.body.classList.toggle('sidebar-active');
        });

        backdrop.addEventListener('click', function () {
            document.body.classList.remove('sidebar-active');
        });
    }

    /* --- Notification Button Toggle --- */
    var notifBtn = document.getElementById('notif-btn');
    var notifMenu = document.getElementById('notif-menu');

    if (notifBtn && notifMenu) {
        notifBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            notifMenu.classList.toggle('show');
        });

        document.addEventListener('click', function (e) {
            if (!notifBtn.contains(e.target) && !notifMenu.contains(e.target)) {
                notifMenu.classList.remove('show');
            }
        });
    }

    /* --- Help Modal Toggle --- */
    var helpBtn = document.getElementById('help-btn');
    var helpModal = document.getElementById('help-modal');
    var closeBtn = helpModal ? helpModal.querySelector('.btn-close') : null;

    if (helpBtn && helpModal) {
        helpBtn.addEventListener('click', function () {
            helpModal.showModal();
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                helpModal.close();
            });
        }

        helpModal.addEventListener('click', function (e) {
            if (e.target === helpModal) {
                helpModal.close();
            }
        });
    }

    /* --- Quick Search Functionality --- */
    var searchInput = document.getElementById('quick-search-input');
    var searchResults = document.getElementById('search-results');

    if (searchInput && searchResults) {
        var searchTimeout;

        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            var query = this.value.trim();

            if (!query || query.length < 2) {
                searchResults.classList.remove('show');
                return;
            }

            searchTimeout = setTimeout(function () {
                fetch('/api/search?q=' + encodeURIComponent(query))
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (data.results && data.results.length > 0) {
                            searchResults.innerHTML = data.results
                                .map(function (result) {
                                    return '<a href="' + result.url + '" class="search-result-item">' +
                                        '<strong>' + result.name + '</strong>' +
                                        '<p>' + result.email + '</p>' +
                                        '</a>';
                                })
                                .join('');
                            searchResults.classList.add('show');
                        } else {
                            searchResults.innerHTML = '<div style="padding: 16px; text-align: center; color: #999;">Không tìm thấy kết quả</div>';
                            searchResults.classList.add('show');
                        }
                    })
                    .catch(function (err) {
                        console.error('Search error:', err);
                    });
            }, 300);
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.search-bar')) {
                searchResults.classList.remove('show');
            }
        });
    }
});
