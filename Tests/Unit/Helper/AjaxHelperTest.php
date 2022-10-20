<?php
namespace RKW\RkwAjax\Tests\Unit\Helper;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use RKW\RkwAjax\Helper\AjaxHelper;

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
 * AjaxHelperTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class AjaxHelperTest extends UnitTestCase
{

    const FIXTURE_PATH = __DIR__ . '/AjaxHelperTest/Fixtures';


    /**
     * @var \RKW\RkwAjax\Helper\AjaxHelper
     */
    private $subject;


    /**
     * Setup
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(AjaxHelper::class);

    }

    //=============================================

    /**
     * @test
     */
    public function getKeyReturnsExpectedDefaultIfNothingSet ()
    {
        /**
         * Scenario:
         *
         * Given nothing is set
         * When getKey is called
         * Then the sha1 of zero is returned
         */
        $expected = sha1(0);
        self::assertEquals($expected, $this->subject->getKey());
    }

    /**
     * @test
     */
    public function getKeyReturnsExpectedValueIfContentUidSet ()
    {

        /**
         * Scenario:
         *
         * Given a contentUid is set
         * When getKey is called
         * Then the sha1 of this contentUid is returned
         */
        $this->subject->setContentUid(12345);
        $expected = sha1(12345);
        self::assertEquals($expected, $this->subject->getKey());
    }


    //=============================================
    /**
     * @test
     */
    public function getContentUidReturnsValueSetWithSetContentUid ()
    {
        /**
         * Scenario:
         *
         * Given a contentUid is set
         * When getContentUid is called
         * Then the this contentUid is returned
         */
        $this->subject->setContentUid(12345);
        self::assertEquals(12345, $this->subject->getContentUid());
    }


    //=============================================

    /**
     * @test
     */
    public function getIsAjaxCallReturnsFalseIfNotAjaxPageType ()
    {
        /**
         * Scenario:
         *
         * Given a pageType not equal to 250 is called
         * When the method is called
         * Then false is returned
         */
        $_GET['type'] = 0;
        self::assertFalse($this->subject->getIsAjaxCall());
    }


    /**
     * @test
     */
    public function getIsAjaxCallReturnsFalseIfAjaxParamsWithoutAjaxPageType ()
    {

        /**
         * Scenario:
         *
         * Given a pageType not equal to 250 is called
         * Given a rkw_ajax param is set
         * When the method is called
         * Then false is returned
         */
        $_GET['type'] = 0;
        $_GET['rkw_ajax'] = ['test'];
        self::assertFalse($this->subject->getIsAjaxCall());
    }

    /**
     * @test
     */
    public function getIsAjaxCallReturnsFalseIfAjaxPageTypeWithoutAjaxParams ()
    {
        /**
         * Scenario:
         *
         * Given a pageType equal to 250 is called
         * Given no rkw_ajax param is set
         * When the method is called
         * Then false is returned
         */
        $_GET['type'] = 250;
        self::assertFalse($this->subject->getIsAjaxCall());
    }


    /**
     * @test
     */
    public function getIsAjaxCallReturnsTrueIfAjaxPageTypeWithAjaxParams ()
    {
        /**
         * Scenario:
         *
         * Given a pageType equal to 250 is called
         * Given a rkw_ajax param is set
         * When the method is called
         * Then true is returned
         */
        $_GET['type'] = 250;
        $_GET['rkw_ajax'] = ['test'];
        self::assertTrue($this->subject->getIsAjaxCall());

    }

    //=============================================

    /**
     * @test
     */
    public function getIsPostCallReturnsFalseIfNoPost ()
    {
        /**
         * Scenario:
         *
         * Given no post variables are set
         * When the method is called
         * Then false is returned
         */
        self::assertFalse($this->subject->getIsPostCall());
    }


    /**
     * @test
     */
    public function getIsPostCallReturnsTrueIfPost ()
    {
        /**
         * Scenario:
         *
         * Given post variables are set
         * When the method is called
         * Then false is returned
         */
        $_POST['test'] = 1;
        self::assertFalse($this->subject->getIsPostCall());
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
