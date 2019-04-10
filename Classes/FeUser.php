<?php
namespace JWeiland\Jwauth;

/*
 * This file is part of the jwauth project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * As TYPO3 stores authentication in fe_session, we have to delete saved session data from fe_users_session
 * after TYPO3 was loaded.
 * This is the last hook I found in TYPO3 universe
 */
class FeUser
{
    /**
     * Clear fe_user session data
     *
     * @param $parameters
     * @param TypoScriptFrontendController $tsfe
     */
    public function clearFeUserSession(array $parameters, TypoScriptFrontendController $tsfe)
    {
        if (
            is_array($tsfe->fe_user->user)
            && !empty($tsfe->fe_user->user)
            && !empty($tsfe->fe_user->user['ip_address'])
            && $tsfe->fe_user->user['ip_address'] === $_SERVER['REMOTE_ADDR']
        ) {
            $tsfe->fe_user->removeSessionData();

            $connection = $this->getConnectionPool()->getConnectionForTable('fe_sessions');
            $connection->delete(
                'fe_sessions',
                [
                    'ses_id' => $tsfe->fe_user->id
                ]
            );
        }
    }

    /**
     * Get TYPO3s Connection Pool
     *
     * @return ConnectionPool
     */
    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
