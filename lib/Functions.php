<?php
/**
 * Generic functions libary.
 *
 * @package Recipe 1.2
 * @author Sebastian Noll
 * @copyright Copyright (c) 2009, Sebastian Noll
 * @license Proprietary
 * @version $Id: Functions.php 19 2011-05-27 10:30:33Z secretchampion $
 */

/**
 * Displays string and shut program down.
 * (Improved function of die().)
 *
 * @param string	The to displayed string.
 *
 * @return void
 */
function terminate($string)
{
	if(is_array($string)) { print_r($string); }
	else { echo $string; }
	exit;
}

/**
 * Forward to login page.
 *
 * @param string	Error id to output
 *
 * @return void
 */
function forwardToLogin($errorid)
{
	if(LOGIN_REQUIRED)
	{
		if(strpos(LOGIN_URL, '?') === false)
		{
			$login = LOGIN_URL."?error=".$errorid;
		}
		else { $login = LOGIN_URL; }
		Hook::event("ForwardToLoginPage", array(&$login, $errorid));
		doHeaderRedirection($login, false);
	}
	Logger::addMessage($errorid);
	Core::getTPL()->display("login");
	return;
}

/**
 * Perform an header redirection.
 *
 * @param string	URL
 *
 * @return void
 */
function doHeaderRedirection($url, $appendSession = false)
{
	return Recipe_Header::redirect($url, $appendSession);
}

/**
 * Checks whether the incoming email address is valid.
 *
 * @param string	Email address to check
 *
 * @return boolean
 */
function isMail($mail)
{
	if(preg_match("#^[^\\x00-\\x1f@]+@[^\\x00-\\x1f@]{2,}\.[a-z]{2,}$#i", $mail) == 0)
	{
		return false;
	}
	return true;
}

/**
 * Generates a random text.
 *
 * @param integer	The length of the random text
 *
 * @return string	The random text
 */
function randString($length)
{
	$pool = "qwertzupasdfghkyxcvbnm";
	$pool .= "23456789";
	$pool .= "QWERTZUPLKJHGFDSAYXCVBNM";
	srand ((double)microtime()*1000000);
	$randstr = "";
	for($index = 0; $index < $length; $index++)
	{
		$randstr .= substr($pool,(rand()%(strlen($pool))), 1);
	}
	return $randstr;
}

/**
 * Parses an URL and return its components.
 *
 * @param string	The URL to parse
 *
 * @return array	The URL components
 */
function parseUrl($url)
{
	$out = array();
	$r  = "^(?:(?P<scheme>\w+)://)?";
	$r .= "(?:(?P<login>\w+):(?P<pass>\w+)@)?";
	$r .= "(?P<host>(?:(?P<subdomain>[\w\.]+)\.)?" . "(?P<domain>\w+\.(?P<extension>\w+)))";
	$r .= "(?::(?P<port>\d+))?";
	$r .= "(?P<path>[\w/]*/(?P<file>\w+(?:\.\w+)?)?)?";
	$r .= "(?:\?(?P<arg>[\w=&]+))?";
	$r .= "(?:#(?P<anchor>\w+))?";
	$r = "!$r!";
	preg_match($r, $url, $out);
	return $out;
}

/**
 * Capitalizes the first letter of each directory part.
 *
 * @param string	Path
 * @param char		Path separator [optional]
 *
 * @return string
 */
function getClassPath($path, $s = "_", $r = "/")
{
	return str_replace(" ", $r, ucwords(str_replace($s, " ", $path)));
}

/**
 * Check if a file exists in the include path.
 *
 * @param string|array	Name of the file to look for
 *
 * @return mixed	The full path if file exists, false otherwise
 */
function file_exists_inc($file)
{
	if(is_array($file))
	{
		foreach($file as $file)
		{
			if($fullpath = file_exists_inc($file))
			{
				return $fullpath;
			}
		}
		return false;
	}
	$paths = explode(PATH_SEPARATOR, get_include_path());
	foreach($paths as $path)
	{
		$fullpath = $path.$file;
		if(file_exists($fullpath))
		{
			return $fullpath;
		}
	}
	return false;
}
?>