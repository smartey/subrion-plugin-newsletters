<?php
/******************************************************************************
 *
 * Subrion - open source content management system
 * Copyright (C) 2015 Intelliants, LLC <http://www.intelliants.com>
 *
 * This file is part of Subrion.
 *
 * Subrion is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Subrion is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Subrion. If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @link http://www.subrion.org/
 *
 ******************************************************************************/

function getAllLevelsChildren($ids, $all, $catTable)
{
	$all = array_unshift($all, $ids);
	$ids_str = implode(',', $all[0]);
	$where = '`pid` IN (' . $ids_str . ')';
	$children = $iaCore->iaDb->onefield('id', $where, 0, null, $catTable);

	if (!empty($children))
	{
		$all = getAllLevelsChildren($children, $all, $catTable);
	}

	return $all;
}

$catTable = 'autos_categs';
$iaAutos = $this->iaCore->factoryPackage('autos', $extra);

if (count($cats) == 1 && $cats[0] == '0')
{
	$where = " t1.`date` > '" . $last_sent . "' ORDER BY t1.`date`";
	$autos = $iaAuto->get($where, 0, $this->iaCore->get('newsletters_items_limit'), array('field' => 'date', 'direction' => 'ASC'));
}
else
{
	$selected_cats = explode(',', $cats);

	$all_cats = getAllLevelsChildren($selected_cats, array(), $catTable);

	$where = " t1.`date` > '" . $last_sent . "' AND `pid` IN (" . $all_cats . ") ORDER BY t1.`date`";
	$autos = $iaAuto->get($where, 0, $this->iaCore->get('newsletters_items_limit'), array('field' => 'date', 'direction' => 'ASC'));
}

$items = array();

if(!empty($autos))
{
	foreach($autos as $key => $auto)
	{
		$items[$key]['title'] = $auto['title'];
		$items[$key]['description'] = strlen($auto['description']) <= $this->iaCore->get('description_chars_num') ?
			$auto['description'] : wordwrap($auto['description'], $this->iaCore->get('description_chars_num'));
		$items[$key]['url'] = $iaauto->url('view', $auto);
		$items[$key]['date'] = strtotime($auto['date']);
	}
}