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

        $plaintextKey = $this->getContentUid();
        if (
            ($this->frontendController)
            && ($this->frontendController->getRequest())
        ) {
            $plaintextKey = $this->getContentUid() . '_' .
                $this->frontendController->getRequest()->getControllerExtensionName() .  '_' .
                $this->frontendController->getRequest()->getPluginName() .  '_' .
                $this->frontendController->getRequest()->getControllerName() . '_' .
                $this->frontendController->getRequest()->getControllerActionName() . '_';
               // serialize($this->getFrontendController()->getSettings());
        }

        $this->key = sha1($plaintextKey);

        $this->getLogger()->log(
            \TYPO3\CMS\Core\Log\LogLevel::DEBUG,
            sprintf(
                'Calculated key "%s", plaintext: "%s".',
                $this->key,
                $plaintextKey
            )
        );

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
