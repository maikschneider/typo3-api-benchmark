<?php

declare(strict_types=1);

defined('TYPO3') or die();

// nnhelpers defines its cache in its own ext_localconf.php, which overrides config/system/additional.php.
$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['nnhelpers']['backend'] = \TYPO3\CMS\Core\Cache\Backend\FileBackend::class;
