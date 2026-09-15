// Product Drive CMS — client interactions

(function () {
    'use strict';

    // ---- Theme toggle ----
    var toggle = document.getElementById('themeToggle');
    function setIcon() {
        if (!toggle) return;
        var dark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        toggle.innerHTML = dark ? '<i class="bi bi-sun"></i>' : '<i class="bi bi-moon-stars"></i>';
    }
    setIcon();
    if (toggle) {
        toggle.addEventListener('click', function () {
            var next = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', next);
            localStorage.setItem('pdcms-theme', next);
            setIcon();
        });
    }

    // ---- Auto-dismiss flash messages ----
    document.querySelectorAll('.flash-alert').forEach(function (el) {
        setTimeout(function () {
            if (!el.parentNode) return;
            el.style.transition = 'opacity .4s ease, transform .4s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateX(20px)';
            setTimeout(function () { el.remove(); }, 400);
        }, 5000);
    });

    // ---- Password visibility toggles ----
    document.querySelectorAll('[data-pw-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.getAttribute('data-pw-toggle'));
            if (!input) return;
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.innerHTML = show ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
        });
    });

    // ---- Image upload preview + dropzone ----
    document.querySelectorAll('[data-image-input]').forEach(function (input) {
        var previewId = input.getAttribute('data-preview');
        var preview = previewId ? document.getElementById(previewId) : null;
        var zone = input.closest('.dropzone');
        if (zone) {
            zone.addEventListener('click', function (e) { if (e.target !== input) input.click(); });
        }
        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            if (file && preview) {
                var reader = new FileReader();
                reader.onload = function (ev) { preview.src = ev.target.result; preview.style.display = 'block'; };
                reader.readAsDataURL(file);
            }
        });
    });

    // ---- Video picker (filename display + dropzone click) ----
    var videoInput = document.getElementById('videoInput');
    if (videoInput) {
        var vzone = videoInput.closest('.dropzone');
        var vname = document.getElementById('videoName');
        if (vzone) vzone.addEventListener('click', function (e) { if (e.target !== videoInput) videoInput.click(); });
        videoInput.addEventListener('change', function () {
            var f = videoInput.files && videoInput.files[0];
            if (f && vname) vname.textContent = f.name;
        });
    }

    // ---- Currency live preview + presets (Settings) ----
    var curForm = document.getElementById('currencyForm');
    if (curForm) {
        var curPreview = document.getElementById('curPreview');
        function fmtPreview() {
            var symbol = (document.getElementById('currency_symbol').value || '');
            var pos = document.getElementById('currency_position').value;
            var dec = Math.max(0, Math.min(4, parseInt(document.getElementById('currency_decimals').value || '2', 10)));
            var ts = document.getElementById('currency_thousand_sep').value;
            if (ts === 'none') ts = '';
            var ds = document.getElementById('currency_decimal_sep').value;
            var space = document.getElementById('currency_space').checked ? '\u00A0' : '';
            var n = (1234567.5).toFixed(dec);
            var parts = n.split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ts);
            var num = parts.length > 1 ? parts[0] + ds + parts[1] : parts[0];
            var out = symbol === '' ? num : (pos === 'after' ? num + space + symbol : symbol + space + num);
            if (curPreview) curPreview.textContent = out;
        }
        curForm.querySelectorAll('input,select').forEach(function (el) {
            el.addEventListener('input', fmtPreview);
            el.addEventListener('change', fmtPreview);
        });
        document.querySelectorAll('[data-cur-preset]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var p = JSON.parse(btn.getAttribute('data-cur-preset'));
                document.getElementById('currency_code').value = p.code;
                document.getElementById('currency_symbol').value = p.symbol;
                document.getElementById('currency_position').value = p.position;
                document.getElementById('currency_decimals').value = p.decimals;
                document.getElementById('currency_thousand_sep').value = p.thousand;
                document.getElementById('currency_decimal_sep').value = p.decimal;
                document.getElementById('currency_space').checked = !!p.space;
                fmtPreview();
            });
        });
    }

    // ---- Confirm-before-submit (delete forms) ----
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!window.confirm(form.getAttribute('data-confirm'))) e.preventDefault();
        });
    });

    // ---- Auto-submit filter selects on the products page ----
    document.querySelectorAll('[data-autosubmit]').forEach(function (el) {
        el.addEventListener('change', function () { el.closest('form').submit(); });
    });
})();
