<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function($extKey)
    {

        //=================================================================
        // Add TypoScript
        //=================================================================
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
            'RKW Ajax',
            'constants',
            '<INCLUDE_TYPOSCRIPT: source="FILE: EXT:rkw_ajax/Configuration/TypoScript/constants.typoscript">'
        );
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
            'RKW Ajax',
            'setup',
            '<INCLUDE_TYPOSCRIPT: source="FILE: EXT:rkw_ajax/Configuration/TypoScript/setup.typoscript">'
        );

        //=================================================================
        // Configure Logger
        //=================================================================
        $GLOBALS['TYPO3_CONF_VARS']['LOG']['RKW']['RkwAjax']['writerConfiguration'] = array(

            // configuration for WARNING severity, including all
            // levels with higher severity (ERROR, CRITICAL, EMERGENCY)
            \TYPO3\CMS\Core\Log\LogLevel::DEBUG => array(
                // add a FileWriter
                'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => array(
                    // configuration for the writer
                    'logFile' => 'typo3temp/var/logs/tx_rkwajax.log'
                )
            ),
        );

    },
    $_EXTKEY
);


