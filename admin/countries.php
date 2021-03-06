<?php
/***************************************************************************
 *   copyright				: (C) 2008 - 2014 WeBid
 *   site					: http://www.webidsupport.com/
 ***************************************************************************/

/***************************************************************************
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version. Although none of the code may be
 *   sold. If you have been sold this script, get a refund.
 ***************************************************************************/

define('InAdmin', 1);
$current_page = 'settings';
include '../common.php';
include $include_path . 'functions_admin.php';
include 'loggedin.inc.php';
include $include_path . 'functions_rebuild.php';

unset($ERR);

if (isset($_POST['act']))
{
	// remove any countries that need to be
	if (isset($_POST['delete']) && count($_POST['delete']) > 0)
	{
		// we use a single SQL query to quickly do ALL our deletes
		$query = "DELETE FROM " . $DBPrefix . "countries WHERE ";

		// if this is the first country being deleted it don't
		// precede it with an " or " in the SQL string
		for ($i = 0; $i < count($_POST['delete']); $i++)
		{
			if ($i > 0)
			{
				$query .= " OR ";
			}
			$query .= "country = '" . $system->cleanvars($_POST['delete'][$i]) . "'";
		}
		$system->check_mysql(mysql_query($query), $query, __LINE__, __FILE__);
	}

	//update countries with new names
	for ($i = 0; $i < count($_POST['old_countries']); $i++)
	{
		if ($_POST['old_countries'][$i] != $_POST['new_countries'][$i])
		{
			$query = "UPDATE " . $DBPrefix . "countries SET
					country = '" .  $system->cleanvars($_POST['new_countries'][$i]) . "'
					WHERE country = '" . $system->cleanvars($_POST['old_countries'][$i]) . "'";
			$system->check_mysql(mysql_query($query), $query, __LINE__, __FILE__);
		}
	}

	// If a new country was added, insert it into database
	if (!empty($_POST['new_countries'][(count($_POST['new_countries']) - 1)]))
	{
		$query = "INSERT INTO " . $DBPrefix . "countries (country) VALUES ('" . $system->cleanvars($_POST['new_countries'][(count($_POST['new_countries']) - 1)]) . "')";
		$system->check_mysql(mysql_query($query), $query, __LINE__, __FILE__);
	}
	rebuild_html_file('countries');
	$ERR = $MSG['1028'];
}

include $include_path . 'countries.inc.php';

$i = 1;
while ($i < count($countries))
{
	$j = $i - 1;
	// check if the country is being used by a user
	$query = "SELECT id FROM " . $DBPrefix . "users WHERE country = '" . $countries[$i] . "' LIMIT 1";
	$res = mysql_query($query);
	$system->check_mysql($res, $query, __LINE__, __FILE__);
	$USEDINUSERS = mysql_num_rows($res);
	
	$template->assign_block_vars('countries', array(
			'COUNTRY' => $countries[$i],
			'SELECTBOX' => ($USEDINUSERS == 0) ? '<input type="checkbox" name="delete[]" value="' . $countries[$i] . '">' : '<img src="../images/nodelete.gif" alt="You cannot delete this">'
			));
	$i++;
}

$template->assign_vars(array(
		'ERROR' => isset($ERR) ? $ERR : ''
		));

$template->set_filenames(array(
		'body' => 'countries.tpl'
		));
$template->display('body');

?>