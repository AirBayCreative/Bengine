<?php
/**
 * Alliance message folder.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Alliance.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_MessageFolder_Alliance extends Bengine_MessageFolder_Abstract
{
	/**
	 * Formats the message.
	 *
	 * @param Bengine_Model_Message	Message model
	 *
	 * @return Bengine_MessageFolder_Alliance
	 */
	protected function _format(Bengine_Model_Message $message)
	{
		$senderName = $message->get("username");
		$sender = (!empty($senderName)) ? Core::getLanguage()->getItem("ALLIANCE_GLOBAL_MAIL")." (".$senderName.")" : Core::getLanguage()->getItem("ALLIANCE");
		$message->set("sender", $sender);

		if(Core::getUser()->get("aid"))
		{
			$subject = "RE: ".preg_replace("#((RE|FW):\s)+#is", "\\1", $message->get("subject"));
			$subject = rawurlencode($subject);

			$linkUrl = "game.php/";
			if(URL_SESSION)
			{
				$linkUrl .= SID."/";
			}
			$linkUrl .= "Alliance/GlobalMail/".$subject;

			if(!empty($senderName))
			{
				$replyImg = Image::getImage("pm.gif", Core::getLanguage()->getItem("REPLY_ALLIANCE_MAIL"));
				$message->set("reply_link", Link::get($linkUrl, $replyImg));
			}
		}
		return $this;
	}
}