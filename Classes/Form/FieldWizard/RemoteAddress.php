<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/jwauth.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Jwauth\Form\FieldWizard;

use JWeiland\Jwauth\Service\RemoteAddressDetector;
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Core\Localization\LanguageService;

/**
 * Shows the remote address TYPO3 currently resolves for the visitor below
 * the IP address field. Helpful, as e.g. reverse proxies or local Docker
 * setups let $_SERVER['REMOTE_ADDR'] differ heavily from the address a user
 * should configure in ip_addresses/ip_address.
 */
class RemoteAddress extends AbstractNode
{
    public function __construct(
        private readonly RemoteAddressDetector $remoteAddressDetector,
    ) {}

    public function render(): array
    {
        $result = $this->initializeResultArray();

        $detectedAddress = $this->remoteAddressDetector->detect();
        if ($detectedAddress === null) {
            return $result;
        }

        $label = sprintf(
            $this->getLanguageService()->sL('LLL:EXT:jwauth/Resources/Private/Language/locallang_db.xlf:fieldWizard.remoteAddress'),
            $detectedAddress['version'],
        );

        $result['html'] = '<div class="form-text">' . htmlspecialchars($label) . ' <code>' . htmlspecialchars($detectedAddress['address']) . '</code></div>';

        return $result;
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
