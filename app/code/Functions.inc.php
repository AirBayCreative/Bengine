<?php
/**
 * Unsorted functions.
 *
 * @package Bengine
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll" <snoll@4ym.org>
 * @version $Id: Functions.inc.php 45 2011-07-26 09:50:07Z secretchampion $
 */



/**
 * Executes a formula.
 *
 * @param string	Formular to execute
 * @param integer	First number to replace
 * @param integer	Second number to replace
 *
 * @return integer	Result
 */
function parseFormula($formula, $basic, $level)
{
	Hook::event("ParseFormula", array(&$formula, &$basic, &$level));
	$formula = Str::replace("{level}", $level, $formula);
	$formula = Str::replace("{basic}", $basic, $formula);
	$formula = Str::replace("{temp}", Bengine::getPlanet()->getData("temperature"), $formula);
	$formula = preg_replace("#\{tech\=([0-9]+)\}#ie", 'Bengine::getResearch(\\1)', $formula);
	$formula = preg_replace("#\{building\=([0-9]+)\}#ie", 'Bengine::getPlanet()->getBuilding(\\1)', $formula);
	$result = 0;
	eval("\$result = ".$formula.";");
	return (int) $result;
}

/**
 * Executes a simple formula.
 *
 * @param string	Formular to execute
 * @param integer	Basic costs
 * @param integer	Level
 *
 * @return integer	Result
 */
function parseSimpleFormula($formula, $basic, $level)
{
	Hook::event("ParseSimpleFormula", array(&$formula, &$basic, &$level));
	$formula = Str::replace("{level}", $level, $formula);
	$formula = Str::replace("{basic}", $basic, $formula);
	$result = 0;
	eval("\$result = ".$formula.";");
	return (int) $result;
}

/**
 * Generates a time term of given seconds.
 *
 * @param integer	Seconds
 *
 * @return string	Time term
 */
function getTimeTerm($secs)
{
	$secs = abs($secs);
	Hook::event("FormatTimeStart", array(&$secs));
	$days = floor($secs/60/60/24);
	$hours = floor(($secs/60/60)%24);
	$mins = floor(($secs/60)%60);
	$seconds = floor($secs%60);
	Hook::event("FormatTimeEnd", array(&$days, &$days, &$mins, &$seconds));
	$parsed = sprintf("%dd %02d:%02d:%02d", $days, $hours, $mins, $seconds);
	if($days == 0) { $parsed = Str::substring($parsed, 3); }
	return $parsed;
}

/**
 * Prettify number: 1000 => 1.000
 *
 * @param integer	The number being formatted
 * @param integer	Sets the number of decimal fs
 *
 * @return string	Number
 */
function fNumber($number, $decimals = 0)
{
	return number_format($number, $decimals, Core::getLang()->getItem("DECIMAL_POINT"), Core::getLang()->getItem("THOUSANDS_SEPERATOR"));
}

/**
 * Returns building time due to the used resources.
 *
 * @param integer	Used metal
 * @param integer	Used silicon
 * @param integer	Building mode (1: building, 2: unit, 3: research)
 *
 * @return integer	Time in seconds
 */
function getBuildTime($metal, $silicon, $mode)
{
	switch($mode)
	{
		case 1: // Buildings
		case 5:
			$roboFactory = Bengine::getPlanet()->getBuilding("ROBOTIC_FACTORY");
			$time = (($metal + $silicon) / 2500) * (1 / ($roboFactory + 1)) * pow(0.5, Bengine::getPlanet()->getBuilding("NANO_FACTORY"));
		break;
		case 2: // Research
			$time = Bengine::getResearchTime($metal + $silicon);
		break;
		case 3: // Shipyard
		case 4:
			$time = (($metal + $silicon) / 5000) * (2 / (Bengine::getPlanet()->getBuilding("SHIPYARD") + 1)) * pow(0.5, Bengine::getPlanet()->getBuilding("NANO_FACTORY"));
		break;
	}

	if(is_numeric(Core::getOptions()->get("GAMESPEED")) && Core::getOptions()->get("GAMESPEED") > 0)
	{
		$time *= 3600 * floatval(Core::getOptions()->get("GAMESPEED"));
	}
	Hook::event("ConstructTime", array(&$time, $metal, $silicon, $mode));
	return (int) max(ceil($time), 1);
}

/**
 * Checks a string for valid characters.
 *
 * @param string
 *
 * @return boolean
 */
function checkCharacters($string)
{
	if(preg_match("/^[A-z0-9_\-\s\.]{".Core::getOptions()->get("MIN_USER_CHARS").",".Core::getOptions()->get("MAX_USER_CHARS")."}$/i", $string))
	{
		return true;
	}
	return false;
}

/**
 * Checks a string for valid email address.
 *
 * @param string
 *
 * @return boolean
 */
