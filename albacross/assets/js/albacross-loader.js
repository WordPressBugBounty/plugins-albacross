(function (window, document) {
    'use strict';

    var current = document.currentScript;

    if (!current || window._nQ_scriptLoaded) {
        return;
    }

    var params = new URL(current.src, document.baseURI).searchParams;
    var clientId = params.get('client_id');

    if (!clientId) {
        return;
    }

    window._nQc = clientId;
    window._nQs = 'WordPress-Plugin';
    window._nQsv = params.get('plugin_version') || '';

    var tracker = document.createElement('script');

    tracker.async = true;
    tracker.src = 'https://serve.albacross.com/track.js';

    if (current.nonce) {
        tracker.nonce = current.nonce;
    }

    tracker.setAttribute('data-cfasync', 'false');
    tracker.setAttribute('data-no-optimize', '1');
    tracker.setAttribute('data-no-defer', '1');
    tracker.setAttribute('data-noptimize', '1');
    tracker.setAttribute('data-nowprocket', '1');
    tracker.setAttribute('data-jetpack-boost', 'ignore');
    tracker.setAttribute('data-pagespeed-no-defer', '1');

    (document.head || document.documentElement).appendChild(tracker);
}(window, document));