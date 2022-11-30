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


/**
 * Class GeneralUtility
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwAjax
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GeneralUtility extends \TYPO3\CMS\Core\Utility\GeneralUtility
{


   /**
    * Merges array recursively but behaves like array_merge
    *
    * @param array $array1
    * @param array $array2
    * @return array
    * @author Daniel <daniel@danielsmedegaardbuus.dk>
    * @author Gabriel Sobrinho <gabriel.sobrinho@gmail.com>
    * @author fantomx1 <fantomx1@gmail.om>
    * @author Steffen Kroggel <developer@steffenkroggel.de>
    */
    static function arrayMergeRecursiveDistinct (array &$array1, array &$array2 ): array
    {

        $merged = $array1;
        foreach ($array2 as $key => &$value) {

            // numeric keys are simply added
            if (is_numeric($key)) {
                $merged [] = $value;

            } else {

                // recursive call if array
                if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                    $merged[$key] = self::arrayMergeRecursiveDistinct($merged[$key], $value);

                // associative keys are overridden
                } else {
                    $merged[$key] = $value;
                }
            }


        }

      return $merged;
    }


}