function checkEmail($mail)
{
	if(!preg_match("#^[a-zA-Z0-9-]+([._a-zA-Z0-9.-]+)*@[a-zA-Z0-9.-]+\.([a-zA-Z]{2,4})$#is", $mail))
	{
		return false;
	}
	$banned = Core::getConfig()->get("BANNED_EMAILS");
	if(!empty($banned))
	{
		$banned = Arr::trimArray(explode(",", $banned));
		foreach($banned as $expr)
		{
			if(Str::inString($expr, $mail))
			{
				return false;
			}
		}
	}
	return true;
}

/**
 * Generates a coordinate link
 *
 * @param integer	Galaxy
 * @param integer	System
 * @param integer	Position
 * @param boolean	Replace session with wildcard
 *
 * @return string	Link code
 */
function getCoordLink($galaxy, $system, $position, $sidWildcard = false)
{
	$sid = ($sidWildcard) ? "%SID%" : SID;
	return Link::get("game.php/".$sid."/Galaxy/Index/".$galaxy."/".$system, "[".$galaxy.":".$system.":".$position."]");
}

/**
 * Returns the CSS class of the fleet mode.
 *
 * @param integer	EH-Mode
 *
 * @return string	CSS class
 */
function getFleetMessageClass($id)
{
	switch($id)
	{
		case 6: $return = "STATIONATE"; break;
		case 7: $return = "TRANSPORT"; break;
		case 8: $return = "COLONIZE"; break;
		case 9: $return = "RECYCLING"; break;
		case 10: $return = "ATTACK"; break;
		case 11: $return = "SPY"; break;
		case 12: case 18: $return = "ALLIANCE_ATTACK"; break;
		case 13: case 17: $return = "HALT"; break;
		case 14: $return = "MOON_DESTRUCTION"; break;
		case 15: $return = "EXPEDITION"; break;
		case 16: $return = "ROCKET_ATTACK"; break;
		case 20: $return = "RETURN_FLY"; break;
		default: $return = "UNKOWN"; break;
	}
	Hook::event("GetConstructionTime", array(&$return, $id));
	return strtolower($return);
}

/**
 * Plain BB-Code parser.
 *
 * @param string	Text to parse
 *
 * @return string
 */
function parseBBCode($text)
{
	$patterns = array();
	$patterns[] = "/\[url=(&quot;|['\"]?)([^\"']+)\\1](.+)\[\/url\]/esiU";
	$patterns[] = "/\[url]([^\"\[]+)\[\/url\]/eiU";
	$patterns[] = "/\[img]([^\"]+)\[\/img\]/siU";
	$patterns[] = "/\[b]([^\"]+)\[\/b\]/siU";
	$patterns[] = "/\[u]([^\"]+)\[\/u\]/siU";
	$patterns[] = "/\[i]([^\"]+)\[\/i\]/siU";
	$patterns[] = "/\[blink]([^\"]+)\[\/blink\]/siU";
	$patterns[] = "/\[d]([^\"]+)\[\/d\]/siU";
	$patterns[] = "/\[color=([^\"]+)\]/siU";
	$patterns[] = "/\[size=([^\"]+)\]/siU";
	$patterns[] = "/\[align=(center|right|left|justify)\]/siU";
	$patterns[] = "/\[list](.+)\[\/list\]/esiU";
	$replace = array();
	$replace[] = 'Link::get("\\2", "\\3", "\\2")';
	$replace[] = 'Link::get("\\1", "\\1")';
	$replace[] = "<img src=\"\\1\" alt=\"\" title=\"\" />";
	$replace[] = "<span style=\"font-weight: bold;\">\\1</span>";
	$replace[] = "<span style=\"text-decoration: underline;\">\\1</span>";
	$replace[] = "<i>\\1</i>";
	$replace[] = "<span style=\"text-decoration: blink;\">\\1</span>";
	$replace[] = "<span style=\"text-decoration: line-through;\">\\1</span>";
	$replace[] = "<span style=\"color: \\1;\">";
	$replace[] = "<span style=\"font-size: \\1;\">";
	$replace[] = "<div style=\"text-align: \\1;\">";
	$replace[] = 'parseList("\\1")';
	Hook::event("ParseBbCodeStart", array(&$text, &$patterns, &$replace));
	$text = preg_replace($patterns, $replace, $text);

	$search = array("[/color]", "[/size]", "[/align]", "{{line}}", "\t");
	$replace = array("</span>", "</span>", "</div>", "<hr/>", " ");
	$text = str_replace($search, $replace, $text);
	Hook::event("ParseBbCodeEnd", array(&$text));
	return $text;
}

/**
 * Filters some vulnerable HTML-tags from rich text.
 *
 * @param string
 *
 * @return string
 */
