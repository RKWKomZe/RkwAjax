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


/**
 * Class AjaxHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwAjax
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @api
 */
class AjaxHelper extends AjaxHelperAbstract
{


    /**
     * Frontend controller
     *
     * @var \RKW\RkwAjax\Controller\AjaxControllerInterface
     */
    protected $frontendController;


    /**
     * Gets the key
     *
     * @return string
     */
    public function getKey ()
    {
        if (! $this->key) {
            $this->calculateKey();
        }
        return $this->key;
    }



    /**
     * Calculates the key
     */
    protected function calculateKey ()
    {
        if (
            ($this->frontendController)
            && ($this->frontendController->getRequest())
        ) {
            $this->key = sha1(
                $this->getContentUid() . '_' .
                md5(
                    $this->frontendController->getRequest()->getPluginName() .
                    $this->frontendController->getRequest()->getControllerName() .
                    $this->frontendController->getRequest()->getControllerActionName() .
                    serialize($this->getFrontendController()->getSettings())
                )
            );
        }

        $this->key = sha1($this->getContentUid());
    }



    /**
     * Gets the frontend controller
     *
     * @return \RKW\RkwAjax\Controller\AjaxControllerInterface
     */
    public function getFrontendController ()
    {
        return $this->frontendController;
    }


    /**
     * Sets the frontend controller
     *
     * @param \RKW\RkwAjax\Controller\AjaxControllerInterface
     */
    public function setFrontendController (\RKW\RkwAjax\Controller\AjaxControllerInterface $frontendController)
    {

        $this->frontendController = $frontendController;
        if (
            ($this->frontendController->getConfigurationManager())
            && ($contentObject = $this->frontendController->getConfigurationManager()->getContentObject())
            && ($contentUid = $contentObject->data['uid'])
        ) {
            $this->setContentUid($contentUid);
        }

    }

}