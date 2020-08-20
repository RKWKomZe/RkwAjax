<?php

namespace RKW\RkwAjax\Encoder;
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
use RKW\RkwAjax\View\AjaxStandaloneView;

/**
 * Class JsonExtendedEncoder
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwAjax
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @deprecated
 */
class JsonTemplateEncoder extends AbstractJsonEncoder
{

    /**
     * @var \TYPO3\CMS\Extbase\Mvc\Web\Request
     */
    protected $request;

    /**
     * @var array
     */
    protected $_viewHelperLayoutRootPaths = array();

    /**
     * @var array
     */
    protected $_viewHelperTemplateRootPaths = array();

    /**
     * @var string
     */
    protected $_viewHelperPartialRootPaths = array();


    /**
     * Sets request for forms
     *
     * This is relevant for validation with forms
     *
     * @param \TYPO3\CMS\Extbase\Mvc\Request $request
     * @param string $overridePlugin
     * @param string $overrideController
     * @param string $overrideAction
     * @return $this
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException if the extension name is not valid
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidControllerNameException if the controller name is not valid
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidActionNameException if the action name is not valid
     * @see \TYPO3\CMS\Fluid\ViewHelpers\FormViewHelper::renderHiddenReferrerFields()
     */
    public function setRequest(\TYPO3\CMS\Extbase\Mvc\Request $request, $overridePlugin = null, $overrideController = null, $overrideAction = null)
    {

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \TYPO3\CMS\Extbase\Mvc\Web\Request $webRequest */
        $webRequest = $objectManager->get(Request::class);
        $webRequest->setControllerVendorName($request->getControllerVendorName());
        $webRequest->setControllerExtensionName($request->getControllerExtensionName());
        $webRequest->setPluginName($request->getPluginName());
        $webRequest->setControllerName($request->getControllerName());
        $webRequest->setControllerActionName($request->getControllerActionName());

        if ($overridePlugin) {
            $webRequest->setPluginName($overridePlugin);
        }

        if ($overrideController) {
            $webRequest->setControllerName($overrideController);
        }

        // default behavior: thisIsAnAjaxAction --> thisIsAnAction
        if (! $overrideAction) {
            $overrideAction = str_replace('Ajax', '', $webRequest->getControllerActionName());
        }
        $webRequest->setControllerActionName($overrideAction);
        $this->request = $webRequest;
        return $this;
    }


    /**
     * Gets request
     *
     * @return \TYPO3\CMS\Extbase\Mvc\Web\Request $request
     */
    public function getRequest()
    {
        return $this->request;
    }


    /**
     * Sets HTML
     *
     * @param string $id
     * @param string|array $html
     * @param string $type
     * @param string $template
     * @param string $htmlString
     * @return $this
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function setHtml($id, $html, $type = 'replace', $template = null, $htmlString = '')
    {

        if (!$this->html[$id]) {
            $this->html[$id] = array();
        }

        $finalType = 'replace';
        if (in_array(strtolower($type), array('append', 'prepend', 'replace'))) {
            $finalType = strtolower($type);
        }

        // set html
        if ($template) {
            $this->html[$id][$finalType] = $this->getHtmlRaw($html, $template);
        } else {
            $this->html[$id][$finalType] = $htmlString;
        }

        return $this;
    }


    /**
     * get HTML
     *
     * @param string|array $html
     * @param string $template
     * @return NULL|string
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function getHtmlRaw($html, $template = null)
    {

        // set template if possible
        if (
            ($template)
            && (is_array($html))
        ) {

            // load ViewHelper
            /** @var \RKW\RkwAjax\View\AjaxStandaloneView $viewHelper */
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);
            $viewHelper = $objectManager->get(AjaxStandaloneView::class);

            // set request; this is relevant for validation with forms - needs to be done first!
            if ($this->getRequest()) {
                $viewHelper->setRequest($this->getRequest());
            }

            // now set relevant paths
            $viewHelper->setLayoutRootPaths($this->_viewHelperLayoutRootPaths);
            $viewHelper->setTemplateRootPaths($this->_viewHelperTemplateRootPaths);
            $viewHelper->setPartialRootPaths($this->_viewHelperPartialRootPaths);
            $viewHelper->setTemplate($template);

            $viewHelper->assignMultiple($html);
            $html = $viewHelper->render();
        }

        return str_replace(array("\n", "\r", "\t"), '', $html);
    }


    /**
     * Constructor
     */
    public function __construct()
    {

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

        /** @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager */
        $configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManagerInterface');

        // get paths
        $extbaseFrameworkConfiguration = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        if (is_array($extbaseFrameworkConfiguration['view']['layoutRootPaths'])) {
            $this->_viewHelperLayoutRootPaths = $extbaseFrameworkConfiguration['view']['layoutRootPaths'];
        }
        if (is_array( $extbaseFrameworkConfiguration['view']['templateRootPaths'])) {
            $this->_viewHelperTemplateRootPaths = $extbaseFrameworkConfiguration['view']['templateRootPaths'];
        }
        if (is_array($extbaseFrameworkConfiguration['view']['partialRootPaths'])) {
            $this->_viewHelperPartialRootPaths = $extbaseFrameworkConfiguration['view']['partialRootPaths'];
        }

        // fallback: old version
        if (
            (count($this->_viewHelperLayoutRootPaths) < 1)
            && (isset($extbaseFrameworkConfiguration['view']['layoutRootPath']))
        ) {
            $this->_viewHelperLayoutRootPaths = array($extbaseFrameworkConfiguration['view']['layoutRootPath']);
        }
        if (
            (count($this->_viewHelperTemplateRootPaths) < 1)
            && (isset($extbaseFrameworkConfiguration['view']['templateRootPath']))
        ) {
            $this->_viewHelperTemplateRootPaths = array($extbaseFrameworkConfiguration['view']['templateRootPath']);
        }
        if (
            (count($this->_viewHelperPartialRootPaths) < 1)
            && (isset($extbaseFrameworkConfiguration['view']['partialRootPath']))
        ) {
            $this->_viewHelperPartialRootPaths = array($extbaseFrameworkConfiguration['view']['partialRootPath']);
        }
    }


}