<?php
namespace JWeiland\Jwauth;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Stefan Froemken <projects@jweiland.net>, jweiland.net
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @package jwauth
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FeUser {

	/**
	 * @var \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected $databaseConnection = NULL;

	/**
	 * constructor of this class
	 */
	public function __construct() {
		$this->databaseConnection = $GLOBALS['TYPO3_DB'];
	}

	/**
	 * clear fe_user session data
	 *
	 * @param $parameters
	 * @param TypoScriptFrontendController $tsfe
	 */
	public function clearFeUserSession(array $parameters, TypoScriptFrontendController $tsfe) {
		if (is_array($tsfe->fe_user->user) && !empty($tsfe->fe_user->user) && !empty($tsfe->fe_user->user['ip_address']) && $tsfe->fe_user->user['ip_address'] === $_SERVER['REMOTE_ADDR']) {
			$tsfe->fe_user->removeSessionData();
			$this->databaseConnection->exec_DELETEquery('fe_sessions', 'ses_id=' . $this->databaseConnection->fullQuoteStr($tsfe->fe_user->id, 'fe_sessions'));
		}
	}
}