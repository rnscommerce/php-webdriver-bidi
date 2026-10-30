<?php

declare(strict_types=1);

return [
    'commands' => [
        'session.subscribe',
        'session.unsubscribe',
        'script.callFunction',
        'script.addPreloadScript',
        'script.removePreloadScript',
        'browsingContext.getTree',
        'browsingContext.create',
        'browsingContext.close',
        'browsingContext.captureScreenshot',
        'browsingContext.startScreencast',
        'browsingContext.stopScreencast',
        'browser.createUserContext',
        'browser.removeUserContext',
        'network.addDataCollector',
        'network.getData',
        'network.removeDataCollector',
    ],
    'events' => [
        'script.message',
        'browsingContext.contextCreated',
        'browsingContext.navigationStarted',
        'browsingContext.domContentLoaded',
        'browsingContext.load',
        'network.beforeRequestSent',
        'network.responseStarted',
        'network.responseCompleted',
        'network.fetchError',
        'log.entryAdded',
    ],
];
