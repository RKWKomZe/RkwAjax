<?php
namespace RKW\RkwAjax\Utilities;

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

use RKW\RkwAjax\Helper\AjaxHelper;

/**
 * Class AjaxWrapperViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwAjax
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class DomUtility
{


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
     * Sets id-tag in first element found and wraps content with ajax-tags in a given HTML-code
     *
     * @param string $html
     * @param AjaxHelper $ajaxHelper
     * @param int $ajaxId
     * @param string $ajaxAction
     * @return string
     */
    public function setAjaxAttributesToElements(
        $html,
        AjaxHelper $ajaxHelper,
        $ajaxId,
        $ajaxAction = 'replace'
    ) {

        // load DOM without implied wrappers
        @$dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $firstWrap = null;
        foreach (self::ALLOWED_TAGS as $tag) {
            if ($firstWrap = $dom->getElementsByTagName($tag)->item(0)) {
                break;
            }
        }

        if ($firstWrap instanceof \DOMElement) {

            // set id and data attributes in first allowed tag
            $id = $ajaxHelper->getKey() . '-' . $ajaxId;
            $firstWrap->setAttribute('id', $id);
            $firstWrap->setAttribute('data-rkwajax-id', $ajaxId);
            $firstWrap->setAttribute('data-rkwajax-action', $ajaxAction);
        }

        /** @see: https://stackoverflow.com/questions/8218230/php-domdocument-loadhtml-not-encoding-utf-8-correctly */
        return utf8_decode($dom->saveHTML($dom->documentElement));
    }



    /**
     * Gets the DOM-Elements by ajax-attributes
     *
     * @param string $html
     * @return array
     */
    public function getElementsByAjaxAttributes(
        $html
    ) {

        // load DOM without implied wrappers
        @$dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $elementList = [];

        // find relevant elements with ajax-attributes
        /** @var \DOMElement $tag */
        foreach (self::ALLOWED_TAGS as $tag) {

            /** @var \DOMElement $element */
            foreach($dom->getElementsByTagName($tag) as $element) {
                if (
                    ($element->hasAttribute('data-rkwajax-id'))
                    && ($element->hasAttribute('data-rkwajax-action'))
                    && ($element->hasAttribute('id'))
                ) {
                    $elementList[] = $element;
                }
            }
        }

        return $elementList;
    }


    /**
     * Gets the DOM element by id
     *
     * @param string $html
     * @param string $id
     * @return \DOMElement|null
     */
    public function getElementById(
        $html,
        $id
    ) {
        // load DOM without implied wrappers
        @$dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        /** @var \DOMElement $element */
        if ($element = $dom->getElementById($id)) {
            if (in_array($element->tagName, self::ALLOWED_TAGS)) {
                return $element;
            }
        }

        return null;
    }


    /**
     * Get the innerHTML of an DOM-Element
     *
     * @param \DOMElement $element
     * @return string
     */
    public function getInnerHtml(\DOMElement $element)
    {
        $innerHtml = '';
        $children = $element->childNodes;
        foreach ($children as $child) {

            /** @see: https://stackoverflow.com/questions/8218230/php-domdocument-loadhtml-not-encoding-utf-8-correctly */
            $innerHtml .= utf8_decode($child->ownerDocument->saveHTML($child));
        }
        return $innerHtml;
    }
}