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

use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;


/**
 * Class AjaxHelperAbstract
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwAjax
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @api
 */
abstract class AjaxHelperAbstract
{

    const PAGE_TYPE = 250;


    /**
     * Unique key
     *
     * @var string
     */
    protected $key = '';


    /**
     * Uid of content element using Ajax
     *
     * @var int
     */
    protected $contentUid = 0;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;


    /**
     * Gets the key
     *
     * @return string
     */
    public function getKey (): string
    {
        return $this->key;
    }


    /**
     * Sets the key
     *
     * @param string $key
     */
    protected function setKey (string $key): void
    {
        $this->key = $key;
    }


    /**
     * Gets the contentUid
     *
     * @return int
     */
    public function getContentUid (): int
    {
        return $this->contentUid;
    }


    /**
     * Sets the contentUid
     *
     * @param int $contentUid
     */
    public function setContentUid (int $contentUid): void
    {
        $this->contentUid = $contentUid;
    }


    /**
     * Checks if was an ajaxCall
     *
     * @return bool
     */
    public function getIsAjaxCall (): bool
    {
        if (
            (GeneralUtility::_GP('rkw_ajax'))
            && (
                (GeneralUtility::_GP('type') == self::PAGE_TYPE)
                || (GeneralUtility::_GP('typeNum') == self::PAGE_TYPE)
            )
        ){
            return true;
        }

        return false;
    }



    /**
     * Checks if was an form post
     *
     * @return bool
     */
    public function getIsPostCall (): bool
    {
        if ($_POST){
            return true;
        }

        return false;
    }


    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger(): Logger
    {

        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }

}
