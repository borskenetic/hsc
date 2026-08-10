/**
 * Staff layout: mobile / tablet nav drawer (#routeWrapper.open).
 * Breakpoint matches Bootstrap lg (992px) and d-lg-none toggles.
 */
document.addEventListener('DOMContentLoaded', function () {
    var toggleBtn = document.getElementById('customMenuToggle');
    var closeBtn = document.getElementById('customMenuClose');
    var routeWrapper = document.getElementById('routeWrapper');
    if (!toggleBtn || !closeBtn || !routeWrapper) {
        return;
    }

    var mq = window.matchMedia('(min-width: 992px)');

    function closeDrawerIfDesktop() {
        if (mq.matches) {
            routeWrapper.classList.remove('open');
        }
    }

    toggleBtn.addEventListener('click', function () {
        routeWrapper.classList.add('open');
    });

    closeBtn.addEventListener('click', function () {
        routeWrapper.classList.remove('open');
    });

    if (typeof mq.addEventListener === 'function') {
        mq.addEventListener('change', closeDrawerIfDesktop);
    } else {
        mq.addListener(closeDrawerIfDesktop);
    }

    window.addEventListener('resize', closeDrawerIfDesktop);

    routeWrapper.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
            if (!mq.matches) {
                routeWrapper.classList.remove('open');
            }
        });
    });
});
