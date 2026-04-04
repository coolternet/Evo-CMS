/**
 * Evo-CMS — script de l’installateur (dépend de vendor.js : jQuery, Bootstrap).
 */
(function () {
    try {
        var k = 'evoInstallAmbientT0';
        var raw = sessionStorage.getItem(k);
        if (raw === null || raw === '') {
            raw = String(Date.now());
            sessionStorage.setItem(k, raw);
        }
        var t0 = parseInt(raw, 10);
        if (isNaN(t0)) {
            return;
        }
        var elapsed = (Date.now() - t0) / 1000;
        if (elapsed < 0) {
            return;
        }
        var d11 = -(elapsed % 11);
        var d15 = -(elapsed % 15);
        var st = document.createElement('style');
        st.id = 'evo-install-ambient-sync';
        st.textContent =
            'body.evo-install .evo-install__ambient-glow--top,' +
            'body.evo-install .evo-install__ambient-glow--tl,' +
            'body.evo-install .evo-install__ambient-glow--br' +
            '{animation-delay:' + d11 + 's,' + d15 + 's;}';
        document.head.appendChild(st);
    } catch (e) {
        /* sessionStorage indisponible ou quota */
    }
})();

(function ($) {
    'use strict';

    function initDatabaseStep() {
        if (!$('#type').length || !$('.db-fields-container').length) {
            return;
        }

        function updateFormFields() {
            var selectedType = $('#type').val();
            var container = $('.db-fields-container');
            var alert = $('.db-alert');

            $('#db_type_backup').val(selectedType);

            $('.db-field').hide();
            $('.' + selectedType).show();

            if (selectedType == 'mysql') {
                container.addClass('mysql-layout');
                alert.hide();
            } else {
                container.removeClass('mysql-layout');
                alert.show();
            }

            if (selectedType == 'sqlite') {
                if (!$('#dbname').val()) {
                    var randomId = Math.random().toString(36).substr(2, 6);
                    $('#dbname').val('db-' + randomId + '.sqlite');
                }
                $('#prefixe').val('');
            } else {
                if (selectedType == 'mysql') {
                    $('#dbname').val('');
                    $('#prefixe').val('evo_');
                }
            }
        }

        $('#form-content').on('submit', function (e) {
            var selectedType = $('#type').val();

            if (selectedType == 'sqlite' && !$('#dbname').val()) {
                var randomId = Math.random().toString(36).substr(2, 6);
                $('#dbname').val('db-' + randomId + '.sqlite');
            }

            if (selectedType == 'mysql') {
                var host = $('#host').val().trim();
                var user = $('#username').val().trim();
                var dbname = $('#dbname').val().trim();

                if (!host || !user || !dbname) {
                    e.preventDefault();
                    alert(document.body.getAttribute('data-evo-install-js-alert-mysql') || '');
                    return false;
                }
            } else if (selectedType == 'sqlite') {
                var dbname = $('#dbname').val().trim();
                console.log('SQLite dbname value:', dbname);
                if (!dbname) {
                    e.preventDefault();
                    alert(document.body.getAttribute('data-evo-install-js-alert-sqlite') || '');
                    return false;
                }
            }

            $('.db-field').hide();
            $('.' + selectedType).show();

            if (selectedType == 'mysql') {
                $('.mysql').show();
            } else {
                $('.sqlite').show();
            }

            $('#db_type_backup').val(selectedType);

            console.log('Form submitted with type:', selectedType);
            console.log('Visible fields:', $('.db-field:visible').length);
        });

        $('#type').bind('change blur keyup', updateFormFields);
        updateFormFields();
    }

    function initThemeToggle() {
        var btn = document.getElementById('evo-install-theme-toggle');
        if (!btn) {
            return;
        }
        var textEl = btn.querySelector('.evo-install__theme-toggle-text');
        var labelLight = btn.getAttribute('data-evo-install-label-light') || '';
        var labelDark = btn.getAttribute('data-evo-install-label-dark') || '';
        function setLightMode(on) {
            document.body.classList.toggle('evo-install--light', on);
            btn.setAttribute('aria-pressed', on ? 'true' : 'false');
            if (textEl) {
                textEl.textContent = on ? labelDark : labelLight;
            }
            try {
                localStorage.setItem('evoInstallColorMode', on ? 'light' : 'dark');
            } catch (e) {
                /* quota / navigation privée */
            }
        }
        setLightMode(document.body.classList.contains('evo-install--light'));
        btn.addEventListener('click', function () {
            setLightMode(!document.body.classList.contains('evo-install--light'));
        });
    }

    $(function () {
        initDatabaseStep();
        initThemeToggle();

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        var $formContent = $('#form-content');
        if ($formContent.length) {
            $formContent.on('submit', function (e) {
                var form = this;
                if (form.getAttribute('data-evo-install-navigating') === '1') {
                    form.removeAttribute('data-evo-install-navigating');
                    return;
                }
                if (e.isDefaultPrevented()) {
                    return;
                }
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    return;
                }
                var oe = e.originalEvent;
                var sub = oe && oe.submitter ? oe.submitter : null;
                if (!sub && document.activeElement && document.activeElement.form === form) {
                    var ae = document.activeElement;
                    if (ae.matches && ae.matches('button[type="submit"]') && ae.getAttribute('name') === 'step') {
                        sub = ae;
                    }
                }
                if (!sub || sub.getAttribute('name') !== 'step') {
                    return;
                }
                e.preventDefault();
                form.setAttribute('data-evo-install-navigating', '1');
                form.classList.add('evo-install-form--exit');
                var navDone = false;
                var fallbackMs = 450;
                var t = window.setTimeout(function () {
                    finishNav();
                }, fallbackMs);
                function finishNav() {
                    if (navDone) {
                        return;
                    }
                    navDone = true;
                    window.clearTimeout(t);
                    form.removeEventListener('animationend', onAnimEnd);
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit(sub);
                    } else {
                        sub.click();
                    }
                }
                function onAnimEnd(ev) {
                    if (ev.target !== form) {
                        return;
                    }
                    if ((ev.animationName || '').indexOf('evoFadeOut') === -1) {
                        return;
                    }
                    finishNav();
                }
                form.addEventListener('animationend', onAnimEnd);
            });
        }

        $(document).on('click', 'a.evo-install-lang-option', function (e) {
            var href = this.getAttribute('href');
            if (!href) {
                return;
            }
            if (document.body.getAttribute('data-evo-install-step') !== '0') {
                return;
            }
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }
            e.preventDefault();
            if ($('body').data('evoLangSwitching')) {
                return;
            }
            $('body').data('evoLangSwitching', true);
            var done = false;
            var navigate = function () {
                if (done) {
                    return;
                }
                done = true;
                window.location.href = href;
            };
            var fallbackMs = 400;
            var t = window.setTimeout(navigate, fallbackMs);
            document.body.classList.add('evo-install-lang-text--exit');
            var onAnimEnd = function (ev) {
                var animName = ev.animationName || '';
                if (animName.indexOf('evoFadeOut') === -1) {
                    return;
                }
                window.clearTimeout(t);
                document.body.removeEventListener('animationend', onAnimEnd);
                navigate();
            };
            document.body.addEventListener('animationend', onAnimEnd);
        });
    });
})(jQuery);
