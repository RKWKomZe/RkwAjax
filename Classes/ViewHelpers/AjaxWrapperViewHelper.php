<?php

namespace RKW\RkwAjax\ViewHelpers;

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

use RKW\RkwAjax\Utilities\DomUtility;
use RKW\RkwAjax\Helper\AjaxHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Class AjaxWrapperViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwAjax
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class AjaxWrapperViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{

    use CompileWithContentArgumentAndRenderStatic;


    /**
     * List of allowed tags
     *
     * @const array
     */
    const ALLOWED_TAGS = [
        'div',
        'form'
    ];

    /**
     * The output must not be escaped.
     *
     * @var bool
     */
    protected $escapeOutput = false;


     /* To ensure all tags are removed, child node's output must not be escaped
     *
     * @var bool
     */
    protected $escapeChildren = false;


    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
    */
    public function initializeArguments()
    {
        $this->registerArgument('value', 'string', 'HTML');
        $this->registerArgument('ajaxId', 'int', 'Id for ajax requests', true);
        $this->registerArgument('ajaxHelper', AjaxHelper::class, 'AjaxHelper- Object', true);
        $this->registerArgument('ajaxAction', 'string', 'Ajax action', false, 'replace');
    }

    /**
     * Sets id-tag in first element found and wraps content with ajax-tags
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param \TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

        $value = $renderChildrenClosure();
        $ajaxId = intval($arguments['ajaxId']);
        $ajaxHelper = $arguments['ajaxHelper'];
        $ajaxAction = strtolower($arguments['ajaxAction']);

        /** @var DomUtility  $domUtility */
        $domUtility = GeneralUtility::makeInstance(DomUtility::class);
        return $domUtility::setAjaxAttributesToElements($value, $ajaxHelper, $ajaxId, $ajaxAction);
    }

}
