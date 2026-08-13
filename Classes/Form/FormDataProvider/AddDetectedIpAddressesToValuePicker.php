<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/jwauth.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Jwauth\Form\FormDataProvider;

use JWeiland\Jwauth\Service\RemoteAddressDetector;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Localization\LanguageService;

/**
 * Adds the visitor's currently detected remote address to the valuePicker of
 * the ip_address field, so editors can pick it up directly instead of
 * looking it up separately.
 */
final class AddDetectedIpAddressesToValuePicker implements FormDataProviderInterface
{
    private const TABLE_NAME = 'tx_jwauth_domain_model_ipaddress';
    private const FIELD_NAME = 'ip_address';

    public function __construct(
        private readonly RemoteAddressDetector $remoteAddressDetector,
    ) {}

    public function addData(array $result): array
    {
        if ($result['tableName'] !== self::TABLE_NAME
            || !isset($result['processedTca']['columns'][self::FIELD_NAME]['config'])
        ) {
            return $result;
        }

        $detectedAddress = $this->remoteAddressDetector->detect();
        if ($detectedAddress === null) {
            return $result;
        }

        $existingItems = $result['processedTca']['columns'][self::FIELD_NAME]['config']['valuePicker']['items'] ?? [];
        $result['processedTca']['columns'][self::FIELD_NAME]['config']['valuePicker']['items'] = array_merge(
            $existingItems,
            [$this->buildValuePickerItem($detectedAddress)],
        );

        return $result;
    }

    /**
     * @param array{version: string, address: string} $detectedAddress
     * @return array{label: string, value: string}
     */
    private function buildValuePickerItem(array $detectedAddress): array
    {
        $labelKey = $detectedAddress['version'] === 'IPv6' ? 'valuePicker.detectedIpv6Address' : 'valuePicker.detectedIpv4Address';
        $label = sprintf(
            $this->getLanguageService()->sL('LLL:EXT:jwauth/Resources/Private/Language/locallang_db.xlf:' . $labelKey),
            $detectedAddress['address'],
        );

        return [
            'label' => $label,
            'value' => $detectedAddress['address'],
        ];
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
