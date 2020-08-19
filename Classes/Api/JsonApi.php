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
 * Class JsonApi
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwAjax
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class JsonApi
{

    /**
     * @const integer Status values
     */
    const STATUS_OK = 1;
    const STATUS_ERROR = 99;

    /**
     * @var integer status
     */
    protected $status = 1;

    /**
     * @var array message
     */
    protected $message = array();

    /**
     * @var array html
     */
    protected $html = array();

    /**
     * @var array data
     */
    protected $data = array();

    /**
     * @var array JavaScript
     */
    protected $javaScript = array();

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
     * Sets status
     *
     * @param integer $value
     * @return $this
     */
    public function setStatus($value)
    {

        if (defined(get_class($this) . '::' . $value)) {
            $this->status = constant(get_class($this) . '::' . $value);
        } else {
            $this->status = self::STATUS_ERROR;
        }

        return $this;
    }


    /**
     * Sets message
     *
     * @param string  $id
     * @param string  $message
     * @param integer $type
     * @return $this
     */
    public function setMessage($id, $message, $type = 1)
    {

        if (!$message) {
            return $this;
        }


        if (!$this->message[$id]) {
            $this->message[$id] = array();
        }

        $finalType = 99;
        if (in_array(intval($type), array(1, 2, 99))) {
            $finalType = intval($type);
        }

        $this->message[$id]['message'] = $message;
        $this->message[$id]['type'] = $finalType;

        return $this;

    }


    /**
     * Sets data
     *
     * @param mixed $data
     * @return $this
     */
    public function setData($data)
    {

        $this->data = $data;
        return $this;
    }

    /**
     * Unsets data
     *
     * @return $this
     */
    public function unsetData()
    {

        $this->data = array();
        return $this;
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
            /** @var \RKW\RkwAjax\View\StandaloneView $viewHelper */
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);
            $viewHelper = $objectManager->get(StandaloneView::class);

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
     * Unset HTML
     *
     * @return $this
     */
    public function unsetHtml()
    {

        $this->html = array();
        return $this;
    }


    /**
     * Sets JavaScript
     *
     * @param boolean $before
     * @param string $javaScript
     * @return $this
     */
    public function setJavaScript($javaScript, $before = false)
    {

        $target = 'after';
        if ($before) {
            $target = 'before';
        }

        if (!is_array($this->javaScript[$target])) {
            $this->javaScript[$target] = array();
        }

        $this->javaScript[$target][] = $javaScript;
        return $this;
    }

    /**
     * Unsets JavaScript
     *
     * @return $this
     */
    public function unsetJavaScript()
    {

        $this->javaScript = array();
        return $this;
    }


    /**
     * Returns JSON-string
     *
     * @return string
     */
    public function __toString()
    {

        $returnArray = array();
        $returnArray['status'] = $this->status;

        if ($this->message) {
            $returnArray['message'] = $this->message;
        }

        if ($this->data) {
            $returnArray['data'] = $this->data;
        }

        if (
            ($this->javaScript)
            && ($this->javaScript['before'])
        ) {
            $returnArray['javaScriptBefore'] = implode(' ', $this->javaScript['before']);
        }

        if ($this->html) {
            $returnArray['html'] = $this->html;
        }

        if (
            ($this->javaScript)
            && ($this->javaScript['after'])
        ) {
            $returnArray['javaScriptAfter'] = implode(' ', $this->javaScript['after']);
        }

        return json_encode($returnArray);
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