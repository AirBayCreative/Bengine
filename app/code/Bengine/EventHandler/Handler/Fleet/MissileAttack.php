<?php
/**
 * Handler for missile attacks.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: MissileAttack.php 8 2010-10-17 20:55:04Z secretchampion $
 */

class Bengine_EventHandler_Handler_Fleet_MissileAttack extends Bengine_EventHandler_Handler_Fleet_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_EventHandler_Handler_Abstract#_execute($event, $data)
	 */
	protected function _execute(Bengine_Model_Event $event, array $data)
	{
		Core::getLanguage()->load(array("info", "AutoMessages"));
		Hook::event("EhMissileAttackStart", array($event, &$data, $this));
		$attackingRockets = $data["rockets"];
		$primaryTarget = $data["primary_target"];
		$attack = $data["attack"];
		$def = array();
		$destroyed = array();

		// Load shelltech for defender
		$_result = Core::getQuery()->select("research2user", "level", "", "userid = '".$event["destination_user_id"]."' AND buildingid = '17'");
		$_row = Core::getDB()->fetch($_result);
		Core::getDB()->free_result($_result);
		$shell = (int) $_row["level"];

		// Load defending units
		$_result = Core::getQuery()->select("unit2shipyard u2s", array("u2s.unitid", "u2s.quantity", "b.name", "basic_metal", "basic_silicon", "basic_hydrogen"), "LEFT JOIN ".PREFIX."construction b ON (b.buildingid = u2s.unitid)", "b.mode = '4' AND u2s.planetid = '".$event["destination"]."'", "b.display_order ASC, b.buildingid ASC");
		while($_row = Core::getDB()->fetch($_result))
		{
			$def[$_row["unitid"]] = $_row;
			$def[$_row["unitid"]]["name"] = Core::getLanguage()->getItem($_row["name"]);
			$def[$_row["unitid"]]["shell"] = ($_row["basic_metal"] + $_row["basic_silicon"]) / 10;
			$def[$_row["unitid"]]["shell"] = floor($def[$_row["unitid"]]["shell"] * (1 + $shell / 10));
		}
		Core::getDB()->free_result($_result);

		// Load guntech for attacker
		$_result = Core::getQuery()->select("research2user", "level", "", "userid = '".$event["userid"]."' AND buildingid = '15'");
		$_row = Core::getDB()->fetch($_result);
		Core::getDB()->free_result($_result);
		$gun = (int) $_row["level"];

		Hook::event("EhMissileAttackLoaded", array($event, &$_row, &$data, &$def, &$attack, &$shell, &$gun, &$primaryTarget));

		if(!array_key_exists($primaryTarget, $def)) { $primaryTarget = 0; }

		// Start attack
		if(count($def) > 0)
		{
			$pointsLost = 0;
			if(isset($def[51]))
			{
				$attackingRockets -= $def[51]["quantity"];
				$destroyed[51] = ($attackingRockets > 0) ? $def[51]["quantity"] : $data["rockets"];
				$pointsLost += $destroyed[51] * $def[51]["basic_metal"];
				$pointsLost += $destroyed[51] * $def[51]["basic_silicon"];
				$pointsLost += $destroyed[51] * $def[51]["basic_hydrogen"];
				if($def[51]["quantity"] - $destroyed[51] <= 0)
				{
					Core::getQuery()->delete("unit2shipyard", "unitid = '51' AND planetid = '".$event["destination"]."'");
				}
				else
				{
					Core::getDB()->query("UPDATE ".PREFIX."unit2shipyard SET quantity = quantity - '".$destroyed[51]."' WHERE unitid = '51' AND planetid = '".$event["destination"]."'");
				}
			}

			if($attackingRockets > 0)
			{
				$damage = floor($attackingRockets * ($attack * (1 + $gun / 10)));
				if($primaryTarget > 0)
				{
					if(!isset($destroyed[$primaryTarget]))
					{
						$destroyed[$primaryTarget] = 0;
					}
					if($damage > $def[$primaryTarget]["shell"] * $def[$primaryTarget]["quantity"])
					{
						$destroyed[$primaryTarget] += $def[$primaryTarget]["quantity"];
						$damage -= $def[$primaryTarget]["shell"] * $destroyed[$primaryTarget];
					}
					else
					{
						$destroyed[$primaryTarget] += floor($damage / $def[$primaryTarget]["shell"]);
						$damage -= $destroyed[$primaryTarget] * $def[$primaryTarget]["shell"];
					}

					if($def[$primaryTarget]["quantity"] - $destroyed[$primaryTarget] <= 0)
					{
						Core::getQuery()->delete("unit2shipyard", "unitid = '".$primaryTarget."' AND planetid = '".$event["destination"]."'");
					}
					else
					{
						Core::getDB()->query("UPDATE ".PREFIX."unit2shipyard SET quantity = quantity - '".$destroyed[$primaryTarget]."' WHERE unitid = '".$primaryTarget."' AND planetid = '".$event["destination"]."'");
					}

					$pointsLost += $destroyed[$primaryTarget] * $def[$primaryTarget]["basic_metal"];
					$pointsLost += $destroyed[$primaryTarget] * $def[$primaryTarget]["basic_silicon"];
					$pointsLost += $destroyed[$primaryTarget] * $def[$primaryTarget]["basic_hydrogen"];
				}

				foreach($def as $key => $value)
				{
					if(!isset($destroyed[$key]))
					{
						$destroyed[$key] = 0;
					}
					if($key == 51 || $key == $primaryTarget || $key == 52) { continue; }
					if($damage > $value["shell"] * $value["quantity"])
					{
						$destroyed[$key] += $value["quantity"];
						$damage -= $value["shell"] * $destroyed[$key];
					}
					else
					{
						$destroyed[$key] += floor($damage / $value["shell"]);
						$damage -= $destroyed[$key] * $value["shell"];
					}

					if($value["quantity"] - $destroyed[$key] <= 0)
					{
						Core::getQuery()->delete("unit2shipyard", "unitid = '".$key."' AND planetid = '".$event["destination"]."'");
					}
					else
					{
						Core::getDB()->query("UPDATE ".PREFIX."unit2shipyard SET quantity = quantity - '".$destroyed[$key]."' WHERE unitid = '".$key."' AND planetid = '".$event["destination"]."'");
					}

					$pointsLost += $destroyed[$key] * $value["basic_metal"];
					$pointsLost += $destroyed[$key] * $value["basic_silicon"];
					$pointsLost += $destroyed[$key] * $value["basic_hydrogen"];
				}
			}
		}

		// Generate report
		Hook::event("EhMissileAttackGenerateReport", array($event, &$_row, &$data, &$pointsLost, &$destroyed, &$def));
		Core::getLanguage()->assign("planet", $data["planetname"]);
		Core::getLanguage()->assign("coords", getCoordLink($data["galaxy"], $data["system"], $data["position"], true));
		$report  = "<table class=\"ntable\" style=\"width: 400px;\">";
		$report .= "<tr><th colspan=\"4\">".Core::getLanguage()->getItem("ROCKET_ATTACK_REPORT_HEADLINE")."</th></tr>";
		$i = 0;
		foreach($def as $key => $def)
		{
			if($i % 2 == 0) { $report .= "<tr>"; }
			if(!isset($destroyed[$key]))
			{
				$destroyed[$key] = 0;
			}
			$quantity = $def["quantity"] - $destroyed[$key];
			$dest = ($destroyed[$key] > 0) ? " (-".$destroyed[$key].")" : "";
			$report .= "<td>".$def["name"]."</td><td>".$quantity.$dest."</td>";
			if(count($def) == $i + 1 && $i % 2 == 0)
			{
				$report .= "<td></td><td></td></tr>";
			}
			if($i % 2 == 1) { $report .= "</tr>"; }
			$i++;
		}
		$report .= "</table>";

		Core::getLanguage()->assign("rockets", $data["rockets"]);
		Core::getLanguage()->assign("attacker", $event["username"]);
		Core::getLanguage()->assign("defender", $event["destination_username"]);
		Core::getLanguage()->assign("lostunits", fNumber($pointsLost));

		// Send report
		$message = Core::getLanguage()->getItem("ROCKET_ATTACK_MSG_ATTACKER").$report;
		Hook::event("EhMissileAttackReportGeneratedAttacker", array($event, &$_row, &$data, &$message));
		Core::getQuery()->insertInto("message", array("mode" => 3, "time" => TIME, "sender" => null, "receiver" => $event["userid"], "message" => $message, "subject" => Core::getLanguage()->getItem("ROCKET_ATTACK_SUBJECT"), "read" => 0));
		$message = Core::getLanguage()->getItem("ROCKET_ATTACK_MSG_DEFENDER").$report;
		Hook::event("EhMissileAttactReportGeneratedDefender", array($event, &$_row, &$data, &$message));
		Core::getQuery()->insertInto("message", array("mode" => 3, "time" => TIME, "sender" => null, "receiver" => $event["destination_user_id"], "message" => $message, "subject" => Core::getLanguage()->getItem("ROCKET_ATTACK_SUBJECT"), "read" => 0));

		// Update points for defender
		$pointsLost /= 1000;
		Core::getDB()->query("UPDATE ".PREFIX."user SET points = points - '".$pointsLost."' WHERE userid = '".$event["destination_user_id"]."'");

		// Update points for attacker
		$points = $data["rockets"] * $data["basic_metal"] + $data["rockets"] * $data["basic_silicon"] + $data["rockets"] * $data["basic_hydrogen"];
		$points /= 1000;
		Core::getDB()->query("UPDATE ".PREFIX."user SET points = points - '".$points."' WHERE userid = '".$event["userid"]."'");
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_EventHandler_Handler_Abstract#_add($event, $data)
	 */
	protected function _add(Bengine_Model_Event $event, array $data)
	{
		Core::getDB()->query("UPDATE ".PREFIX."unit2shipyard SET quantity = quantity - '".$data["rockets"]."' WHERE unitid = '52' AND planetid = '".$event->getPlanetid()."'");
		Core::getQuery()->delete("unit2shipyard", "quantity = '0'");
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see app/code/Bengine/EventHandler/Handler/Bengine_EventHandler_Handler_Abstract#_remove($event, $data)
	 */
	protected function _remove(Bengine_Model_Event $event, array $data)
	{
		return false;
	}

	/**
	 * Checks the event for validation.
	 *
	 * @return boolean
	 */
	protected function _isValid()
	{

		return false;
	}
}
?>