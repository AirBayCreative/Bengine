<?php
/**
 * Administrator interface to modify construction data.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Edit.php 19 2011-05-27 10:30:33Z secretchampion $
 */

class Bengine_Page_Construction_Edit extends Bengine_Page_Abstract
{
	/**
	 * List of resources.
	 *
	 * @var array
	 */
	protected $resources = array(
		"metal"		=> "METAL",
		"silicon"	=> "SILICON",
		"hydrogen"	=> "HYDROGEN",
		"energy"	=> "ENERGY"
	);

	/**
	 * Shows edit form.
	 *
	 * @return Bengine_Page_Construction_Edit
	 */
	protected function init()
	{
		Core::getUser()->checkPermissions("CAN_EDIT_CONSTRUCTIONS");
		Core::getLanguage()->load("Administrator");
		return parent::init();
	}

	/**
	 * Index action.
	 *
	 * @param integer $id	Construction ID
	 *
	 * @return Bengine_Page_Construction_Edit
	 */
	protected function indexAction($id)
	{
		if($this->isPost())
		{
			if($this->getParam("addreq"))
			{
				$this->addRequirement($id, $this->getParam("level"), $this->getParam("needs"));
			}
			if($this->getParam("saveconstruction"))
			{
				$this->saveConstruction(
					$this->getParam("name"), $this->getParam("name_id"), $this->getParam("allow_on_moon"), $this->getParam("desc"), $this->getParam("full_desc"),
					$this->getParam("prod_what"), $this->getParam("prod"), $this->getParam("cons_what"), $this->getParam("consumption"), $this->getParam("special"),
					$this->getParam("basic_metal"), $this->getParam("basic_silicon"), $this->getParam("basic_hydrogen"), $this->getParam("basic_energy"),
					$this->getParam("charge_metal"), $this->getParam("charge_silicon"), $this->getParam("charge_hydrogen"), $this->getParam("charge_energy")
				);
			}
		}
		$select = array(
			"c.name AS name_id", "p.content AS name", "c.special", "c.allow_on_moon",
			"c.basic_metal", "c.basic_silicon", "c.basic_hydrogen", "c.basic_energy",
			"c.prod_metal", "c.prod_silicon", "c.prod_hydrogen", "c.prod_energy",
			"c.cons_metal", "c.cons_silicon", "c.cons_hydrogen", "c.cons_energy",
			"c.charge_metal", "c.charge_silicon", "c.charge_hydrogen", "c.charge_energy"
		);
		$joins  = "LEFT JOIN ".PREFIX."phrases p ON (p.title = c.name)";
		$result = Core::getQuery()->select("construction c", $select, $joins, "c.buildingid = '".$id."' AND p.languageid = '".Core::getLanguage()->getOpt("languageid")."'");
		if($row = Core::getDB()->fetch($result))
		{
			Core::getDB()->free_result($result);
			Hook::event("EditUnitDataLoaded", array(&$row));

			// Set production
			$prodWhat = "";
			if(!empty($row["prod_metal"]))
			{
				$row["prod"] = $row["prod_metal"];
				$prodWhat = "metal";
			}
			else if(!empty($row["prod_silicon"]))
			{
				$row["prod"] = $row["prod_silicon"];
				$prodWhat = "silicon";
			}
			else if(!empty($row["prod_hydrogen"]))
			{
				$row["prod"] = $row["prod_hydrogen"];
				$prodWhat = "hydrogen";
			}
			else if(!empty($row["prod_energy"]))
			{
				$row["prod"] = $row["prod_energy"];
				$prodWhat = "energy";
			}

			// Set Consumption
			$consWhat = "";
			if(!empty($row["cons_metal"]))
			{
				$row["consumption"] = $row["cons_metal"];
				$consWhat = "metal";
			}
			else if(!empty($row["cons_silicon"]))
			{
				$row["consumption"] = $row["cons_silicon"];
				$consWhat = "silicon";
			}
			else if(!empty($row["cons_hydrogen"]))
			{
				$row["consumption"] = $row["cons_hydrogen"];
				$consWhat = "hydrogen";
			}
			else if(!empty($row["cons_energy"]))
			{
				$row["consumption"] = $row["cons_energy"];
				$consWhat = "energy";
			}

			Core::getTPL()->assign("prodWhat", $this->getResourceSelect($prodWhat));
			Core::getTPL()->assign("consWhat", $this->getResourceSelect($consWhat));

			Core::getTPL()->assign($row);

			$result = Core::getQuery()->select("phrases", "content", "", "languageid = '".Core::getLanguage()->getOpt("languageid")."' AND title = '".$row["name_id"]."_DESC'");
			$_row = Core::getDB()->fetch($result);
			Core::getDB()->free_result($result);
			Core::getTPL()->assign("description", Str::replace("<br />", "", $_row["content"]));
			$result = Core::getQuery()->select("phrases", "content", "", "languageid = '".Core::getLanguage()->getOpt("languageid")."' AND title = '".$row["name_id"]."_FULL_DESC'");
			$_row = Core::getDB()->fetch($result);
			Core::getDB()->free_result($result);
			Core::getTPL()->assign("full_description", Str::replace("<br />", "", $_row["content"]));

			$req = array(); $i = 0;
			$result = Core::getQuery()->select("requirements r", array("r.requirementid", "r.needs", "r.level", "p.content"), "LEFT JOIN ".PREFIX."construction b ON (b.buildingid = r.needs) LEFT JOIN ".PREFIX."phrases p ON (p.title = b.name)", "r.buildingid = '".$id."' AND p.languageid = '".Core::getLanguage()->getOpt("languageid")."'");
			while($row = Core::getDB()->fetch($result))
			{
				$req[$i]["delete"] = Link::get("game.php/sid:".SID."/Construction_Edit/DeleteRequirement/".$row["requirementid"]."/".$id, "[".Core::getLanguage()->getItem("DELETE")."]");
				$req[$i]["name"] = Link::get("game.php/".SID."/Construction_Edit/Index/".$row["needs"], $row["content"]);
				$req[$i]["level"] = $row["level"];
				$i++;
			}
			Core::getTPL()->addLoop("requirements", $req);

			$const = array(); $i = 0;
			$result = Core::getQuery()->select("construction b", array("b.buildingid", "p.content"), "LEFT JOIN ".PREFIX."phrases p ON (p.title = b.name)", "(b.mode = '1' OR b.mode = '2' OR b.mode = '5') AND p.languageid = '".Core::getLanguage()->getOpt("languageid")."'", "p.content ASC");
			while($row = Core::getDB()->fetch($result))
			{
				$const[$i]["name"] = $row["content"];
				$const[$i]["id"] = $row["buildingid"];
				$i++;
			}
			Core::getDB()->free_result($result);
			Core::getTPL()->addLoop("constructions", $const);
	   	}
	   	return $this;
	}

