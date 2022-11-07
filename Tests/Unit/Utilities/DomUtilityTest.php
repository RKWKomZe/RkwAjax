<?php
namespace RKW\RkwAjax\Tests\Unit\Utilities;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use Masterminds\HTML5;
use RKW\RkwAjax\Helper\AjaxHelper;
use RKW\RkwAjax\Utilities\DomUtility;
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
class DomUtilityTest extends UnitTestCase
{

    const FIXTURE_PATH = __DIR__ . '/DomUtilityTest/Fixtures';

    /**
     * @var \RKW\RkwAjax\Helper\AjaxHelper
     */
    private $ajaxHelper;

    /**
     * @var \RKW\RkwAjax\Utilities\DomUtility
     */
    private $subject;

    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp(): void
    {

        parent::setUp();
        $this->ajaxHelper = GeneralUtility::makeInstance(AjaxHelper::class);
        $this->subject = GeneralUtility::makeInstance(DomUtility::class);

    }


    /**
     * @test
     */
    public function setAjaxAttributesToElementsSetsAttributes ()
    {

        /**
        * Scenario:
        *
        * Given the html contains a wrapping element
        * Given the html the wrapping element is a valid tag
        * When setAjaxAttributesToElements is called
        * Then the wrapper gets the corresponding ajax-attribute set
        */
        $source = file_get_contents(self::FIXTURE_PATH . '/Source/Check10.txt');
        $expected = file_get_contents(self::FIXTURE_PATH . '/Expected/Check10.txt');

        $this->ajaxHelper->setContentUid(55);
        $result = $this->subject::setAjaxAttributesToElements($source, $this->ajaxHelper, 99, 'replace');
        self::assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function setAjaxAttributesToElementsSetsAttributesRecursive ()
    {

        /**
         * Scenario:
         *
         * Given the html contains a wrapping element
         * Given the html the wrapping element is a valid tag
         * Given the valid tag is not the first one
         * When setAjaxAttributesToElements is called
         * Then the wrapper gets the corresponding ajax-attribute set
         */
        $source = file_get_contents(self::FIXTURE_PATH . '/Source/Check20.txt');
        $expected = file_get_contents(self::FIXTURE_PATH . '/Expected/Check20.txt');

        $this->ajaxHelper->setContentUid(55);
        $result = $this->subject::setAjaxAttributesToElements($source, $this->ajaxHelper, 99, 'replace');
        self::assertEquals($expected, $result);
    }


    /**
     * @test
     */
    public function setAjaxAttributesToElementsRespectsAllowedTags ()
    {

        /**
         * Scenario:
         *
         * Given the html contains a wrapping element
         * Given the wrapping element is an invalid tag
         * When setAjaxAttributesToElements is called
         * Then the wrapper gets no corresponding id-attribute set
         * Then no comment elements are set inside of it
         */
        $source = file_get_contents(self::FIXTURE_PATH . '/Source/Check30.txt');
        $expected = file_get_contents(self::FIXTURE_PATH . '/Expected/Check30.txt');
        $this->ajaxHelper->setContentUid(55);

        $result = $this->subject::setAjaxAttributesToElements($source, $this->ajaxHelper, 99, 'replace');
        self::assertEquals($expected, $result);
    }


    /**
     * @test
     */
    public function setAjaxAttributesToElementsAddsEmptyDivAsFallback ()
    {

        /**
         * Scenario:
         *
         * Given the html contains a wrapping element
         * Given the wrapping element empty
         * When setAjaxAttributesToElements is called
         * Then an empty DIV is added
         * Then the wrapper gets the corresponding ajax-attribute set
         */
        $source = file_get_contents(self::FIXTURE_PATH . '/Source/Check100.txt');
        $expected = file_get_contents(self::FIXTURE_PATH . '/Expected/Check100.txt');
        $this->ajaxHelper->setContentUid(55);

        $result = $this->subject::setAjaxAttributesToElements($source, $this->ajaxHelper, 99, 'replace');
        self::assertEquals($expected, $result);
    }



    //=============================================

    /**
     * @test
     */
    public function getElementsByAjaxAttributesReturnsElements ()
    {

        /**
         * Scenario:
         *
         * Given the html contains two elements with ajax-attributes
         * Given the two elements use a valid tag
         * When getElementsByAjaxAttributes is called
         * Then the two elements are returned
         */
        $source = file_get_contents(self::FIXTURE_PATH . '/Source/Check40.txt');
        $result = $this->subject::getElementsByAjaxAttributes($source);
        self::assertCount(2, $result);
        self::assertInstanceOf(\DOMElement::class, $result[0]);
        self::assertInstanceOf(\DOMElement::class, $result[1]);
        self::assertEquals('773bc02ea02b903280d609bb6a883735afbd7f14-1', $result[0]->getAttribute('id'));
        self::assertEquals('773bc02ea02b903280d609bb6a883735afbd7f14-2', $result[1]->getAttribute('id'));

    }

    /**
     * @test
     */
    public function getElementsByAjaxAttributesRespectsAllowedTags ()
    {

        /**
         * Scenario:
         *
         * Given the html contains two elements with ajax-attributes
         * Given one element uses invalid tags
         * Given one elements uses a valid tag
         * When getElementsByAjaxAttributes is called
         * Then one elements is returned
         */
        $source = file_get_contents(self::FIXTURE_PATH . '/Source/Check50.txt');
        $result = $this->subject::getElementsByAjaxAttributes($source);
        self::assertCount(1, $result);
        self::assertInstanceOf(\DOMElement::class, $result[0]);
        self::assertEquals('773bc02ea02b903280d609bb6a883735afbd7f14-2', $result[0]->getAttribute('id'));

    }
    //=============================================

    /**
     * @test
     */
    public function getElementByItReturnsElement ()
    {

        /**
         * Scenario:
         *
         * Given the html contains two elements with id-attributes
         * Given the two elements use a valid tag
         * When getElementById is called with one of the ids
         * Then the elements with the given id is returned
         */
        $source = file_get_contents(self::FIXTURE_PATH . '/Source/Check80.txt');
        $result = $this->subject::getElementById($source, '773bc02ea02b903280d609bb6a883735afbd7f14-1');
        self::assertInstanceOf(\DOMElement::class, $result);
        self::assertEquals('773bc02ea02b903280d609bb6a883735afbd7f14-1', $result->getAttribute('id'));

    }

    /**
     *
     */
    public function getElementByIdRespectsAllowedTags ()
    {

        /**
         * Scenario:
         *
         * Given the html contains two elements with id-attributes
         * Given one of the elements uses a invalid tag
         * When getElementById is called with id of the element with the invalid tag
         * Then null is returned
         */
        $source = file_get_contents(self::FIXTURE_PATH . '/Source/Check90.txt');
        $result = $this->subject::getElementById($source, '773bc02ea02b903280d609bb6a883735afbd7f14-1');
        self::assertNull($result);

    }

    //=============================================
    /**
     * @test
     */
    public function getInnerHtmlReturnsInnerHtml ()
    {

        /**
         * Scenario:
         *
         * Given we have DOMElement with innerHtml
         * When getInnerHtmlis called
         * Then the innerHtml is returned
         */
        $source = file_get_contents(self::FIXTURE_PATH . '/Source/Check60.txt');
        $expected = file_get_contents(self::FIXTURE_PATH . '/Expected/Check60.txt');

        $htmlObject = new HTML5(
            ['disable_html_ns' => true]
        );
        $doc = $htmlObject->loadHTML($source);

        $result = $this->subject::getInnerHtml($doc->getElementsByTagName('body')->item(0));
        self::assertEquals($expected, $result);

    }

    /**
     * @test
     */
    public function getInnerHtmlIgnoresEmptyTags ()
    {

        /**
         * Scenario:
         *
         * Given we have DOMElement without innerHtml
         * When getInnerHtmlis called
         * Then nothing is returned
         */
        $source = file_get_contents(self::FIXTURE_PATH . '/Source/Check70.txt');

        $htmlObject = new HTML5(
            ['disable_html_ns' => true]
        );
        $doc = $htmlObject->loadHTML($source);

        $result = $this->subject::getInnerHtml($doc->getElementsByTagName('body')->item(0));
        self::assertEmpty($result);

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
