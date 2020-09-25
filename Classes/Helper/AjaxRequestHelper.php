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
 * Class AjaxRequestHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwAjax
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @api
 */
class AjaxRequestHelper extends AjaxHelperAbstract
{


    /**
     * Array of ids from elements in DOM to change via Ajax
     *
     * @var array
     */
    protected $idList;


    /**
     * Checks if it was an Ajax-call
     *
     * @var bool
     */
    protected $isAjaxCall;



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
     * Sets the idList
     *
     * @param array $idList
     */
    public function setIdList (array $idList)
    {
        $this->idList = $idList;
    }



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
     * Init values based on GET-/POST-Params or on given params and settings
     *
     */
    public function initFromGetPost ()
    {

        $values = [];
        if (GeneralUtility::_GP('rkw_ajax')) {
            $values = GeneralUtility::_GP('rkw_ajax');

            if (
                (GeneralUtility::_GP('type') == self::PAGE_TYPE)
                || (GeneralUtility::_GP('typeNum') == self::PAGE_TYPE)
            ){
                $this->isAjaxCall = true;
            }
        }

        if ($values) {
            if ($contentUid = $values['cid']) {
                $this->setContentUid($contentUid);
            }
            if ($key = $values['key']) {
                $this->setKey($key);
            }
            if ($idList = GeneralUtility::trimExplode(',', $values['idl'])) {
                $this->setIdList($idList);
            }
        }
    }


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->initFromGetPost();
    }
}