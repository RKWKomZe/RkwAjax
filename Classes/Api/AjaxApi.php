<?php

namespace RKW\RkwAjax\Api;
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
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use RKW\RkwAjax\View\StandaloneView;

/**
 * Class AjaxApi
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwAjax
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @api
 */
class AjaxApi
{

    /**
     * @var array settings
     */
    protected $settings = [];

     /**
     * @var \TYPO3\CMS\Extbase\Mvc\Request
     */
    protected $request;

    /**
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    protected $contentObject;


    /**
     * @var \RKW\RkwAjax\Domain\Repository\ContentRepository
     * @inject
     */
    protected $contentRepository;


    /**
     * Init Ajax API
     *
     * @param $settings
     * @param $request
     */
    public function init(array &$settings, \TYPO3\CMS\Extbase\Mvc\Request $request, \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $contentObject)
    {

        var_dump($request->getControllerExtensionName());

        $this->request = $request;
        $this->contentObject = $contentObject;

        // merge settings with settings from flexForm
        $flexFormData = $this->contentRepository->getFlexformDataByUid(
            $contentObject->data['uid'],
            $request->getPluginName(),
            $request->getControllerExtensionName()
        );

        /**
         * Merges settings from typoscript with settings from flexform
         *
         * @param $contentUid

        protected function loadMergedSettings($contentUid)
    {
        $flexFormData = $this->ttContentRepository->findFlexformDataByUid($contentUid, $this->request->getPluginName());
        foreach ($flexFormData as $settingKey => $settingValue) {
            $this->settings[str_replace('settings.', '', $settingKey)] = $settingValue;
        }
    }
         */

    }



}