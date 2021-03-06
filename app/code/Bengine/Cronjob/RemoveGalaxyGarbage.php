<?php
/**
 * Removes destroyed planets.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: RemoveGalaxyGarbage.php 26 2011-06-17 13:09:26Z secretchampion $
 */

class Bengine_Cronjob_RemoveGalaxyGarbage extends Recipe_CronjobAbstract
{
	/**
	 * Removes galaxy garbage.
	 *
	 * @return Bengine_Cronjob_RemoveGalaxyGarbage
	 */
	protected function removeGalaxyGarbage()
	{
		$result = Core::getQuery()->select("galaxy g", array("g.planetid", "e.eventid"), "LEFT JOIN ".PREFIX."events e ON (e.destination = g.planetid)", "g.destroyed = '1'");
		while($row = Core::getDB()->fetch($result))
		{
			if(!$row["eventid"])
			{
				$id = $row["planetid"];
				Core::getQuery()->delete("planet", "planetid = '".$id."'");
			}
		}
		Core::getDB()->free_result($result);
		return $this;
	}

	/**
	 * Executes this cronjob.
	 *
	 * @return Bengine_Cronjob_RemoveGalaxyGarbage
	 */
	protected function _execute()
	{
		$this->removeGalaxyGarbage();
		return $this;
	}
}
?>