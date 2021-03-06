<?php
/**
 * Shows statistics for all users.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Statistics.php 46 2011-07-30 14:38:11Z secretchampion $
 */

class Bengine_Page_Statistics extends Bengine_Page_Abstract
{
	/**
	 * Number of seconds which are allowed to pass for an assault to count as "recent".
	 *
	 * @var integer
	 */
	const RECENT_ASSAULT_TIME = 86400;

	/**
	 * Number of seconds which are allowed to pass for an useraction to count as "recent".
	 *
	 * @var integer
	 */
	const RECENT_ONLINE_TIME = 300;

	/**
	 * Holds unit numbers.
	 *
	 * @var array
	 */
	protected $unitCount = array();

	/**
	 * Constructor: loads language group.
	 *
	 * @return Bengine_Page_Statistics
	 */
	protected function init()
	{
		Core::getLang()->load(array("Statistics", "info"));
		return parent::init();
	}

	/**
	 * Index action.
	 *
	 * @return Bengine_Page_Statistics
	 */
	protected function indexAction()
	{
		Hook::event("StatisticsStart");

		$this->loadTotalUnits();
		$statistics = array(
			"totalMetal" => $this->fetchTotalMetal(),
			"totalSilicon" => $this->fetchTotalSilicon(),
			"totalHydrogen" => $this->fetchTotalHydrogen(),
			"totalPlayers" => fNumber($this->fetchTotalPlayers()),
			"totalPlanets" => fNumber($this->fetchTotalPlanets()),
			"totalMoons" => fNumber($this->fetchTotalPlanets(1)),
			"totalOnline" => fNumber($this->fetchOnlinePlayers()),
			"totalDebrisFields" => fNumber($this->fetchDebrisFields()),
			"totalRecentAssaults" => fNumber($this->fetchRecentAssaults()),
		);

		$totalRessources = $statistics["totalSilicon"] + $statistics["totalHydrogen"] + $statistics["totalMetal"];

		$statistics["percentMetal"] = fNumber(($statistics["totalMetal"] / $totalRessources) * 100, 2);
		$statistics["percentSilicon"] = fNumber(($statistics["totalSilicon"] / $totalRessources) * 100, 2);
		$statistics["percentHydrogen"] = fNumber(($statistics["totalHydrogen"] / $totalRessources) * 100, 2);

		$statistics["totalMetal"] = fNumber($statistics["totalMetal"] / 1000000, 2);
		$statistics["totalSilicon"] = fNumber($statistics["totalSilicon"] / 1000000, 2);
		$statistics["totalHydrogen"] = fNumber($statistics["totalHydrogen"] / 1000000, 2);

		Hook::event("StatisticsFinished", array(&$statistics));

		Core::getTPL()->assign($statistics);
		Core::getTPL()->addLoop("ships", $this->unitCount);
		return $this;
	}

	/**
	 * Calculates the total number of metal in the universe.
	 *
	 * @return integer
	 */
	protected function fetchTotalMetal()
	{
		$result = Core::getQuery()->select("planet", array("SUM(metal) AS totalMetal"));
		return Core::getDB()->fetch_field($result, "totalMetal");
	}

	/**
	 * Calculates the total number of silicon in the universe.
	 *
	 * @return integer
	 */
	protected function fetchTotalSilicon()
	{
		$result = Core::getQuery()->select("planet", array("SUM(silicon) AS totalSilicon"));
		return Core::getDB()->fetch_field($result, "totalSilicon");
	}

	/**
	 * Calculates the total number of hydrogen in the universe.
	 *
	 * @return integer
	 */
	protected function fetchTotalHydrogen()
	{
		$result = Core::getQuery()->select("planet", array("SUM(hydrogen) AS totalHydrogen"));
		return Core::getDB()->fetch_field($result, "totalHydrogen");
	}

	/**
	 * Calculates the total number of players in the universe.
	 *
	 * @return integer
	 */
	protected function fetchTotalPlayers()
	{
		$result = Core::getQuery()->select("user", array("COUNT(userid) AS numPlayers"));
		return Core::getDB()->fetch_field($result, "numPlayers");
	}

	/**
	 * Calculates the total number of planets in the universe.
	 *
	 * @param integer $isMoon [optional]
	 * @return integer
	 */
	protected function fetchTotalPlanets($isMoon = 0)
	{
		$result = Core::getQuery()->select("planet", array("COUNT(planetid) AS numPlanets"), "", "ismoon = '{$isMoon}'");
		return Core::getDB()->fetch_field($result, "numPlanets");
	}

	/**
	 * Loads all total units.
	 *
	 * @return Bengine_Page_Statistics
	 */
	protected function loadTotalUnits()
	{
		$select = new Recipe_Database_Select();
		$select->from(array("c" => "construction"));
		$select->attributes(array(
			"c" => array("name"),
			"s" => array("total" => new Recipe_Database_Expr("SUM(s.quantity)")),
		));
		$select->join(array("s" => "unit2shipyard"), array("s" => "unitid", "c" => "buildingid"))
			->group(array("c" => "buildingid"))
			->where(array("c" => "mode"), 3, "OR")
			->where(array("c" => "mode"), 4, "OR")
			->order(array("c" => "display_order"), "ASC")
			->order(array("c" => "buildingid"), "ASC");
		$result = $select->getResource();
		while($row = Core::getDB()->fetch($result))
		{
			$this->unitCount[$row["name"]] = $row["total"];
		}
		Core::getDatabase()->free_result($result);
		return $this;
	}

	/**
	 * Calculates the total number of online players in the universe.
	 *
	 * @return int
	 */
	protected function fetchOnlinePlayers()
	{
		$result = Core::getQuery()->select("user", array("COUNT(userid) AS numPlayers"), "", "last >= ".(TIME - self::RECENT_ONLINE_TIME)." AND last <= ".TIME);
		return Core::getDB()->fetch_field($result, "numPlayers");
	}

	/**
	 * Calculates the total number of recent assaults in the universe.
	 *
	 * @return integer
	 */
	protected function fetchRecentAssaults()
	{
		$result = Core::getQuery()->select("assault", array("COUNT(assaultid) AS numAssaults"), "", "time >= ".(TIME - self::RECENT_ASSAULT_TIME)." AND time <= ".TIME);
		return Core::getDB()->fetch_field($result, "numAssaults");
	}

	/**
	 * Calculates the total number of debris fields in the universe.
	 *
	 * @return integer
	 */
	protected function fetchDebrisFields()
	{
		$result = Core::getQuery()->select("galaxy", array("COUNT(planetid) AS numDebrisFields"), "", "metal > 0 OR silicon > 0");
		return Core::getDB()->fetch_field($result, "numDebrisFields");
	}
}
?>