<?php

declare(strict_types=1);

defined('TYPO3') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'CampExtbase',
    'Api',
    [\MaikSchneider\CampExtbase\Controller\ArticleController::class => 'list, show'],
);
