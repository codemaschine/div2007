<?php
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
 * Deprecated
 *
 * Part of the div2007 (Static Methods for Extensions since 2007) extension.
 *
 * email functions
 *
 * @author  Franz Holzinger <franz@ttproducts.de>
 * @maintainer	Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage div2007
 *
 *
 */



  // deprecated: will be removed in 2024

class tx_div2007_email {

	/**
	* sends the email in plaintext or HTML format or both
	*
	* @param string  $toEMail: recipients email address
	* @param string  $subject: subject of the message
	* @param string  $PLAINContent: plain version of the message
	* @param string  $HTMLContent: HTML version of the message
	* @param string  $fromEmail: email address
	* @param string  $fromName: name
	* @param string  $attachment: file name
	* @param string  $cc: CC
	* @param string  $bcc: BCC
	* @param string  $returnPath: return path
	* @param string  $replyTo: email address
	* @param string  $extKey: extension key
	* @param string  $hookVar: name of the hook
	* @return void
	*/
	static public function sendMail (
		$toEMail,
		$subject,
		$PLAINContent,
		$HTMLContent,
		$fromEMail,
		$fromName,
		$attachment = '',
		$cc = '',
		$bcc = '',
		$returnPath = '',
		$replyTo = '',
		$extKey = '',
		$hookVar = '',
		$defaultSubject = ''
	) {
        if (
            isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][DIV2007_EXT]['debug.']) &&
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][DIV2007_EXT]['debug.']['mail']
        ) {
            debug ($toEMail, '$toEMail'); // keep this
            debug ($subject, '$subject'); // keep this
            debug ($PLAINContent, '$PLAINContent'); // keep this
            debug ($HTMLContent, '$HTMLContent'); // keep this
            debug ($fromEMail, '$fromEMail'); // keep this
            debug ($fromName, '$fromName'); // keep this
            return TRUE;
        }
            
		$result = TRUE;
		$charset = 'utf-8';
		if (
			isset($GLOBALS['TSFE']->renderCharset) &&
			$GLOBALS['TSFE']->renderCharset != ''
		) {
			$charset = $GLOBALS['TSFE']->renderCharset;
		}

		if (!is_array($toEMail) && trim($toEMail)) {
			$emailArray = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $toEMail);
			$toEMail = array();
			foreach ($emailArray as $email) {
				$toEMail[] = $email;
			}
		}

		if (is_array($toEMail) && count($toEMail)) {
			$emailArray = $toEMail;
			$errorEmailArray = array();
			foreach ($toEMail as $k => $v) {
				if (
					(
						!is_numeric($k) &&
						!\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($k)
					) &&
					(
						$v == '' ||
						!\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($v)
					)
				) {
					unset($emailArray[$k]);
					$errorEmailArray[$k] = $v;
				}
			}
			$toEMail = $emailArray;

			if (
				count($errorEmailArray)
			) {
				foreach ($errorEmailArray as $k => $v) {
					$email = $k;
					if (is_numeric($k)) {
						$email = $v;
					}

					debug ('tx_div2007_email::sendMail invalid email address: to "'. $email . '"'); // keep this
				}
			}

			if (
				!count($toEMail)
			) {
				debug ('tx_div2007_email::sendMail exited with error 1'); // keep this
				return FALSE;
			}
		} else {
				debug ('tx_div2007_email::sendMail exited with error 2'); // keep this
			return FALSE;
		}

		if (
			!\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($fromEMail)
		) {
			debug ('tx_div2007_email::sendMail invalid email address: from "' . $fromEMail . '"'); // keep this
			debug ('tx_div2007_email::sendMail exited with error 3'); // keep this
			return FALSE;
		}

		$fromName = str_replace('"', '\'', $fromName);
		$fromNameSlashed = tx_div2007_alpha5::slashName($fromName);

		if ($subject == '') {
			if ($defaultSubject == '') {
				$defaultSubject = 'message from ' . $fromNameSlashed . ($fromNameSlashed != '' ? '<' : '') . $fromEMail . ($fromNameSlashed != '' ? '>' : '');
			}

				// First line is subject
			if ($HTMLContent) {
				$parts = preg_split('/<title>|<\\/title>/i', $HTMLContent, 3);
				$subject = trim($parts[1]) ? strip_tags(trim($parts[1])) : $defaultSubject;
			} else {
					// First line is subject
				$parts = explode(chr(10), $PLAINContent, 2);
				$subject = trim($parts[0]) ? trim($parts[0]) : $defaultSubject;
				$PLAINContent = trim($parts[1]);
			}
		}

        $mail = tx_div2007_core::newMailMessage();
        $mail->setTo($toEMail)
            ->setFrom(array($fromEMail => $fromName))
            ->setReturnPath($returnPath)
            ->setSubject($subject)
            ->setBody($HTMLContent, 'text/html', $charset)
            ->addPart($PLAINContent, 'text/plain', $charset);

        if ($replyTo) {
            $mail->setReplyTo(array($replyTo => $fromEmail));
        }

        if (isset($attachment)) {
            if (is_array($attachment)) {
                $attachmentArray = $attachment;
            } else {
                $attachmentArray = array($attachment);
            }
            foreach ($attachmentArray as $theAttachment) {
                if (file_exists($theAttachment)) {
                    $mail->attach(Swift_Attachment::fromPath($theAttachment));
                }
            }
        }

            // HTML
        if (trim($HTMLContent)) {
            $HTMLContent = self::embedMedia($mail, $HTMLContent);
            $mail->setBody($HTMLContent, 'text/html', $charset);
        }

        if ($bcc != '') {
            $mail->addBcc($bcc);
        }

		if (
			isset($mail) &&
			is_object($mail) &&
			$extKey &&
			$hookVar &&
			isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]) &&
			is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]) &&
			isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey][$hookVar]) &&
			is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey][$hookVar])
		) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey][$hookVar] as $classRef) {
				$hookObj= \TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj($classRef);
				if (method_exists($hookObj, 'init')) {
					$hookObj->init($mail);
				}

				if (method_exists($hookObj, 'sendMail')) {
					$result = $hookObj->sendMail(
						$toEMail,
						$subject,
						$PLAINContent,
						$HTMLContent,
						$fromEMail,
						$fromName,
						$attachment,
						$cc,
						$bcc,
						$returnPath,
						$replyTo,
						$extKey,
						$hookVar,
						$result
					);

					if ($result === FALSE) {
						debug ('tx_div2007_email::sendMail exited with error 5'); // keep this
						break;
					}
				}
			}
		}

		if (
			$result !== FALSE &&
			isset($mail) &&
			is_object($mail)
		) {
			$mailClass = $mail::class;

			if (
				method_exists($mail, 'send') &&
				method_exists($mail, 'isSent')
			) {
				$mail->send();
				$result = $mail->isSent();
				if (!$result) {
					debug('tx_div2007_email::sendMail exited with error 6'); // keep this
					debug('tx_div2007_email::sendMail undelivered: ', implode(',', $mail->getFailedRecipients())); // keep this
				}
			} elseif (method_exists($mail, 'sendTheMail')) {
				$mail->sendTheMail();
			} else {
				$result = FALSE;
			}
		}

		return $result;
	}

	/**
	 * Embeds media into the mail message
	 *
	 * @param TYPO3\CMS\Core\Mail\MailMessage (TYPO3 4.x: t3lib_mail_Message) $mail: mail message
	 * @param string $htmlContent: the HTML content of the message
	 * @return string the subtituted HTML content
	 */
	static public function embedMedia ($mail, $htmlContent) {
		$substitutedHtmlContent = $htmlContent;
		$media = array();
		$attribRegex = self::makeTagRegex(array('img', 'embed', 'audio', 'video'));
			// Split the document by the beginning of the above tags
		$codepieces = preg_split($attribRegex, $htmlContent);
		$len = strlen($codepieces[0]);
		$pieces = count($codepieces);
		$reg = array();
		for ($i = 1; $i < $pieces; $i++) {
			$tag = strtolower(strtok(substr($htmlContent, $len + 1, 10), ' '));
			$len += strlen($tag) + strlen($codepieces[$i]) + 2;
			$dummy = preg_match('/[^>]*/', $codepieces[$i], $reg);
				// Fetches the attributes for the tag
			$attributes = self::getTagAttributes($reg[0]);
			if ($attributes['src'] != '' && $attributes['src'] != 'clear.gif') {
				$media[] = $attributes['src'];
			}
		}

		foreach ($media as $key => $source) {
			$substitutedHtmlContent = str_replace(
				'"' . $source . '"',
				'"' . $mail->embed(Swift_Image::fromPath($source)) . '"',
				$substitutedHtmlContent
			);
		}

		return $substitutedHtmlContent;
	}

	/**
	 * Creates a regular expression out of an array of tags
	 *
	 * @param	array		$tags: the array of tags
	 * @return	string		the regular expression
	 */
	static public function makeTagRegex (array $tags) {
		$regexpArray = array();
		foreach ($tags as $tag) {
			$regexpArray[] = '<' . $tag . '[[:space:]]';
		}
		return '/' . implode('|', $regexpArray) . '/i';
	}

	/**
	 * This function analyzes a HTML tag
	 * If an attribute is empty (like OPTION) the value of that key is just empty. Check it with is_set();
	 *
	 * @param string $tag: is either like this "<TAG OPTION ATTRIB=VALUE>" or this " OPTION ATTRIB=VALUE>" which means you can omit the tag-name
	 * @return array array with attributes as keys in lower-case
	 */
	static public function getTagAttributes ($tag) {
		$attributes = array();
		$tag = ltrim(preg_replace('/^<[^ ]*/', '', trim($tag)));
		$tagLen = strlen($tag);
		$safetyCounter = 100;
			// Find attribute
		while ($tag) {
			$value = '';
			$reg = preg_split('/[[:space:]=>]/', $tag, 2);
			$attrib = $reg[0];

			$tag = ltrim(substr($tag, strlen($attrib), $tagLen));
			if (substr($tag, 0, 1) == '=') {
				$tag = ltrim(substr($tag, 1, $tagLen));
				if (substr($tag, 0, 1) == '"') {
						// Quotes around the value
					$reg = explode('"', substr($tag, 1, $tagLen), 2);
					$tag = ltrim($reg[1]);
					$value = $reg[0];
				} else {
						// No quotes around value
					preg_match('/^([^[:space:]>]*)(.*)/', $tag, $reg);
					$value = trim($reg[1]);
					$tag = ltrim($reg[2]);
					if (substr($tag, 0, 1) == '>') {
						$tag = '';
					}
				}
			}
			$attributes[strtolower($attrib)] = $value;
			$safetyCounter--;
			if ($safetyCounter < 0) {
				break;
			}
		}
		return $attributes;
	}
}

?>
