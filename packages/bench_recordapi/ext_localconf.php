<?php

declare(strict_types=1);

defined('TYPO3') or die();

$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['bench_recordapi'] ??= [
    'backend' => \TYPO3\CMS\Core\Cache\Backend\FileBackend::class,
    'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
];

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc']['bench_recordapi']
    = \Typo3ApiBenchmark\Recordapi\CacheInvalidationHook::class . '->clearCachePostProc';
