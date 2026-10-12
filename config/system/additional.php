<?php

declare(strict_types=1);

$GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['driverMiddlewares']['bench/query-counter'] = [
    'target' => \Typo3ApiBenchmark\Bench\Database\QueryCountingDriverMiddleware::class,
];

if (getenv('IS_DDEV_PROJECT') === 'true') {
    $GLOBALS['TYPO3_CONF_VARS'] = array_replace_recursive($GLOBALS['TYPO3_CONF_VARS'], [
        'DB' => [
            'Connections' => [
                'Default' => [
                    'dbname' => 'db',
                    'driver' => 'mysqli',
                    'host' => 'db',
                    'password' => 'db',
                    'port' => '3306',
                    'user' => 'db',
                ],
            ],
        ],
        'GFX' => [
            'processor' => 'ImageMagick',
            'processor_path' => '/usr/bin/',
        ],
        'MAIL' => [
            'transport' => 'smtp',
            'transport_smtp_server' => 'localhost:1025',
        ],
        'SYS' => [
            'trustedHostsPattern' => '.*.*',
            'devIPmask' => '*',
            'displayErrors' => 1,
        ],
    ]);
}

$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tca_api'] = [
    'backend' => \TYPO3\CMS\Core\Cache\Backend\FileBackend::class,
    'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
];

// Every API response cache on the same backend, so cache hits compare the APIs and not the storage.
foreach (['pages' => ['pages'], 'sg_apicore_responses' => ['all'], 't3api_response' => ['all', 'pages']] as $cacheName => $groups) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheName] = [
        'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
        'backend' => \TYPO3\CMS\Core\Cache\Backend\FileBackend::class,
        'groups' => $groups,
    ];
}

// sg_apicore reads its extension configuration while its own ext_localconf.php loads.
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sg_apicore']['apiPathPrefix'] = '/_sgapi/';
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sg_apicore']['activateDemoApis'] = '0';
