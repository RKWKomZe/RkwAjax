<?php

namespace RKW\RkwAjax\Helper;
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


/**
 * Class AjaxHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwAjax
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @api
 */
class AjaxHelper
{

    /**
     * @var int
     */
    protected $contentUid;


    /**
     * @var \TYPO3\CMS\Form\Mvc\Configuration\ConfigurationManager
     * @inject
     */
    protected $configurationManager;


    /**
     * Gets the contentUid
     *
     * @return int
     */
    public function getContentUid ()
    {
        if (! $this->contentUid) {

            if (
                ($this->configurationManager)
                && ($this->configurationManager->getContentObject())
            ){
                $this->contentUid = intval($this->configurationManager->getContentObject()->data['uid']);
            }
            if (GeneralUtility::_GP('ajaxContentUid')) {
                $this->contentUid = intval(GeneralUtility::_GP('ajaxContentUid'));
            }
        }
        return $this->contentUid;
    }


    /**
     * gets identifier
     *
     * @return string
     */
    public function getKey ()
    {
        return sha1($this->getContentUid());
    }
}