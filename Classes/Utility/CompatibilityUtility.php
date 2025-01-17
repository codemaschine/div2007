<?php

namespace JambageCom\Div2007\Utility;

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
 * Part of the div2007 (Static Methods for Extensions since 2007) extension.
 *
 * Backwards compatibility related functions
 *
 * @author  Franz Holzinger <franz@ttproducts.de>
 * @maintainer	Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage div2007
 *
 *
 */


use TYPO3\CMS\Core\Utility\GeneralUtility;


class CompatibilityUtility {

    static public function getPageRepository ()
    {
        $classname = \TYPO3\CMS\Core\Domain\Repository\PageRepository::class;
        $pageRepository = GeneralUtility::makeInstance($classname);
        return $pageRepository;
    }
    
    static public function isLoggedIn ()
    {
        $context = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class);
        $result = $context->getPropertyFromAspect('frontend.user', 'isLoggedIn');

        return $result;
    }

    static public function includeHiddenContent ()
    {
        $context = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class);
        $result = $context->getPropertyFromAspect('visibility', 'includeHiddenContent');

        return $result;
    }

    static public function includeHiddenPages ()
    {
        $context = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class);
        $result = $context->getPropertyFromAspect('visibility', 'includeHiddenPages');

        return $result;
    }    
}

