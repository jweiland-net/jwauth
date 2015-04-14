<?php
namespace JWeiland\Jwauth\Service;

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
use TYPO3\CMS\Core\Service\AbstractService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @package jwauth
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class IpAuthService extends AbstractService {

	protected $subType = '';
	protected $loginData = '';
	protected $authInfo = '';

	/**
	 * @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
	 */
	protected $feUserAuth = NULL;

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
	 * returns TRUE, if Service is available
	 *
	 * @return bool
	 */
	public function init() {
		// @ToDo: Add some checks
		return TRUE;
	}

	/**
	 * @param string $subType
	 * @param array $loginData
	 * @param array $authInfo
	 * @param \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $feUserAuth
	 * @return void
	 */
	public function initAuth($subType, $loginData, $authInfo, \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $feUserAuth) {
		$this->subType = $subType;
		$this->loginData = $loginData;
		$this->authInfo = $authInfo;
		$this->feUserAuth = $feUserAuth;
	}

	/**
	 * get fe_user with given IP-Address
	 *
	 * @return array|FALSE
	 */
	public function getUser() {
		// search on all pids for user records
		$this->authInfo['db_user']['check_pid_clause'] = '';
		// get remote address
		$remoteAddress = htmlspecialchars(strip_tags($this->authInfo['REMOTE_ADDR']));
		// @ToDo: Check if we have a page-object for enableFields somewhere
		$user = $this->feUserAuth->fetchUserRecord(
			$this->authInfo['db_user'],
			'', // we have no username
			' AND ip_address=' . $this->databaseConnection->fullQuoteStr($remoteAddress, 'fe_users')
		);
		return $user;
	}

	/**
	 * Authenticate user as valid, if IP-Address matches RemoteHost
	 *
	 * @param array $tempuser
	 * @return int
	 */
	public function authUser(array $tempuser) {
		// this is an additional check against the IP-Address
		// just to be sure
		if (GeneralUtility::cmpIP($this->authInfo['REMOTE_ADDR'], $tempuser['ip_address'])) {
			// 200 and above indicates a directly authenticated user with no further checks
			return 200;
		} else {
			// 0 indicates NOT logged in. 100 indicates NOT logged in, but further services can still try to authenticate the user
			return 100;
		}
	}

}