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

use Masterminds\HTML5;
use RKW\RkwAjax\Helper\AjaxHelper;

/**
 * Class AjaxWrapperViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
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
     * @throws \DOMException
     */
    public static function setAjaxAttributesToElements(
        string $html,
        AjaxHelper $ajaxHelper,
        int $ajaxId,
        string $ajaxAction = 'replace'
    ): string {

        // load DOM without implied wrappers
        /** @var HTML5 $dom  */
        $htmlObject = new HTML5(
            [
                'disable_html_ns' => true,
            ]
        );
        $doc = $htmlObject->loadHTML($html);
        $html = $doc->getElementsByTagName('html')->item(0);

        // add HTML-element when no html elements are available
        if ($doc->getElementsByTagName('html')->count() == 0) {
            $html = $doc->createElement('html');
            $doc->appendChild($html);
        }

        // search for matching tags
        $firstWrap = null;
        foreach (self::ALLOWED_TAGS as $tag) {
            if ($firstWrap = $doc->getElementsByTagName($tag)->item(0)) {
                break;
            }
        }

        // add empty div when no allowed tag is found
        if (! $firstWrap){

            $firstWrap = $doc->createElement('div');
            $html->appendChild($firstWrap);
        }

        if ($firstWrap instanceof \DOMElement) {

            // set id and data attributes in first allowed tag
            $id = $ajaxHelper->getKey() . '-' . $ajaxId;
            $firstWrap->setAttribute('id', $id);
            $firstWrap->setAttribute('data-rkwajax-id', $ajaxId);
            $firstWrap->setAttribute('data-rkwajax-action', $ajaxAction);
        }

        // HTML-Tag is inserted automatically, therefore we return the innerHTML of it
        return self::getInnerHtml($doc->getElementsByTagName('html')->item(0));
    }



    /**
     * Gets the DOM-Elements by ajax-attributes
     *
     * @param string $html
     * @return array
     */
    public static function getElementsByAjaxAttributes(string $html): array {

        // load DOM without implied wrappers
        /** @var HTML5 $dom  */
        $htmlObject = new HTML5(
            ['disable_html_ns' => true]
        );
        $doc = $htmlObject->loadHTML($html);

        $elementList = [];

        // find relevant elements with ajax-attributes
        /** @var \DOMElement $tag */
        foreach (self::ALLOWED_TAGS as $tag) {

            /** @var \DOMElement $element */
            foreach($doc->getElementsByTagName($tag) as $element) {
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
    public static function getElementById(
        string $html,
        string $id
    ):? \DOMElement {

        // load DOM without implied wrappers
        $htmlObject = new HTML5(
            ['disable_html_ns' => true]
        );
        $doc = $htmlObject->loadHTML($html);

        /** @var \DOMElement $element */
        if ($element = $doc->getElementById($id)) {
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
    public static function getInnerHtml(\DOMElement $element): string
    {
        $innerHtml = '';
        $children = $element->childNodes;
        foreach ($children as $child) {
            $innerHtml .= $child->ownerDocument->saveHTML($child);
        }
        return trim($innerHtml);
    }
}
