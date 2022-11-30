<?php
namespace RKW\RkwAjax\Tests\Unit\Encoder;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use RKW\RkwAjax\Encoder\JsonEncoder;
use RKW\RkwMailer\Domain\Repository\QueueMailRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

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
 * JsonEncoderTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class JsonEncoderTest extends UnitTestCase
{

    const FIXTURE_PATH = __DIR__ . '/JsonEncoderTest/Fixtures';


    /**
     * @var \RKW\RkwAjax\Encoder\JsonEncoder
     */
    private $subject;


    /**
     * Setup
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(JsonEncoder::class, ['unitTest' => true]);

    }

    //=============================================

    /**
     * @todo
     * @test
     */
    public function testing ()
    {

        self::assertEquals(1, 1);
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
