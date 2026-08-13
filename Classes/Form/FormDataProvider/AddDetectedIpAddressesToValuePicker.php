<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/jwauth.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Jwauth\Form\FormDataProvider;

use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Adds the visitor's currently detected remote address to the valuePicker of
 * the ip_address field, so editors can pick it up directly instead of looking
 * it up separately.
 */
final class AddDetectedIpAddressesToValuePicker implements FormDataProviderInterface
{
    private const TABLE_NAME = 'tx_jwauth_domain_model_ipaddress';
    private const FIELD_NAME = 'ip_address';

    public function addData(array $result): array
    {
        if ($result['tableName'] !== self::TABLE_NAME
            || !isset($result['processedTca']['columns'][self::FIELD_NAME]['config'])
        ) {
            return $result;
        }

        $detectedAddresses = $this->getDetectedIpAddresses();
        if ($detectedAddresses === []) {
            return $result;
        }

        $existingItems = $result['processedTca']['columns'][self::FIELD_NAME]['config']['valuePicker']['items'] ?? [];
        $result['processedTca']['columns'][self::FIELD_NAME]['config']['valuePicker']['items'] = array_merge(
            $existingItems,
            $detectedAddresses,
        );

        return $result;
    }

    /**
     * A dual-stack webserver may expose an IPv4 client through an IPv4-mapped
     * IPv6 address (e.g. "::ffff:203.0.113.5"). In that case, offer both
     * notations, since an address stored in the other notation would not
     * match it with GeneralUtility::cmpIP().
     *
     * @return list<array{0: string, 1: string}>
     */
    private function getDetectedIpAddresses(): array
    {
        $remoteAddress = GeneralUtility::getIndpEnv('REMOTE_ADDR');
        if (!GeneralUtility::validIP($remoteAddress)) {
            return [];
        }

        if (preg_match('/^::ffff:(\d+\.\d+\.\d+\.\d+)$/i', $remoteAddress, $matches)) {
            return [
                $this->buildValuePickerItem('valuePicker.detectedIpv6Address', $remoteAddress),
                $this->buildValuePickerItem('valuePicker.detectedIpv4Address', $matches[1]),
            ];
        }

        $labelKey = str_contains($remoteAddress, ':') ? 'valuePicker.detectedIpv6Address' : 'valuePicker.detectedIpv4Address';

        return [
            $this->buildValuePickerItem($labelKey, $remoteAddress),
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function buildValuePickerItem(string $labelKey, string $ipAddress): array
    {
        $label = sprintf(
            $this->getLanguageService()->sL('LLL:EXT:jwauth/Resources/Private/Language/locallang_db.xlf:' . $labelKey),
            $ipAddress,
        );

        return [$label, $ipAddress];
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