function richText($str)
{
	static $purifier = false;
	if(!$purifier)
	{
		define("HTMLPURIFIER_PREFIX", dirname(RECIPE_ROOT_DIR)."/lib");
		require_once "HTMLPurifier/Bootstrap.php";
		$config = HTMLPurifier_Config::createDefault();
		$config->set("Cache.SerializerPath", APP_ROOT_DIR."var/cache/htmlpurifier");
		$config->set("Core.Encoding", Core::getLang()->getOpt("charset"));
		$config->set("HTML.DefinitionID", "enduser-customize.html tutorial");
		$config->set('HTML.DefinitionRev', 1);
		$config->set("HTML.Doctype", "XHTML 1.0 Strict");
		$config->set("HTML.ForbiddenAttributes", array("id", "class"));
		if($def = $config->maybeGetRawHTMLDefinition())
		{
			$form = $def->addElement(
				"object",
				"Inline",
				"Optional:PARAM | Flow",
				"Common",
				array("declare" => "Bool",
					"classid" => "URI",
					"codebase" => "URI",
					"data" => "URI",
					"type" => "CDATA",
					"codetype" => "CDATA",
					"archive" => "CDATA",
					"standby" => "CDATA",
					"height" => "Number",
					"width" => "Number",
					"usemap" => "URI",
					"name" => "CDATA",
					"tabindex" => "Number")
			);
			$param = $def->addElement(
				"param",
				"Inline",
				"Empty",
				"",
				array(
					"id" => "Number",
					"name*" => "CDATA",
					"value" => "CDATA",
					"valuetype" => "Enum#DATA,REF,OBJECT",
					"type" => "CDATA")
			);
		}
		$purifier = new HTMLPurifier($config);
	}
	$str = Str::replace("\\r", "", $str);
	$str = Str::replace("\\n", "", $str);
	return Core::getDB()->real_escape_string($purifier->purify(stripslashes($str)));
}

/**
 * Extended BB-Code parser to generate HTML list.
 *
 * @param string	Raw code
 *
 * @return string	HTML
 */
function parseList($list)
{
	if($list == "") { return $list; }
	$list = str_replace("*", "</li>\n<li>", $list);
	if(strstr($list, "<li>")) { $list .= "</li>"; }
	$list = preg_replace("/^.*(<li>)/sU", "\\1", $list);
	return "<ul class=\"ally-list\">".$list."</ul>";
}

/**
 * Generates HTML code for a select option.
 *
 * @param string	Option value
 * @param string	Option name
 * @param boolean	Set selected tag
 * @param string	Additional CSS class
 *
 * @return string	HTML
 */
function createOption($value, $option, $selected, $class = "")
{
	$class = ($class != "") ? " class=\"".$class."\"" : "";
	$coption = "<option value=\"".$value."\"".$class;
	if($selected == 1)
	{
		$coption .= " selected=\"selected\"";
	}
	$coption .= ">".$option."</option>";
	return $coption;
}

/**
 * Deletes all data of an alliance.
 *
 * @param integer	Alliance id to delete
 *
 * @return void
 */
function deleteAlliance($aid)
{
	$result = Core::getQuery()->select("user2ally u2a", array("u2a.userid", "a.tag"), "LEFT JOIN ".PREFIX."alliance a ON (a.aid = u2a.aid)", "u2a.aid = '".$aid."'");
	while($row = Core::getDB()->fetch($result))
	{
		new Bengine_AutoMsg(23, $row["userid"], TIME, $row);
	}
	Core::getDB()->free_result($result);
	Core::getQuery()->delete("alliance", "aid = '".$aid."'");
	Hook::event("DeleteAlliance", array($aid));
	return;
}

/**
 * Deletes all planet data.
 *
 * @param integer	Planet id
 * @param integer	User id
 * @param boolean	Planet is moon
 *
 * @return void
 */
function deletePlanet($id, $userid, $ismoon)
{
	$points  = Bengine_PointRenewer::getBuildingPoints($id);
	$points += Bengine_PointRenewer::getFleetPoints($id);
	$fpoints = Bengine_PointRenewer::getFleetPoints_Fleet($id);
	if(!$ismoon)
	{
		$result = Core::getQuery()->select("galaxy", "moonid", "", "planetid = '".$id."'");
		$row = Core::getDB()->fetch($result);
		Core::getDB()->free_result($result);
		if($row["moonid"]) { deletePlanet($row["moonid"], $userid, 1); }
	}
	Core::getDB()->query("UPDATE ".PREFIX."user SET points = points - '".$points."', fpoints = fpoints - '".$fpoints."' WHERE userid = '".$userid."'");
	Core::getQuery()->update("planet", array("userid"), array(null), "planetid = '".$id."'");
	if($ismoon)
	{
		Core::getQuery()->update("galaxy", array("moonid"), array(null), "moonid = '".$id."'");
	}
	else
	{
		Core::getQuery()->update("galaxy", array("destroyed"), array(1), "planetid = '".$id."'");
	}
	Hook::event("DeletePlanet", array($id, $userid, $ismoon));
	return;
}

