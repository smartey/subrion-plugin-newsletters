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

class iaBackendController extends iaAbstractControllerPluginBackend
{
	protected $_name = 'queue';

	protected $_processEdit = false;


	public function __construct()
	{
		parent::__construct();

		$this->_path = IA_ADMIN_URL . 'newsletters' . IA_URL_DELIMITER;

		$this->setHelper($this->_iaCore->factoryPlugin($this->getPluginName(), iaCore::ADMIN, $this->getName()));
	}

	public function _indexPage(&$iaView)
	{
		if (isset($this->_iaCore->requestPath[0]) && 'toggle' == $this->_iaCore->requestPath[0])
		{
			return $this->_toggle($iaView);
		}

		$iaView->assign('queue', $this->getHelper()->get());
	}

	protected function _htmlAction(&$iaView)
	{
		$this->_delete($iaView);
	}

	protected function _assignValues(&$iaView, array &$entryData)
	{
		$statuses = $this->_iaDb->getEnumValues(iaUsers::getTable(), 'status');
		$statuses = $statuses['values'];

		$iaView->assign('statuses', $statuses);
		$iaView->assign('usergroups', $this->_iaCore->factory('users')->getUsergroups());
		$iaView->assign('check', (!empty($_POST['type']) && 'html' == $_POST['type'] ? true : false));
	}

	protected function _setDefaultValues(array &$entry)
	{
		$entry = array(
			'from_name' => iaUsers::getIdentity()->fullname,
			'from_mail' => iaUsers::getIdentity()->email,
			'type' => 'html',
			'subj' => '',
			'body' => '',
			'html_body' => ''
		);
	}

	protected function _preSaveEntry(array &$entry, array $data, $action)
	{
		parent::_preSaveEntry($entry, $data, $action);

		$body = ('text' == $data['type']) ? $data['body'] : $data['html_body'];
		$userGroups = isset($data['groups']) ? $data['groups'] : array();

		list($error, $this->_messages) = $this->getHelper()->create($data['from_name'], $data['from_mail'],
			$data['subj'], $body, ('html' == $data['type']), $userGroups, isset($data['subscribers']), $data['st']);

		return !$error;
	}

	protected function _entryAdd(array $entryData)
	{
		return true;
	}

	protected function _setPageTitle(&$iaView, array $entryData, $action)
	{
		parent::_setPageTitle($iaView, $entryData, $action);

		if (iaCore::ACTION_ADD == $action)
		{
			iaBreadcrumb::insert(iaLanguage::get('newsletter'), IA_ADMIN_URL . 'newsletters/', iaBreadcrumb::POSITION_FIRST + 1);
			iaBreadcrumb::remove(iaBreadcrumb::POSITION_LAST);
		}
	}

	protected function _toggle(&$iaView)
	{
		if (!isset($this->_iaCore->requestPath[1]))
		{
			return iaView::errorPage(iaView::ERROR_NOT_FOUND);
		}

		$this->getHelper()->toggle((int)$this->_iaCore->requestPath[1]);

		$iaView->setMessages(iaLanguage::get('saved'), iaView::SUCCESS);

		iaUtil::go_to($this->getPath());
	}

	protected function _delete(&$iaView)
	{
		if (!isset($this->_iaCore->requestPath[0]))
		{
			return iaView::errorPage(iaView::ERROR_NOT_FOUND);
		}

		$this->getHelper()->delete((int)$this->_iaCore->requestPath[0]);

		$iaView->setMessages(iaLanguage::get('queue_removed'), iaView::SUCCESS);

		iaUtil::go_to($this->getPath());
	}
}