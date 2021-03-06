<?php
/**
 * Shows galaxy
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Galaxy.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Page_Galaxy extends Bengine_Page_Abstract
{
	/**
	 * Current viewing galaxy.
	 *
	 * @var integer
	 */
	protected $galaxy = 0;

	/**
	 * Current viewing system.
	 *
	 * @var integer
	 */
	protected $system = 0;

	/**
	 * Missile range.
	 *
	 * @var integer
	 */
	protected $missileRange = 0;

	/**
	 * Main method to display a sun system.
	 *
	 * @return Bengine_Page_Galaxy
	 */
	protected function init()
	{
		Core::getLanguage()->load(array("Galaxy", "Statistics"));
		$this->missileRange = Bengine::getRocketRange();
		if(Core::getRequest()->getGET("1") && Core::getRequest()->getGET("2"))
		{
			$this->setCoordinatesByGet(Core::getRequest()->getGET("1"), Core::getRequest()->getGET("2"));
		}
		return parent::init();
	}

	/**
	 * Sets the coordinates by POST.
	 *
	 * @param integer	Galaxy
	 * @param integer	System
	 * @param string	Submit type
	 *
	 * @return Bengine_Page_Galaxy
	 */
	protected function setCoordinatesByPost($galaxy, $system, $submitType)
	{
		$this->galaxy = $galaxy;
		$this->system = $system;
		switch($submitType)
		{
			case "prevgalaxy":
				$this->galaxy--;
			break;
			case "nextgalaxy":
				$this->galaxy++;
			break;
			case "prevsystem":
				$this->system--;
			break;
			case "nextsystem":
				$this->system++;
			break;
		}
		return $this;
	}

	/**
	 * Validates the inputted galaxy and system.
	 *
	 * @return Bengine_Page_Galaxy
	 */
	protected function validateInputs()
	{
		if(empty($this->galaxy))
		{
			$this->galaxy = Bengine::getPlanet()->getData("galaxy");
		}
		if(empty($this->system))
		{
			$this->system = Bengine::getPlanet()->getData("system");
		}

		if($this->galaxy < 1) { $this->galaxy = 1; }
		else if($this->galaxy > Core::getOptions()->get("GALAXYS")) { $this->galaxy = Core::getOptions()->get("GALAXYS"); }
		if($this->system < 1) { $this->system = 1; }
		else if($this->system > Core::getOptions()->get("SYSTEMS")) { $this->system = Core::getOptions()->get("SYSTEMS"); }
		return $this;
	}

	/**
	 * Sets the coordinates by GET.
	 *
	 * @param integer	Galaxy
	 * @param integer	System
	 *
	 * @return Bengine_Page_Galaxy
	 */
	protected function setCoordinatesByGet($galaxy, $system)
	{
		if($galaxy && $system)
		{
			$this->galaxy = $galaxy;
			$this->system = $system;
		}
		return $this;
	}

	/**
	 * Substracts hydrogen for each galaxy view.
	 *
	 * @return Bengine_Page_Galaxy
	 */
	protected function subtractHydrogen()
	{
		if($this->galaxy != Bengine::getPlanet()->getData("galaxy") || $this->system != Bengine::getPlanet()->getData("system"))
		{
			if(Bengine::getPlanet()->getData("hydrogen") - 10 < 0)
			{
				Logger::dieMessage("DEFICIENT_CONSUMPTION");
			}
			Bengine::getPlanet()->setData("hydrogen", Bengine::getPlanet()->getData("hydrogen") - 10);
			Core::getDB()->query("UPDATE ".PREFIX."planet SET hydrogen = hydrogen - '10' WHERE planetid = '".Core::getUser()->get("curplanet")."'");
		}
		return $this;
	}

	/**
	 * Index action.
	 *
	 * @return Bengine_Page_Galaxy
	 */
	protected function indexAction()
	{
		if($this->isPost())
		{
			$this->setCoordinatesByPost($this->getParam("galaxy"), $this->getParam("system"), $this->getParam("submittype"));
		}
		$this->validateInputs()->subtractHydrogen();

		// Star surveillance
		$canMonitorActivity = false;
		if(Bengine::getPlanet()->getBuilding("STAR_SURVEILLANCE") > 0)
		{
			$range = pow(Bengine::getPlanet()->getBuilding("STAR_SURVEILLANCE"), 2) - 1;
			$diff = abs(Bengine::getPlanet()->getData("system") - $this->system);
			if($this->galaxy == Bengine::getPlanet()->getData("galaxy") && $range >= $diff)
			{
				$canMonitorActivity = true;
			}
		}
		Core::getTPL()->assign("canMonitorActivity", $canMonitorActivity);

		// Images
		$rockimg = Image::getImage("rocket.gif", Core::getLanguage()->getItem("ROCKET_ATTACK"));

		// Get sunsystem data
		$select = array(
			"g.planetid", "g.position", "g.destroyed", "g.metal", "g.silicon", "g.moonid",
			"p.picture", "p.planetname", "p.last as planetactivity",
			"u.username", "u.usertitle", "u.userid", "u.points", "u.last as useractivity", "u.umode",
			"m.planetid AS moon", "m.picture AS moonpic", "m.planetname AS moonname", "m.diameter AS moonsize", "m.temperature", "m.last as moonactivity",
			"a.tag", "a.name", "a.showmember", "a.homepage", "a.showhomepage",
			"u2a.aid", "b.to"
		);
		$joins  = "LEFT JOIN ".PREFIX."planet p ON (p.planetid = g.planetid)";
		$joins .= "LEFT JOIN ".PREFIX."user u ON (u.userid = p.userid)";
		$joins .= "LEFT JOIN ".PREFIX."planet m ON (m.planetid = g.moonid)";
		$joins .= "LEFT JOIN ".PREFIX."user2ally u2a ON (u2a.userid = u.userid)";
		$joins .= "LEFT JOIN ".PREFIX."alliance a ON (a.aid = u2a.aid)";
		$joins .= "LEFT JOIN ".PREFIX."ban_u b ON (b.userid = u.userid AND b.to > '".TIME."')";
		$result = Core::getQuery()->select("galaxy g", $select, $joins, "g.galaxy = '".$this->galaxy."' AND g.system = '".$this->system."'");
		$systemData = array();
		$UserList = new Bengine_User_List();
		$UserList->setKey("position");
		$UserList->setNewbieProtection(true);
		$UserList->setFetchRank(true);
		$UserList->setTagAsLink(false);
		$UserList->load($result);
		$sys = $UserList->getArray();
		Hook::event("SolarSystemDataBefore", array($this, &$sys));
		for($i = 1; $i <= 15; $i++)
		{
			if(isset($sys[$i]) && !$sys[$i]["destroyed"])
			{
				$sys[$i]["systempos"] = $i;
				if($sys[$i]["tag"] != "")
				{
					$sys[$i]["allydesc"] = sprintf(Core::getLanguage()->getItem("GALAXY_ALLY_HEADLINE"), $sys[$i]["tag"], $sys[$i]["alliance_rank"]);
				}

				$sys[$i]["metal"] = fNumber($sys[$i]["metal"]);
				$sys[$i]["silicon"] = fNumber($sys[$i]["silicon"]);
				$sys[$i]["picture"] = Image::getImage("planets/small/s_".$sys[$i]["picture"].Core::getConfig()->get("PLANET_IMG_EXT"), $sys[$i]["planetname"], 30, 30);
				$sys[$i]["picture"] = Link::get("game.php/".SID."/Mission/Index/".$this->galaxy."/".$this->system."/".$i, $sys[$i]["picture"]);
				$sys[$i]["planetname"] = Link::get("game.php/".SID."/Mission/Index/".$this->galaxy."/".$this->system."/".$i, $sys[$i]["planetname"]);
				$sys[$i]["moonpicture"] = ($sys[$i]["moonpic"] != "") ? Image::getImage("planets/small/s_".$sys[$i]["moonpic"].Core::getConfig()->get("PLANET_IMG_EXT"), $sys[$i]["moonname"], 22, 22) : "";
				$sys[$i]["moon"] = sprintf(Core::getLanguage()->getItem("MOON_DESC"), $sys[$i]["moonname"]);
				$sys[$i]["moonsize"] = fNumber($sys[$i]["moonsize"]);
				$sys[$i]["moontemp"] = fNumber($sys[$i]["temperature"]);

				if($sys[$i]["moonactivity"] > $sys[$i]["planetactivity"])
				{
					$activity = $sys[$i]["moonactivity"];
				}
				else
				{
					$activity = $sys[$i]["planetactivity"];
				}
				if($activity > TIME - 900 && $sys[$i]["userid"] != Core::getUser()->get("userid")) { $sys[$i]["activity"] = "(*)"; }
				else if($activity > TIME - 3600 && $sys[$i]["userid"] != Core::getUser()->get("userid")) { $sys[$i]["activity"] = "(".floor((TIME - $activity) / 60)." min)"; }
				else { $sys[$i]["activity"] = ""; }

				if(Bengine::getPlanet()->getBuilding("ROCKET_STATION") > 3 && $sys[$i]["userid"] != Core::getUser()->get("userid") && $this->inMissileRange())
				{
					$sys[$i]["rocketattack"] = Link::get("game.php/".SID."/RocketAttack/Index/".$sys[$i]["planetid"], $rockimg);
					$sys[$i]["moonrocket"] = "<tr><td colspan=&quot;3&quot;>".Str::replace("\"", "", Link::get("game.php/".SID."/RocketAttack/Index/".$sys[$i]["moon"]."/1", Core::getLanguage()->getItem("ROCKET_ATTACK")))."</td></tr>";
				}
				else
				{
					$sys[$i]["rocketattack"] = "";
					$sys[$i]["moonrocket"] = "";
				}

				$sys[$i]["allypage"] = Str::replace("\"", "", Link::get("game.php/".SID."/Alliance/Page/".$sys[$i]["aid"], Core::getLanguage()->getItem("ALLIANCE_PAGE"), 1));
				if(($sys[$i]["showhomepage"] || $sys[$i]["aid"] == Core::getUser()->get("aid")) && $sys[$i]["homepage"] != "")
				{
					$sys[$i]["homepage"] = "<tr><td>".Str::replace("'", "\'", Link::get($sys[$i]["homepage"], Core::getLanguage()->getItem("HOMEPAGE"), 2))."</td></tr>";
				}
				else
				{
					$sys[$i]["homepage"] = "";
				}
				if($sys[$i]["showmember"])
				{
					$sys[$i]["memberlist"] = "<tr><td>".Str::replace("\"", "", Link::get("game.php/".SID."/Alliance/Memberlist/".$sys[$i]["aid"], Core::getLanguage()->getItem("SHOW_MEMBERLIST"), 3))."</td></tr>";
				}
				$sys[$i]["debris"] = Image::getImage("debris.jpg", "", 25, 25);
			}
			else
			{
				if(!isset($sys[$i]["destroyed"])) { $sys[$i] = array(); $sys[$i]["destroyed"] = false; }
				$sys[$i]["systempos"] = $i;
				$sys[$i]["userid"] = null;
				$sys[$i]["metal"] = ($sys[$i]["destroyed"]) ? fNumber($sys[$i]["metal"]) : "";
				$sys[$i]["silicon"] = ($sys[$i]["destroyed"]) ? fNumber($sys[$i]["silicon"]) : "";
				$sys[$i]["picture"] = ($sys[$i]["destroyed"]) ? Image::getImage("planets/small/s_".$sys[$i]["picture"].Core::getConfig()->get("PLANET_IMG_EXT"), $sys[$i]["planetname"], 30, 30) : "";
				$sys[$i]["planetname"] = ($sys[$i]["destroyed"]) ? Core::getLanguage()->getItem("DESTROYED_PLANET") : "";
				$sys[$i]["moon"] = "";
				$sys[$i]["moonid"] = "";
				$sys[$i]["username"] = "";
				$sys[$i]["alliance"] = "";
				$sys[$i]["activity"] = "";
				$sys[$i]["moonpicture"] = "";
				$sys[$i]["user_status_long"] = "";
			}
		}
		ksort($sys);
		Hook::event("SolarSystemDataAfter", array($this, &$sys));
		Core::getTPL()->assign("sendesp", Image::getImage("esp.gif", Core::getLanguage()->getItem("SEND_ESPIONAGE_PROBE")));
		Core::getTPL()->assign("monitorfleet", Image::getImage("binocular.gif", Core::getLanguage()->getItem("MONITOR_FLEET_ACTIVITY")));
		Core::getTPL()->assign("moon", Str::replace("\"", "", Image::getImage("planets/mond".Core::getConfig()->get("PLANET_IMG_EXT"), Core::getLanguage()->getItem("MOON"), 75, 75)));
		Core::getTPL()->addLoop("sunsystem", $sys);
		Core::getTPL()->assign("debris", Str::replace("\"", "", Image::getImage("debris.jpg", Core::getLanguage()->getItem("DEBRIS"), 75, 75)));
		Core::getTPL()->assign("galaxy", $this->galaxy);
		Core::getTPL()->assign("system", $this->system);
		return $this;
	}

	/**
	 * Checks if the current solar system is in missile range.
	 *
	 * @return boolean
	 */
	protected function inMissileRange()
	{
		if($this->galaxy != Bengine::getPlanet()->getData("galaxy"))
		{
			return false;
		}
		if(abs(Bengine::getPlanet()->getData("system") - $this->system) <= $this->missileRange)
		{
			return true;
		}
		return false;
	}
}
?>