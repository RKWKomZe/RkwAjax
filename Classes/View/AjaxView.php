<?php
namespace RKW\RkwAjax\View;

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
use TYPO3\CMS\Extbase\Mvc\Web\Response;
use \TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * Class AjaxView
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwAjax
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class AjaxView extends \TYPO3\CMS\Fluid\View\TemplateView
{

    /**
     * @var \RKW\RkwAjax\Encoder\JsonEncoder
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $jsonEncoder;

    /**
     * @var \RKW\RkwAjax\Helper\AjaxRequestHelper
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $ajaxRequestHelper;

    /**
     * @var \RKW\RkwAjax\Helper\AjaxHelper
     */
    protected $ajaxHelper;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;


    /**
     * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
     */
    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }


    /**
     * Set AjaxHelper to view
     * @param \RKW\RkwAjax\Helper\AjaxHelper $ajaxHelper
     */
    public function setAjaxHelper( \RKW\RkwAjax\Helper\AjaxHelper $ajaxHelper)
    {
        $this->ajaxHelper = $ajaxHelper;
        $this->assign('ajaxHelper', $this->ajaxHelper);
    }


    /**
     * Loads the template source and render the template.
     * If "layoutName" is set in a PostParseFacet callback, it will render the file with the given layout.
     *
     * @param string|null $actionName If set, this action's template will be rendered instead of the one defined in the context.
     * @return string Rendered Template
     * @api
     */
    public function render($actionName = null)
    {
        $result = parent::render($actionName);

        // check for pageType and key
        if ($this->ajaxRequestHelper->getIsAjaxCall()) {

            $this->getLogger()->log(
                \TYPO3\CMS\Core\Log\LogLevel::DEBUG,
                sprintf(
                    'Ajax-Call with ajaxKey "%s" and ajaxIdList "%s" for view detected.',
                    $this->ajaxRequestHelper->getKey(),
                    $this->ajaxRequestHelper->getIdList()
                )
            );

            if (
                ($result)
                && ($this->ajaxRequestHelper->getKey() == $this->ajaxHelper->getKey())
                && ($ajaxIdList = $this->ajaxRequestHelper->getIdList())
                && ($ajaxKey = $this->ajaxRequestHelper->getKey())
                // && ($ajaxContentUid = $this->ajaxRequestHelper->getContentUid())
            ) {

                $json = $this->jsonEncoder->setHtmlByDom(
                    $result,
                    $ajaxIdList,
                    $ajaxKey
                );

                $this->sendResponse($json);
                return '';

            }
        }

        return $result;
    }

    /**
     * Send response to browser
     *
     * @param string $data The response data
     */
    protected function sendResponse($data)
    {
        $response = $this->objectManager->get(Response::class);
        $response->setHeader('Content-Type', 'application/json; charset=utf-8');
        $response->setContent($data);
        $response->sendHeaders();
        $response->send();
        exit;
    }


    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {

        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        }

        return $this->logger;
    }

}
