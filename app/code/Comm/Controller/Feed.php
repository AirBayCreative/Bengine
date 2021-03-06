<?php
/**
 * Feed controller
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Feed.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Comm_Controller_Feed extends Comm_Controller_Abstract
{
	/**
	 * Max items per feed.
	 *
	 * @var integer
	 */
	const MAX_FEED_ITEMS = 10;

	/**
	 * Folder id of the outbox.
	 *
	 * @var integer
	 */
	const OUTBOX_FOLDER_ID = 2;

	/**
	 * Folder id of combat reports.
	 *
	 * @var integer
	 */
	const COMBATS_REPORT_FOLDER_ID = 5;

	/**
	 * Userid for the feed.
	 *
	 * @var integer
	 */
	protected $user_id = 0;

	/**
	 * Index action.
	 *
	 * @return Comm_Controller_Feed
	 */
	public function index()
	{
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see app/code/Comm/Controller/Comm_Controller_Abstract#init()
	 */
	protected function init()
	{
		Core::getLanguage()->load(array("Prefs", "Message"));
		$this->setIsAjax();
	}

	/**
	 * Checks if the given key is ok for the userid.
	 *
	 * @param integer	User id
	 * @param string	Feed key
	 *
	 * @return boolean
	 */
	public function checkKey($user_id, $key)
	{
		$result = Core::getQuery()->select("feed_keys", array("feed_key"), "", "user_id = '".$user_id."' AND feed_key = '".$key."'");

		return (Core::getDB()->num_rows($result) > 0);
	}

	/**
	 * Creates and shows an rss feed.
	 *
	 * @return Comm_Controller_Feed
	 */
	public function rssAction()
	{
		$this->title = Core::getLang()->get("FEED");
		$this->langcode = Core::getLang()->getOpt("langcode");
		$this->showFeed($this->getParam("action"));
		return $this;
	}

	/**
	 * Creates and shows an atom feed.
	 *
	 * @return Comm_Controller_Feed
	 */
	public function atomAction()
	{
		$this->showFeed($this->getParam("action"));
		return $this;
	}

	/**
	 * Shows the feeds.
	 *
	 * @param string	Feed type
	 *
	 * @return Comm_Controller_Feed
	 */
	protected function showFeed($type)
	{
		$this->user_id = $this->getParam("1");
		$key = $this->getParam("2");

		if(!$this->checkKey($this->user_id, $key))
		{
			throw new Recipe_Exception_Generic("You have followed an invalid feed link.");
		}

		$this->selfUrl = BASE_URL."feed/".$type;
		$this->alternateUrl = BASE_URL."feed";
		$this->title = Core::getLang()->get("FEED");
		Core::getTPL()->addLoop("feed", $this->getItems(0, self::MAX_FEED_ITEMS));
		$this->setTemplate($type);
		return $this;
	}

	/**
	 * Returns the last messages for the user.
	 *
	 * @param integer	Offset
	 * @param integer	Count
	 *
	 * @return array
	 */
	protected function getItems($offset, $count)
	{
		$folderClassCache = array();
		$items = array();
		$messages = Bengine::getCollection("message");
		$messages->addTimeOrder()
			->addReceiverFilter($this->user_id)
			->addFolderJoin();
		$select = $messages->getSelect();
		$select->limit($offset, $count);
		$select->where("m.mode != ?", self::OUTBOX_FOLDER_ID);
		foreach($messages as $message)
		{
			if(!isset($folderClassCache[$message->get("folder_class")]))
			{
				$folderClass = explode("/", $message->get("folder_class"));
				$folderClass[0] = ucwords($folderClass[0]);
				$folderClass[1] = ucwords($folderClass[1]);
				$folderClass = $folderClass[0]."_MessageFolder_".$folderClass[1];
				$folderClass = new $folderClass();
				$folderClassCache[$message->get("folder_class")] = $folderClass;
			}
			else
			{
				$folderClass = $folderClassCache[$message->get("folder_class")];
			}
			$folderClass->formatMessage($message, true);

			$items[] = array(
				"date"	=> Date::timeToString(3, $message->getTime(), "D, d M Y H:i:s O", false),
				"author" => ($message->getUsername()) ? $message->getUsername() : "System",
				"title" => strip_tags($message->get("subject")),
				"text" => $message->get("message"),
				"link" => $message->get("link"),
				"date_atom" => Date::timeToString(3, $message->getTime(), "c", false),
				"guid" => $message->getId(),
			);
		}
		return $items;
	}
}
?>