<?php
/**
 * Account activation function.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Activation.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Account_Activation
{
	/**
	 * Activation key to verify email address.
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * Constructor.
	 *
	 * @param string Activation key
	 *
	 * @return void
	 */
	public function __construct($key)
	{
		$this->key = $key;
		$this->activateAccount();
		return;
	}

	/**
	 * Returns the activation key.
	 *
	 * @return string
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * Activates an account, if key exists.
	 * Starts log in on success.
	 *
	 * @return Bengine_Account_Activation
	 */
	protected function activateAccount()
	{
		if(!empty($this->key))
		{
			$result = Core::getQuery()->select("user u", array("u.userid", "u.username", "p.password", "temp_email"), "LEFT JOIN ".PREFIX."password p ON (p.userid = u.userid)", "u.activation = '".$this->getKey()."'");
			if($row = Core::getDB()->fetch($result))
			{
				Core::getDB()->free_result($result);
				Hook::event("ActivateAccount", array($this));
				Core::getQuery()->update("user", array("activation", "email"), array("", $row["temp_email"]), "userid = '".$row["userid"]."'");
				$login = new Bengine_Login($row["username"], $row["password"], "game.php", "trim");
				$login->setCountLoginAttempts(false);
				$login->checkData();
				$login->startSession();
				return $this;
			}
			Core::getDB()->free_result($result);
		}
		Recipe_Header::redirect("?error=ACTIVATION_FAILED", false);
		return $this;
	}
}
?>