/**
 * Returns the planet order.
 *
 * @param integer	Order mode
 *
 * @return string	ORDER BY term for SQL query
 */
function getPlanetOrder($mode)
{
	switch($mode)
	{
		case 1:
			$order = "p.planetid ASC";
			break;
		case 2:
			$order = "p.planetname ASC";
			break;
		case 3:
			$order = "g.galaxy ASC, g.system ASC, g.position ASC";
			break;
		default:
			$order = "p.planetid ASC";
			break;
	}
	Hook::event("GetPlanetOrder", array($mode, &$order));
	return $order;
}

/**
 * Checks if user is newbie protected.
 *
 * @param integer	Points of requesting user
 * @param integer	Points of target user
 *
 * @return integer	0: no protection, 1: weak, 2: strong
 */
function isNewbieProtected($committer, $target)
{
	Hook::event("NewbieProtection", array(&$committer, &$target));
	$newbieMin = (int) Core::getOptions()->get("NEWBIE_PROTECTION_MIN");
	$newbieFactorMin = (float) Core::getOptions()->get("NEWBIE_PROTECTION_FACTOR_MIN");
	$newbieFactorMax = (float) Core::getOptions()->get("NEWBIE_PROTECTION_FACTOR_MAX");
	if($committer <= 0 || $target <= 0)
	{
		if($committer > $target)
		{
			return 1;
		}
		return 2;
	}
	$n = 0;
	if($committer < $newbieMin) { $n += 1; }
	if($target < $newbieMin) { $n += 1; }
	if($target/$committer > $newbieFactorMin) { $n += 2; }
	if($committer/$target > $newbieFactorMin) { $n += 2; }
	if($committer < $target * $newbieFactorMax / 100) { $n += 2; }
	if($target < $committer * $newbieFactorMax / 100) { $n += 2; }
	if($n < 3)
	{
		return 0;
	}
	if($committer > $target)
	{
		return 1;
	}
	return 2;
}

/**
 * Time that a rocket needs to reach is destination.
 *
 * @param integer	Difference between galaxies
 *
 * @return integer	Time in seconds
 */
function getRocketFlightDuration($diff)
{
	return 30 + 60 * abs($diff);
}

/**
 * Replaces special characters.
 *
 * @param string	String to convert
 *
 * @return string	Converted string
 */
function convertSpecialChars($string)
{
	$string = htmlentities($string, ENT_NOQUOTES, "UTF-8", false);
	$string = htmlspecialchars_decode($string, ENT_NOQUOTES);
	$string = nl2br($string);
	return $string;
}

/**
 * Checks a string for valid url.
 *
 * @param string	String to check
 *
 * @return boolean
 */
function isValidURL($string)
{
	$pattern = "#^(http|https|mailto|news|irc)://([\w\d][\w\d\$\_\.\+\!\*\(\)\,\;\/\?\:\(at)\&\~\=\-]+)(:(\d +))?(/[^ ]*)?$#is";
	if(preg_match($pattern, $string)) { return true; }
	return false;
}

/**
 * Checks a string for valid image url.
 *
 * @param string	String to check
 *
 * @return boolean
 */
function isValidImageURL($string)
{
	$pattern = "#^http://([\w\d][\w\d\$\_\.\+\!\*\(\)\,\;\/\?\:\(at)\&\~\=\-]+)(:(\d +))?(/[^ ]*)?\.(jpg|jpeg|gif|png)$#is";
	if(preg_match($pattern, $string)) { return true; }
	return false;
}

/**
 * Sets the production factor of an user (e.g. for vacation mode).
 *
 * @param integer	The user id
 * @param integer	The new production factor
 *
 * @return void
 */
function setProdOfUser($userid, $prod)
{
	$sql = "UPDATE `".PREFIX."building2planet` b2p, `".PREFIX."planet` p SET b2p.`prod_factor` = '".$prod."' WHERE b2p.`planetid` = p.`planetid` AND p.`userid` = '".$userid."'";
	try { Core::getDB()->query($sql); }
	catch(Exception $e)
	{
		$e->printError();
	}
	Core::getQuery()->update("planet", array("solar_satellite_prod"), array($prod), "userid = '".$userid."'");
	return;
}

/**
 * Allows only positive numbers. Returns 0
 * if $n is lesser than 0.
 *
 * @param integer
 *
 * @return integer
 */
function _pos($n)
{
	if($n < 0)
	{
		return 0;
	}
	return (int) $n;
}
?>