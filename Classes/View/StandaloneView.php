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
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;

/**
 * A standalone template view.
 * Should be used as view if you want to use Fluid without Extbase extensions
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwAjax
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @api
 */
class StandaloneView extends \TYPO3\CMS\Fluid\View\StandaloneView
{


    /**
     * Sets the template
     *
     * @param string $templateName
     * @api
     */
    public function setTemplate($templateName)
    {

        $currentVersion = VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
        if ($currentVersion < 8000000) {

            parent::setTemplate($templateName);

        } else {

            // check if there is a template path included
            // with TYPO3 8 templates are set via the given controller action.
            // Thus using setTemplate with relative paths will result in using this path as controller action.
            if (
                ($subFolder = dirname($templateName))
                && ($subFolder != '.')
            ){
                $newTemplatePaths = $existingTemplatePaths = $this->baseRenderingContext->getTemplatePaths()->getTemplateRootPaths();
                foreach ($existingTemplatePaths as $path) {
                    $newTemplatePaths[] = $path . $subFolder;
                }
                $this->baseRenderingContext->getTemplatePaths()->setTemplateRootPaths($newTemplatePaths);
                parent::setTemplate(basename($templateName));

                \TYPO3\CMS\Core\Utility\GeneralUtility::deprecationLog(__CLASS__ . '::' . __METHOD__ . '(): Please do not use this method with relative paths included. ' .
                    'The paths should be added to the TemplateRootPaths instead. From TYPO3 8.7 on templates are set via the given controller action. ' .
                    'Thus using setTemplate with relative paths will in turn result in using this path as controller action.'
                );
            }
        }
    }



    /**
     * Set the request object
     *
     * @param \TYPO3\CMS\Extbase\Mvc\Web\Request $webRequest
     * @return void
     * @see __construct()
     */
    public function setRequest(\TYPO3\CMS\Extbase\Mvc\Web\Request $webRequest)
    {
        // set basics
        $webRequest->setRequestURI(GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'));
        $webRequest->setBaseURI(GeneralUtility::getIndpEnv('TYPO3_SITE_URL'));

        /** @var UriBuilder $uriBuilder  **/
        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        $uriBuilder->setRequest($webRequest);

        $currentVersion = VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
        if ($currentVersion < 8000000) {

            /** @var ControllerContext $controllerContext **/
            $this->controllerContext->setRequest($webRequest);
            $this->controllerContext->setUriBuilder($uriBuilder);

        } else {

            /** @var ControllerContext $controllerContext **/
            $controllerContext = $this->objectManager->get(ControllerContext::class);
            $controllerContext->setRequest($webRequest);
            $controllerContext->setUriBuilder($uriBuilder);

            /** @var RenderingContext $renderingContext */
            $renderingContext = $this->objectManager->get(RenderingContext::class, $this);
            $renderingContext->setControllerContext($controllerContext);
            $this->setRenderingContext($renderingContext);
        }
    }

}
