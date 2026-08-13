<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/jwauth.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Jwauth\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Detects the visitor's remote address as resolved by TYPO3. Used by the
 * remoteAddress field wizard and by AddDetectedIpAddressesToValuePicker, so
 * both places agree on the same IPv4/IPv6 detection logic.
 */
class RemoteAddressDetector
{
    private const IPV4 = 'IPv4';
    private const IPV6 = 'IPv6';

    /**
     * @return array{version: string, address: string}|null
     */
    public function detect(): ?array
    {
        $remoteAddress = GeneralUtility::getIndpEnv('REMOTE_ADDR');
        if (!GeneralUtility::validIP($remoteAddress)) {
            return null;
        }

        return [
            'version' => str_contains($remoteAddress, ':') ? self::IPV6 : self::IPV4,
            'address' => $remoteAddress,
        ];
    }
}
