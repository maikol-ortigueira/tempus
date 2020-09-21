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

		JHtmlSidebar::addEntry(
			Text::_('COM_TEMPUS_TITLE_CONCERTS'),
			'index.php?option=com_tempus&view=concerts',
			$vName == 'concerts'
		);

		JHtmlSidebar::addEntry(
			Text::_('COM_TEMPUS_TITLE_REHEARSALS'),
			'index.php?option=com_tempus&view=rehearsals',
			$vName == 'rehearsals'
		);

		/*###addSubmenu-new-view###*/

	}

	/**
	 * Gets the field attached to an item
	 *
	 * @param   int     $pk     The item's id
	 *
	 * @param   string  $table  The table's name
	 *
	 * @param   string  $field  The field's name
	 *
	 * @return  array  The files
	 */
	public static function getField($pk, $table, $field)
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
	 * Gets values of a simple row from the database
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

		return $db->loadAssoc();
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
	public static function getListValues($fields = array(), $table, $whereField, $whereValue, $condition = '=')
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select($db->quoteName($fields))
			->from($table)
			->where($db->quoteName($whereField) . ' ' . $condition . ' ' . $db->quote($whereValue));

		$db->setQuery($query);

		return $db->loadAssocList();
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

	public static function getVoices($all=false)
	{
		$voices = array(
			1 => 'soprano',
			2 => 'alto',
			3 => 'tenor',
			4 => 'bass',
		);

		if ($all)
		{
			$voices[100] = 'satb';
			// array_push($voices, 'satb');
		}

		return $voices;
	}

	/**
	 * Method to get the user profile data
	 *
	 * @param string $user_id
	 * 
	 * @return stdClass 	The user profile data
	 */
	public static function getUserProfile($user_id)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);

		$query->select('*')
			->from($db->quoteName('#__user_profiles'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($user_id));

		$db->setQuery($query);

		$result = $db->loadObjectList();

		$profile = new stdClass();

		foreach ($result as $value) {
			$key = explode('.',$value->profile_key);
			$profile->{$key[1]} = json_decode($value->profile_value);
		}

		return $profile;
	}

	public static function get($item, $item_id): stdClass
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);

		$query
			->select('*')
			->from($db->quoteName('#__tempus_' . $item . 's'))
			->where($db->quoteName('id') . ' = ' . $db->quote($item_id));

		$db->setQuery($query);

		$result = $db->loadObject();

		return $result;
	}
}

