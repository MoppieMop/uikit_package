<?php
declare(strict_types = 1);

/*
 * This file is part of the package bk2k/bootstrap-package.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace BK2K\BootstrapPackage\Hooks\Frontend;

use BK2K\BootstrapPackage\Utility\LanguageUtility;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Dynamically creates language config for TS Setup
 */
class TypoScriptLanguageHook
{
    const SYS_LANGUAGE_UID_PLACEHOLDER = '###SYSLANGUAGEUIDPLACEHOLDER###';
    const LANGUAGE_PLACEHOLDER = '###LANGUAGEPLACEHOLDER###';
    const LOCALE_PLACEHOLDER = '###LOCALEPLACEHOLDER###';
    const HREF_LANG_PLACEHOLDER = '###HREFLANGPLACEHOLDER###';
    const DIRECTION_PLACEHOLDER = '###DIRECTIONPLACEHOLDER###';

    /**
     * @var array
     */
    protected $setupTemplate = [
        '[globalVar = GP:L = ' . self::SYS_LANGUAGE_UID_PLACEHOLDER . ']',
        'config {',
        '    sys_language_uid = ' . self::SYS_LANGUAGE_UID_PLACEHOLDER,
        '    language = ' . self::LANGUAGE_PLACEHOLDER,
        '    locale_all = ' . self::LOCALE_PLACEHOLDER,
        '    htmlTag_setParams = lang="' . self::HREF_LANG_PLACEHOLDER . '" dir="' . self::DIRECTION_PLACEHOLDER . '" class="no-js"',
        '}',
        '[global]'
    ];

    /**
     * Returns the TypoScript Setup language conditions
     *
     * @return string
     */
    protected function createLanguageConditions(): string
    {
        $setup = '';
        foreach (LanguageUtility::getLanguageRows() as $uid => $row) {
            $template = $this->setupTemplate;
            if ($uid === 0) {
                $template = array_slice($template, 1, -1);
            }
            $template = implode(LF, $template) . LF;
            $template = str_replace([
                self::SYS_LANGUAGE_UID_PLACEHOLDER,
                self::LANGUAGE_PLACEHOLDER,
                self::LOCALE_PLACEHOLDER,
                self::HREF_LANG_PLACEHOLDER,
                self::DIRECTION_PLACEHOLDER
            ], [
                $uid,
                $row['twoLetterIsoCode'],
                $row['locale'],
                $row['hreflang'],
                $row['direction']
            ], $template);
            $setup .= $template;
        }

        return $setup;
    }

    /**
     * Add TypoScript language setup
     *
     * @param array $params
     * @param TemplateService $templateService
     * @return void
     */
    public function addLanguageSetup(&$params, &$templateService)
    {
        if (TYPO3_MODE === 'BE') {
            ExtensionManagementUtility::addTypoScriptSetup($this->createLanguageConditions());
        } else {
            $templateService->config = [$this->createLanguageConditions()] + $templateService->config;
        }
    }
}
