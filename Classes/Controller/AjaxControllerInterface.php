<?php

namespace RKW\RkwAjax\Controller;
/*
 * This file is part of the TYPO3 CMS project.
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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\FlexFormService;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use RKW\RkwAjax\Domain\Repository\ContentRepository;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Class AjaxAbstractController
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwAjax
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @api
 */
interface AjaxControllerInterface
{

    /**
     * Returns the settings of the controller
     *
     * @return array
     */
    public function getSettings();


    /**
     * Returns the configurationManager of the controller
     *
     * @return \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     */
    public function getConfigurationManager();


    /**
     * Returns the requestObject of the controller
     *
     * @return \TYPO3\CMS\Extbase\Mvc\Request
     */
    public function getRequest();
}