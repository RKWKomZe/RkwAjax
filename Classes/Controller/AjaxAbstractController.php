<?php

namespace RKW\RkwAjax\Controller;
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
use TYPO3\CMS\Extbase\Service\FlexFormService;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;


/**
 * Class AjaxAbstractController
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwAjax
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @api
 */
abstract class AjaxAbstractController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * The default view object to use if none of the resolved views can render
     * a response for the current request.
     *
     * @var string
     * @api
     */
    protected $defaultViewObjectName = \RKW\RkwAjax\View\AjaxView::class;

    /**
     * @var \RKW\RkwAjax\Domain\Repository\ContentRepository
     * @inject
     */
    protected $contentRepository;

    /**
     * @var \RKW\RkwAjax\Helper\AjaxHelper
     * @inject
     */
    protected $ajaxHelper;


    /**
     * @param ConfigurationManagerInterface $configurationManager
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager)
    {
        parent::injectConfigurationManager($configurationManager);

        // load data from ContentObjectRender or via database
        if (
            (! $configurationManager->getContentObject())
            || (! $flexFormData = $configurationManager->getContentObject()->data['pi_flexform'])
        ){

            /** @var \RKW\RkwAjax\Domain\Model\Content content */
            if ($content = $this->contentRepository->findByIdentifier($this->ajaxHelper->getContentUid())) {
                $flexFormData = $content->getPiFlexform();
            }
        }

        // merge FlexForm settings with TypoScript settings
        if ($flexFormData) {

            /** @var FlexFormService $flexFormService */
            $flexFormService = GeneralUtility::makeInstance(FlexFormService::class);
            $flexFormSettings = $flexFormService->convertFlexFormContentToArray($flexFormData);
            $this->settings = array_merge($this->settings, $flexFormSettings['settings']);
        }
    }


    /**
     * Assigns ajaxUtility to all actions
     *
     * @param ViewInterface $view The view to be initialized
     * @api
     */
    protected function initializeView(ViewInterface $view)
    {
        $view->assign('ajaxHelper', $this->ajaxHelper);
    }



}