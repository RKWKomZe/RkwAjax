<?php
namespace RKW\RkwAjax\Tests\Unit\Utilities;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use Masterminds\HTML5;
use RKW\RkwAjax\Utilities\GeneralUtility as RkwGeneralUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;


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
 * DomUtilityTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwAjax
 * @license http://www.gnu.org/licenses/gpl.txt GNU General Public License, version 3 or later
 */
class GeneralUtilityTest extends UnitTestCase
{

    const FIXTURE_PATH = __DIR__ . '/GeneralUtilityTest/Fixtures';


    /**
     * @var \RKW\RkwAjax\Utilities\GeneralUtility
     */
    private $subject;

    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp(): void
    {

        parent::setUp();
        $this->subject = GeneralUtility::makeInstance(RkwGeneralUtility::class);

    }


    /**
     * @test
     */
    public function arrayMergeRecursiveDistinctMergesOneDimensionalArray ()
    {

        /**
        * Scenario:
        *
        * Given array1 has one dimension with keys and numbers
        * Given array2 has one dimension with keys and numbers
        * When arrayMergeRecursiveDistinct is called
        * Then the arrays are merged like array_merge does
        */

        $array1 = [
            'farbe' => 'rot',
            2,
            4
        ];

        $array2 = [
            'a',
            'b',
            'farbe' => 'grün',
            'form' => 'trapezoid',
             4
        ];

        $expected = [
            'farbe' => 'grün',
            0 => 2,
            1 => 4,
            2 => 'a',
            3 => 'b',
            'form' => 'trapezoid',
            4 => 4,
        ];

        $result = array_merge ($array1, $array2);
        $result2 = $this->subject::arrayMergeRecursiveDistinct($array1, $array2);
        self::assertEquals($expected, $result);
        self::assertEquals($expected, $result2);
    }


    /**
     * @test
     */
    public function arrayMergeRecursiveDistinctMergesTwoDimensionalArray ()
    {

        /**
         * Scenario:
         *
         * Given array1 has two dimensions with keys and numbers
         * Given array2 has two dimension with keys and numbers
         * When arrayMergeRecursiveDistinct is called
         * Then the arrays are merged like array_merge does, but recursively
         */

        $array1 = [
            'farbe' => 'rot',
            2,
            4,
            'sub' => [
                'farbe' => 'rot',
                2,
                3,
            ]
        ];

        $array2 = [
            'a',
            'b',
            'farbe' => 'grün',
            'form' => 'trapezoid',
            4,
            'sub' => [
                'a',
                'b',
                'farbe' => 'blau',
                'form' => 'trapezoid',
                3,
            ]
        ];

        $expected = [
            'farbe' => 'grün',
            0 => 2,
            1 => 4,
            'sub' => [
                'farbe' => 'blau',
                0 => 2,
                1 => 3,
                2 => 'a',
                3 => 'b',
                'form' => 'trapezoid',
                4 => 3,
            ],
            2 => 'a',
            3 => 'b',
            'form' => 'trapezoid',
            4 => 4,
        ];

        $result = $this->subject::arrayMergeRecursiveDistinct($array1, $array2);
        self::assertEquals($expected, $result);
    }

    //=============================================


    /**
     * TearDown
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }








}
