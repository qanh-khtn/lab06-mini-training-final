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

    /* --- Sidebar Collapse Toggle --- */
    var sidebarToggle = document.getElementById('sidebar-toggle');
    var sidebar = document.getElementById('sidebar');

    if (sidebarToggle && sidebar) {
        // Load saved state from localStorage
        var isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
            document.documentElement.style.setProperty('--sidebar-w', '64px');
        }

        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('collapsed');
            var newState = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebar-collapsed', newState);
            // Update CSS variable for main wrapper margin
            document.documentElement.style.setProperty('--sidebar-w', newState ? '64px' : '240px');
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

        // Mark notification as read on click
        var notifItems = notifMenu.querySelectorAll('.notif-item');
        notifItems.forEach(function (item) {
            item.addEventListener('click', function (e) {
                if (!item.classList.contains('unread')) return;

                // Prevent default link behavior temporarily to mark as read
                e.preventDefault();

                // Mark as read
                item.classList.remove('unread');
                item.style.backgroundColor = 'var(--bg-2)';

                // Update unread badge count
                var unreadCount = notifMenu.querySelectorAll('.notif-item.unread').length;
                var badge = notifBtn.querySelector('.notif-badge');
                if (badge) {
                    badge.textContent = Math.max(0, unreadCount);
                    badge.style.display = unreadCount > 0 ? 'flex' : 'none';
                }

                // Navigate after a short delay
                setTimeout(function () {
                    window.location.href = item.href;
                }, 150);
            });
        });
    }

    /* --- Help Modal Toggle (Help & Instructions) --- */
    /* Default behavior:
       - Closed by default (no auto-open)
       - Opens ONLY when clicking ? button
       - Closes by: clicking X button, clicking outside, or pressing Escape key
    */
    var helpBtn = document.getElementById('help-btn');
    var helpModal = document.getElementById('help-modal');
    var closeBtn = helpModal ? helpModal.querySelector('.btn-close') : null;

    if (helpBtn && helpModal) {
        // Force modal closed on page load
        setTimeout(function () {
            helpModal.close();
        }, 50);

        // Open modal: Click ? button in top-right
        helpBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            helpModal.showModal();
        });

        // Close modal: Click × button
        if (closeBtn) {
            closeBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                helpModal.close();
            });
        }

        // Close modal: Click outside (backdrop)
        helpModal.addEventListener('click', function (e) {
            if (e.target === helpModal) {
                e.preventDefault();
                helpModal.close();
            }
        });

        // Close modal: Press Escape key
        helpModal.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                helpModal.close();
            }
        });
    }

    /* --- Topbar Hide on Scroll --- */
    var topbar = document.querySelector('.topbar');
    if (topbar) {
        var lastScrollTop = 0;

        window.addEventListener('scroll', function () {
            var scrollTop = window.pageYOffset || document.documentElement.scrollTop;

            if (scrollTop > lastScrollTop && scrollTop > 100) {
                // Scrolling DOWN, hide topbar
                topbar.classList.add('hidden');
            } else {
                // Scrolling UP or near top, show topbar
                topbar.classList.remove('hidden');
            }

            lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
        }, false);
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
                            searchResults.innerHTML = '<div style="padding: 16px; text-align: center; color: var(--text-3);">Không tìm thấy kết quả</div>';
                            searchResults.classList.add('show');
                        }
                    })
                    .catch(function (err) {
                        console.error('Search error:', err);
                    });
            }, 300);
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('#quick-search-form')) {
                searchResults.classList.remove('show');
            }
        });
    }
});
