<?php

/**
 * @version    1.0.0
 * @package    Com_Tempus
 * @author     Maikol Fustes <maikol.ortigueira@gmail.com>
 * @copyright  2020 Maikol Fustes
 * @license    Licencia Pública General GNU versión 2 o posterior. Consulte LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Object\CMSObject;

/**
 * Tempus helper.
 *
 * @since  1.6
 */
class TempusHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  string
	 *
	 * @return void
	 */
	public static function addSubmenu($vName = '')
	{
		JHtmlSidebar::addEntry(
			Text::_('COM_TEMPUS_TITLE_SONGS'),
			'index.php?option=com_tempus&view=songs',
			$vName == 'songs'
		);

		JHtmlSidebar::addEntry(
			Text::_('COM_TEMPUS_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&view=categories&extension=com_tempus',
			$vName == 'categories'
		);

		JHtmlSidebar::addEntry(
			Text::_('COM_TEMPUS_SUBMENU_SINGERS'),
			'index.php?option=com_tempus&view=singers',
			$vName == 'singers'
		);

		/*###addSubmenu-new-view###*/

	}

	/**
	 * Gets the files attached to an item
	 *
	 * @param   int     $pk     The item's id
	 *
	 * @param   string  $table  The table's name
	 *
	 * @param   string  $field  The field's name
	 *
	 * @return  array  The files
	 */
	public static function getFiles($pk, $table, $field)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select($field)
			->from($table)
			->where('id = ' . (int) $pk);

		$db->setQuery($query);

		return explode(',', $db->loadResult());
	}

	/**
	 * Gets a list of values from de database
	 *
	 * @param   array    $fields  		Fields to return values
	 * @param   string   $table   		Table name
	 * @param   string   $whereField   	The field that makes the condition
	 * @param   string   $whereValue  	Value from
	 * @param   string   $condition  	Condition
	 *
	 * @return  array	Values
	 */
	public static function getValues($fields = array(), $table, $whereField, $whereValue, $condition = '=')
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select($db->quoteName($fields))
			->from($table)
			->where($db->quoteName($whereField) . ' ' . $condition . ' ' . $db->quote($whereValue));

		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return    JObject
	 *
	 * @since    1.6
	 */
	public static function getActions()
	{
		$user   = Factory::getUser();
		$result = new CMSObject;

		$assetName = 'com_tempus';

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}

	public static function getVoices()
	{
		$voices = array(
			'0' => 'SATB',
			'1' => 'Sopranos',
			'2' => 'Altos',
			'3' => 'Tenores',
			'4' => 'Bajos',
		);

		return $voices;
	}
}

