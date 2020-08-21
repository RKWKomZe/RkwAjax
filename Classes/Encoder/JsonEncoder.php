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

use RKW\RkwAjax\Utilities\DomUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class JsonEncoder
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwAjax
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class JsonEncoder extends AbstractJsonEncoder
{


    /**
     * Sets HTML by given list of ids in DOM
     *
     * @param string $html
     * @param array $idList
     * @param string $idPrefix
     * @return $this
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function setHtmlByDom($html, array $idList, $idPrefix = '')
    {
        /** @var DomUtility $domUtility */
        $domUtility = GeneralUtility::makeInstance(DomUtility::class);

        // now get contents for all given ids
        foreach ($idList as $id) {

            $finalId = $idPrefix . '-' . $id;
            $element = $domUtility::getElementById($html, $finalId);

            if ($element instanceof \DOMElement) {
                $type = $element->getAttribute('data-rkwajax-action');
                $finalType = 'replace';
                if (in_array(strtolower($type), array('append', 'prepend', 'replace'))) {
                    $finalType = strtolower($type);
                }

                $this->html[$finalId][$finalType] = $domUtility::getInnerHTML($element);
            }
        }

        return $this;
    }

}