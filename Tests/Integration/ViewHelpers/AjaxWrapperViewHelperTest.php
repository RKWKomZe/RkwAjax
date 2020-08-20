<?php
namespace RKW\RkwAjax\Tests\Integration\ViewHelpers;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwAjax\Helper\AjaxHelper;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;


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
 * AjaxWrapperViewHelperTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwAjax
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class AjaxWrapperViewHelperTest extends FunctionalTestCase
{

    const FIXTURE_PATH = __DIR__ . '/AjaxWrapperViewHelperTest/Fixtures';

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_ajax'
    ];

    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [ ];

    /**
     * @var \RKW\RkwAjax\Helper\AjaxHelper
     */
    private $ajaxHelper;

    /**
     * @var \TYPO3\CMS\Fluid\View\StandaloneView
     */
    private $standAloneViewHelper;


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager;


    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp()
    {

        parent::setUp();

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_ajax/Configuration/TypoScript/setup.txt',
                'EXT:rkw_ajax/Tests/Integration/ViewHelpers/AjaxWrapperViewHelperTest/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );


        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->ajaxHelper = $this->objectManager->get(AjaxHelper::class);
        $this->standAloneViewHelper = $this->objectManager->get(StandaloneView::class);
        $this->standAloneViewHelper->setTemplateRootPaths(
            [
                0 => self::FIXTURE_PATH . '/Frontend/Templates'
            ]
        );


    }


    /**
     * @test
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itSetsIdAndWrapperComments ()
    {

        /**
        * Scenario:
        *
        * Given the ViewHelper is used around a wrapping element in a template
        * Given the wrapper is used with a valid tag inside
        * When the ViewHelper is rendered
        * Then the wrapper gets an corresponding id-attribute set
        * Then the comment elements are set inside of it and wrap the whole innerHTML
        */

        $this->standAloneViewHelper->setTemplate('Check10.html');
        $_GET['ajaxContentUid'] = '55';
        $this->standAloneViewHelper->assign('ajaxHelper', $this->ajaxHelper);

        $expected = file_get_contents(self::FIXTURE_PATH . '/Expected/Check10.txt');
        $result = $this->standAloneViewHelper->render();

        static::assertEquals($expected, $result);
    }

    /**
     * @test
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itSetsIdAndWrapperCommentsRecursive ()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used around a wrapping element in a template
         * Given the wrapper is used with a valid tag inside
         * Given the valid tag is not the first one*
         * When the ViewHelper is rendered
         * Then the wrapper gets an corresponding id-attribute set
         * Then the comment elements are set inside of it and wrap the whole innerHTML
         */

        $this->standAloneViewHelper->setTemplate('Check20.html');
        $_GET['ajaxContentUid'] = '55';
        $this->standAloneViewHelper->assign('ajaxHelper', $this->ajaxHelper);

        $expected = file_get_contents(self::FIXTURE_PATH . '/Expected/Check20.txt');
        $result = $this->standAloneViewHelper->render();

        static::assertEquals($expected, $result);
    }

    /**
     * @test
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRespectsAllowedTags ()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used around a wrapping element in a template
         * Given the wrapper is used with an invalid tag inside
         * When the ViewHelper is rendered
         * Then the wrapper gets no corresponding id-attribute set
         * Then no comment elements are set inside of it
         */

        $this->standAloneViewHelper->setTemplate('Check30.html');
        $_GET['ajaxContentUid'] = '55';
        $this->standAloneViewHelper->assign('ajaxHelper', $this->ajaxHelper);

        $expected = file_get_contents(self::FIXTURE_PATH . '/Expected/Check30.txt');
        $result = $this->standAloneViewHelper->render();

        static::assertEquals($expected, $result);
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