(function () {
    function ready() {
        document.body.classList.remove('sa-page-leaving');
        requestAnimationFrame(function () {
            document.body.classList.add('sa-page-ready');
            document.body.classList.remove('sa-page-loading');
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ready, { once: true });
    } else {
        ready();
    }

    window.addEventListener('pageshow', ready);
    setTimeout(ready, 1200);

    document.addEventListener('click', function (event) {
        var link = event.target.closest && event.target.closest('a[href]');
        if (!link) return;
        if (link.target && link.target !== '_self') return;
        if (link.hasAttribute('download')) return;
        if (link.dataset.noTransition === 'true') return;

        var href = link.getAttribute('href') || '';
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) return;

        try {
            var url = new URL(link.href, window.location.href);
            if (url.origin !== window.location.origin) return;
            if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) return;
            document.body.classList.remove('sa-page-ready');
            document.body.classList.add('sa-page-leaving');
        } catch (e) {}
    }, true);
})();
