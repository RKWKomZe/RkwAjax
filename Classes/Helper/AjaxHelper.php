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

    const PAGE_TYPE = 250;


    /**
     * Checks if it was an Ajax-call
     *
     * @var bool
     */
    protected $isAjaxCall;


    /**
     * Unique key
     *
     * @var string
     */
    protected $key;


    /**
     * Uid of content element using Ajax
     *
     * @var int
     */
    protected $contentUid;


    /**
     * Array of ids from elements in DOM to change via Ajax
     *
     * @var array
     */
    protected $idList;


    /**
     * @var \TYPO3\CMS\Form\Mvc\Configuration\ConfigurationManager
     * @inject
     */
    protected $configurationManager;



    /**
     * Checks if was an ajaxCall
     *
     * @return bool
     */
    public function isAjaxCall ()
    {
        return boolval($this->isAjaxCall);
    }


    /**
     * Gets the key
     *
     * @return string
     */
    public function getKey ()
    {
        if (! $this->key) {
            $this->key = sha1($this->getContentUid());
        }
        return $this->key;
    }


    /**
     * Gets the contentUid
     *
     * @return int
     */
    public function getContentUid ()
    {
        return intval($this->contentUid);
    }


    /**
     * Gets the idList
     *
     * @return array
     */
    public function getIdList ()
    {
        return $this->idList;
    }



    /**
     * Init values based on GET-Params or on given param
     *
     * @param array $settings
     */
    public function init (array $settings = [])
    {

        if (GeneralUtility::_GP('rkw_ajax')) {
            $settings = GeneralUtility::_GP('rkw_ajax');

            if (
                (GeneralUtility::_GP('type') == self::PAGE_TYPE)
                || (GeneralUtility::_GP('typeNum') == self::PAGE_TYPE)
            ){
                $this->isAjaxCall = true;
            }
        }

        if ($settings) {
            if ($contentUid = $settings['cid']) {
                $this->contentUid = $contentUid;
            }
            if ($key = $settings['key']) {
                $this->key = $key;
            }
            if ($idList = GeneralUtility::trimExplode(',', $settings['idl'])) {
                $this->idList = $idList;
            }
        }
    }


    /**
     * Constructor
     *
     * @param array $settings
     */
    public function __construct(array $settings = [])
    {
        $this->init($settings);
    }
}