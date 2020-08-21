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
    protected function setUp()
    {
        parent::setUp();
        $this->subject = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(AjaxHelper::class, ['unitTest' => true]);

    }

    //=============================================
    /**
     * @test
     */
    public function initSetsContentUidByGet ()
    {

        /**
         * Scenario:
         *
         * Given rkwAjax-array is set via $_GET
         * Given the contentUid is set in this array
         * When init is called
         * Then getContentUid returns the given value
         */
        $_GET['rkw_ajax']['cid'] = 95;
        $this->subject->init();
        self::assertEquals(95, $this->subject->getContentUid());
    }


    /**
     * @test
     */
    public function initSetsKeyByGet ()
    {

        /**
         * Scenario:
         *
         * Given rkwAjax-array is set via $_GET
         * Given the key is set in this array
         * When init is called
         * Then getKey returns the given value
         */
        $_GET['rkw_ajax']['key'] = 'beatMeBitch';
        $this->subject->init();
        self::assertEquals('beatMeBitch', $this->subject->getKey());
    }


    /**
     * @test
     */
    public function initSetsIdListByGet ()
    {

        /**
         * Scenario:
         *
         * Given rkwAjax-array is set via $_GET
         * Given the idList is set in this array
         * When init is called
         * Then getIdList returns an array of the given ids
         */
        $_GET['rkw_ajax']['idl'] = '15,30';
        $this->subject->init();

        $result = $this->subject->getIdList();
        self::assertInternalType('array', $result );
        self::assertCount(2, $result );
        self::assertEquals(15, $result[0]);
        self::assertEquals(30, $result[1]);

    }

    /**
     * @test
     */
    public function initSetsContentUidByParams ()
    {

        /**
         * Scenario:
         *
         * Given a param array is set
         * Given the contentUid is set in this array
         * When init is called
         * Then getContentUid returns the given value
         */
        $params = ['cid' => 95];
        $this->subject->init($params);
        self::assertEquals(95, $this->subject->getContentUid());
    }


    /**
     * @test
     */
    public function initSetsKeyByParams ()
    {

        /**
         * Scenario:
         *
         * Given a param array is set
         * Given the key is set in this array
         * When init is called
         * Then getKey returns the given value
         */
        $params = ['key' => 'beatMeBitch'];
        $this->subject->init($params);
        self::assertEquals('beatMeBitch', $this->subject->getKey());
    }


    /**
     * @test
     */
    public function initSetsIdListByParams ()
    {

        /**
         * Scenario:
         *
         * Given a param array is set
         * Given the idList is set in this array
         * When init is called
         * Then getIdList returns an array of the given ids
         */
        $params = ['idl' => '15,30'];
        $this->subject->init($params);

        $result = $this->subject->getIdList();
        self::assertInternalType('array', $result );
        self::assertCount(2, $result );
        self::assertEquals(15, $result[0]);
        self::assertEquals(30, $result[1]);

    }

    /**
     * @test
     */
    public function initSetsContentUidByGetOverParams ()
    {

        /**
         * Scenario:
         *
         * Given rkwAjax-array is set via $_GET
         * Given a param array is set
         * Given the contentUid is set in both arrays
         * When init is called
         * Then getContentUid returns the given value of $_GET
         */
        $_GET['rkw_ajax']['cid'] = 95;
        $params = ['cid' => 70];
        $this->subject->init($params);
        self::assertEquals(95, $this->subject->getContentUid());
    }


    /**
     * @test
     */
    public function initSetsKeyByGetOverParams ()
    {

        /**
         * Scenario:
         *
         * Given rkwAjax-array is set via
         * Given a param array is set 
         * Given the key is set in both arrays
         * When init is called
         * Then getKey returns the given value of $_GET
         */
        $_GET['rkw_ajax']['key'] = 'beatMeBitch';
        $params = ['key' => 'beatMeHarderBitch'];
        $this->subject->init($params);
        self::assertEquals('beatMeBitch', $this->subject->getKey());
    }


    /**
     * @test
     */
    public function initSetsIdListByGetOverParams ()
    {

        /**
         * Scenario:
         *
         * Given rkwAjax-array is set via $_GET
         * Given a param array is set
         * Given the idList is set in both arrays
         * When init is called
         * Then getIdList returns an array of the given ids of $_GET
         */
        $_GET['rkw_ajax']['idl'] = '15,30';
        $params = ['idl' => '10,20'];
        $this->subject->init();

        $result = $this->subject->getIdList();
        self::assertInternalType('array', $result );
        self::assertCount(2, $result );
        self::assertEquals(15, $result[0]);
        self::assertEquals(30, $result[1]);

    }    
    
    //=============================================

    /**
     * @test
     */
    public function isAjaxCallReturnsFalseIfNotAjaxPageType ()
    {
        $this->subject->init();
        self::assertFalse($this->subject->isAjaxCall());
    }


    /**
     * @test
     */
    public function isAjaxCallReturnsFalseIfAjaxParamsWithoutAjaxPageType ()
    {
        $_GET['rkw_ajax'] = ['test'];
        $this->subject->init();
        self::assertFalse($this->subject->isAjaxCall());
    }

    /**
     * @test
     */
    public function isAjaxCallReturnsFalseIfAjaxPageTypeWithoutAjaxParams ()
    {
        $_GET['type'] = 250;
        $this->subject->init();
        self::assertFalse($this->subject->isAjaxCall());
    }


    /**
     * @test
     */
    public function isAjaxCallReturnsTrueeIfAjaxPageTypeWithAjaxParams ()
    {
        $_GET['type'] = 250;
        $_GET['rkw_ajax'] = ['test'];
        $this->subject->init();
        self::assertTrue($this->subject->isAjaxCall());
    }

    //=============================================
    /**
     * @test
     */
    public function getKeyReturnsSha1OfZeroIfNothingSet ()
    {
        $this->subject->init();
        self::assertEquals(sha1(0), $this->subject->getKey());
    }


    /**
     * @test
     */
    public function getKeyReturnsSha1OfGivenContentUid ()
    {
        $_GET['rkw_ajax']['cid'] = 95;
        $this->subject->init();
        self::assertEquals(sha1(95), $this->subject->getKey());
    }


    //=============================================
    /**
     * @test
     */
    public function getContentUidReturnsZeroIfNothingSet ()
    {
        $this->subject->init();
        self::assertEquals(0, $this->subject->getContentUid());
    }

    //=============================================
    /**
     * @test
     */
    public function getContentUidReturnsssdfsfsfsfasfasfasZeroIfNothingSet ()
    {
        $this->subject->init();
        self::assertEquals(0, $this->subject->getContentUid());
    }

    //=============================================

    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }








}