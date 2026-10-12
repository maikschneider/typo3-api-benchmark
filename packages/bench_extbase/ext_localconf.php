<?php

declare(strict_types=1);

defined('TYPO3') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'BenchExtbase',
    'Api',
    [
        \Typo3ApiBenchmark\Extbase\Controller\ArticleController::class => 'list, show',
        \Typo3ApiBenchmark\Extbase\Controller\DepthController::class => 'list',
    ],
);
