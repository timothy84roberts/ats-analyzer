(function () {
    var KEY = 'ats-theme';

    function current() {
        try {
            return localStorage.getItem(KEY) || 'light';
        } catch (e) {
            return 'light';
        }
    }

    function apply(theme) {
        document.documentElement.dataset.theme = theme === 'dark' ? 'dark' : 'light';
        try {
            localStorage.setItem(KEY, document.documentElement.dataset.theme);
        } catch (e) {}
        document.querySelectorAll('[data-theme-toggle-label]').forEach(function (el) {
            el.textContent = document.documentElement.dataset.theme === 'dark' ? 'Light mode' : 'Dark mode';
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        apply(current());
        document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                apply(document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark');
            });
        });
    });
})();