	/**
	 * Adds Requirements for a construction.
	 *
	 * @param integer $id		Construction ID
	 * @param integer $level	Level
	 * @param integer $needs	Required construction
	 *
	 * @return Bengine_Page_Construction_Edit
	 */
	protected function addRequirement($id, $level, $needs)
	{
		if(!is_numeric($level) || $level < 0) { $level = 1; }
		Core::getQuery()->insert("requirements", array("buildingid", "needs", "level"), array($id, $needs, $level));
		Core::getCache()->flushObject("requirements");
		return $this;
	}

	/**
	 * Deletes the stated requirement.
	 *
	 * @param integer $delete	Requirement id
	 * @param integer $returnId	Construction ID
	 *
	 * @return Bengine_Page_Construction_Edit
	 */
	protected function deleteRequirementAction($delete, $returnId)
	{
		Core::getQuery()->delete("requirements", "requirementid = '".$delete."'");
		Core::getCache()->flushObject("requirements");
		$this->redirect("game.php/".SID."/Construction_Edit/Index/".$returnId);
		return $this;
	}

	/**
	 * Saves the construction data.
	 *
	 * @param string $name
	 * @param string $nameId
	 * @param integer $allowOnMoon
	 * @param string $desc
	 * @param string $fullDesc
	 * @param string $prodWhat
	 * @param string $prod
	 * @param string $consWhat
	 * @param string $consumption
	 * @param string $special
	 * @param string $basicMetal
	 * @param string $basicSilicon
	 * @param string $basicHydrogen
	 * @param string $basicEnergy
	 * @param string $chargeMetal
	 * @param string $chargeSilicon
	 * @param string $chargeHydrogen
	 * @param string $chargeEnergy
	 *
	 * @return Bengine_Page_Construction_Edit
	 */
	protected function saveConstruction(
		$name, $nameId, $allowOnMoon, $desc, $fullDesc,
		$prodWhat, $prod, $consWhat, $consumption, $special,
		$basicMetal, $basicSilicon, $basicHydrogen, $basicEnergy,
		$chargeMetal, $chargeSilicon, $chargeHydrogen, $chargeEnergy
	)
	{
		Hook::event("EditUnitSave");

		// Fetch production from form
		$prodMetal = ""; $prodSilicon = ""; $prodHydrogen = ""; $prodEnergy = "";
		if($prodWhat == "metal")
		{
			$prodMetal = $prod;
		}
		else if($prodWhat == "silicon")
		{
			$prodSilicon = $prod;
		}
		else if($prodWhat == "hydrogen")
		{
			$prodHydrogen = $prod;
		}
		else if($prodWhat == "energy")
		{
			$prodEnergy = $prod;
		}

		// Fetch consumption from form
		$consMetal = ""; $consSilicon = ""; $consHydrogen = ""; $consEnergy = "";
		if($consWhat == "metal")
		{
			$consMetal = $consumption;
		}
		else if($consWhat == "silicon")
		{
			$consSilicon = $consumption;
		}
		else if($consWhat == "hydrogen")
		{
			$consHydrogen = $consumption;
		}
		else if($consWhat == "energy")
		{
			$consEnergy = $consumption;
		}

		// Now generate the sql query.
		$atts = array("special", "allow_on_moon",
			"basic_metal", "basic_silicon", "basic_hydrogen", "basic_energy",
			"prod_metal", "prod_silicon", "prod_hydrogen", "prod_energy",
			"cons_metal", "cons_silicon", "cons_hydrogen", "cons_energy",
			"charge_metal", "charge_silicon", "charge_hydrogen", "charge_energy"
		);
		$vals = array($special, (int) $allowOnMoon,
			$basicMetal, $basicSilicon, $basicHydrogen, $basicEnergy,
			$prodMetal, $prodSilicon, $prodHydrogen, $prodEnergy,
			$consMetal, $consSilicon, $consHydrogen, $consEnergy,
			$chargeMetal, $chargeSilicon, $chargeHydrogen, $chargeEnergy
		);
		Core::getQuery()->update("construction", $atts, $vals, "name = '".$nameId."'");

		// Save the name and description
		$languageId = Core::getLang()->getOpt("languageid");
		if(Str::length($name) > 0)
		{
			$result = Core::getQuery()->select("phrases", "phraseid", "", "title = '".$nameId."'");
			if(Core::getDB()->num_rows($result) > 0)
			{
				Core::getQuery()->update("phrases", array("content"), array(convertSpecialChars($name)), "title = '".$nameId."'");
			}
			else
			{
				Core::getQuery()->insert("phrases", array("languageid", "phrasegroupid", "title", "content"), array($languageId, 4, $nameId, convertSpecialChars($name)));
			}
			Core::getDB()->free_result($result);
		}
		if(Str::length($desc) > 0)
		{
			$result = Core::getQuery()->select("phrases", "phraseid", "", "title = '".$nameId."_DESC'");
			if(Core::getDB()->num_rows($result) > 0)
			{
				Core::getQuery()->update("phrases", array("content"), array(convertSpecialChars($desc)), "title = '".$nameId."_DESC'");
			}
			else
			{
				Core::getQuery()->insert("phrases", array("languageid", "phrasegroupid", "title", "content"), array($languageId, 4, $nameId."_DESC", convertSpecialChars($desc)));
			}
			Core::getDB()->free_result($result);
		}
		if(Str::length($fullDesc) > 0)
		{
			$result = Core::getQuery()->select("phrases", "phraseid", "", "title = '".$nameId."_FULL_DESC'");
			if(Core::getDB()->num_rows($result) > 0)
			{
				Core::getQuery()->update("phrases", array("content"), array(convertSpecialChars($fullDesc)), "title = '".$nameId."_FULL_DESC'");
			}
			else
			{
				Core::getQuery()->insert("phrases", array("languageid", "phrasegroupid", "title", "content"), array($languageId, 4, $nameId."_FULL_DESC", convertSpecialChars($fullDesc)));
			}
			Core::getDB()->free_result($result);
		}

		// Rebuild language cache
		Core::getLang()->rebuild("info");
		return $this;
	}

	/**
	 * Creates the options of all resources.
	 *
	 * @param string $what	Pre-selected entry
	 *
	 * @return string	Option list
	 */
	protected function getResourceSelect($what)
	{
		$options = "";
		foreach($this->resources as $key => $value)
		{
			if($what == $key) { $s = 1; } else { $s = 0; }
			$options .= createOption($key, Core::getLang()->getItem($value), $s);
		}
		return $options;
	}
}
?>