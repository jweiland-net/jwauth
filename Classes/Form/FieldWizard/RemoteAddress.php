<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/jwauth.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Jwauth\Form\FieldWizard;

use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Shows the REMOTE_ADDR TYPO3 currently resolves for the visitor below the
 * IP address field. Helpful, as e.g. reverse proxies or local Docker setups
 * let $_SERVER['REMOTE_ADDR'] differ heavily from the address a user should
 * configure in ip_addresses/ip_address.
 */
class RemoteAddress extends AbstractNode
{
    public function render(): array
    {
        $result = $this->initializeResultArray();

        $label = $this->getLanguageService()->sL(
            'LLL:EXT:jwauth/Resources/Private/Language/locallang_db.xlf:fieldWizard.remoteAddress'
        );
        $remoteAddress = htmlspecialchars(strip_tags(GeneralUtility::getIndpEnv('REMOTE_ADDR')));

        $result['html'] = '<div class="form-text">' . htmlspecialchars($label) . ' <code>' . $remoteAddress . '</code></div>';

        return $result;
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
