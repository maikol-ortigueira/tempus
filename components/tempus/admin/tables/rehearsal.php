<?php
/**
 * @version    1.0.0
 * @package    Com_Tempus
 * @author     Maikol Fustes <maikol.ortigueira@gmail.com>
 * @copyright  2020 Maikol Fustes
 * @license    Licencia Pública General GNU versión 2 o posterior. Consulte LICENSE.txt
 */

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\Registry\Registry;
use Joomla\CMS\Table\Table;

// No direct access
defined('_JEXEC') or die;

/**
 * Rehearsal Table class
 *
 * @since  1.6
 */
class TempusTableRehearsal extends Table
{
	/**
	 * Constructor
	 *
	 * @param   JDatabase  &$db  A database connector object
	 */
	public function __construct(&$db)
	{
		JObserverMapper::addObserverClassToClass('JTableObserverContenthistory', 'TempusTablerehearsal', array('typeAlias' => 'com_tempus.rehearsal'));
		parent::__construct('#__tempus_rehearsals', 'id', $db);
        $this->setColumnAlias('published', 'state');
    }

	/**
	 * Overloaded bind function to pre-process the params.
	 *
	 * @param   array  $array   Named array
	 * @param   mixed  $ignore  Optional array or list of parameters to ignore
	 *
	 * @return  null|string  null is operation was satisfactory, otherwise returns an error
	 *
	 * @see     JTable:bind
	 * @since   1.5
     * @throws Exception
	 */
	public function bind($array, $ignore = '')
	{
		$app = Factory::getApplication();
	    $date = Factory::getDate();

		$input = $app->input;
		$task = $input->getString('task', '');

		if ($array['id'] == 0 && empty($array['created_by']))
		{
			$array['created_by'] = Factory::getUser()->id;
		}

		if ($array['id'] == 0 && empty($array['modified_by']))
		{
			$array['modified_by'] = Factory::getUser()->id;
		}

		if ($task == 'apply' || $task == 'save')
		{
			$array['modified_by'] = Factory::getUser()->id;
		}

		if (isset($array['rehearsal_date']))
		{
			$array['start_date'] = $this->getDate($array['rehearsal_date'], $array['start_hour'], $array['start_minute'], $array['start_ampm']);
			$array['end_date'] = $this->getDate($array['rehearsal_date'], $array['end_hour'], $array['end_minute'], $array['end_ampm']);

			// Comprobar que las horas están bien
			$endTime = (int) strtotime($array['end_date'])/60;
			$startTime = (int) strtotime($array['start_date'])/60;
			$difTime = ($endTime - $startTime);

			// Si la diferencia entre la hora de inicio y la hora de finalización no supera la media hora
			if (30 > $difTime)
			{
				$app->enqueueMessage(Text::sprintf('COM_TEMPUS_REHEARSAL_LESS_THAN_ONE_HOUR_MESSAGE', Text::_('COM_TEMPUS_TIME_HALF_HOUR'), (string) $difTime), 'warning');
			}
			// Si la diferencia entre la hora de inicio y la hora de finalización no supera la hora
			if (60 > $difTime && $difTime >= 30)
			{
				$app->enqueueMessage(Text::sprintf('COM_TEMPUS_REHEARSAL_LESS_THAN_ONE_HOUR_MESSAGE', Text::_('COM_TEMPUS_TIME_ONE_HOUR'), (string) $difTime));
			}
			// Si la hora comienzo es mayor o igual a la hora de finalización.
			if (0 >= $difTime)
			{
				$this->setError(Text::_('COM_TEMPUS_END_TIME_LOWER_THAN_START_TIME'));
				return false;
			}
		}

		if (isset($array['convocation']) && is_array($array['convocation']))
		{
			// Recuperamos las voces existentes
			$voices = TempusHelper::getVoices();

			$convocation = array();
			foreach ($voices as $key => $voice) {
				$voice = $voice . '_ids';
				if (isset($array['convocation'][$voice]) && is_array($array['convocation'][$voice]))
				{
					$convocation[TempusHelper::getVoices()[$key]] = implode(',', $array['convocation'][$voice]);
				}
				else
				{
					$convocation[TempusHelper::getVoices()[$key]] = '';
				}
			}
			$registry = new Registry;
			$registry->loadArray($convocation);
			$array['convocation'] = (string) $registry;
		}

		if (isset($array['notifications']) && is_array($array['notifications']))
		{
			$registry = new Registry;
			$registry->loadArray($array['notifications']);
			$array['notifications'] = (string) $registry;
		}

		if (isset($array['songs_id']) && is_array($array['songs_id']))
		{
			$array['songs_id'] = implode(',', $array['songs_id']);
		}

		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new Registry;
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}

		if (isset($array['metadata']) && is_array($array['metadata']))
		{
			$registry = new Registry;
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string) $registry;
		}

		if (!Factory::getUser()->authorise('core.admin', 'com_tempus.rehearsal.' . $array['id']))
		{
			$actions         = Access::getActionsFromFile(
				JPATH_ADMINISTRATOR . '/components/com_tempus/access.xml',
				"/access/section[@name='rehearsal']/"
			);
			$default_actions = Access::getAssetRules('com_tempus.rehearsal.' . $array['id'])->getData();
			$array_jaccess   = array();

			foreach ($actions as $action)
			{
                if (key_exists($action->name, $default_actions))
                {
                    $array_jaccess[$action->name] = $default_actions[$action->name];
                }
			}

			$array['rules'] = $this->JAccessRulestoArray($array_jaccess);
		}

		// Bind the rules for ACL where supported.
		if (isset($array['rules']) && is_array($array['rules']))
		{
			$this->setRules($array['rules']);
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * This function convert an array of JAccessRule objects into an rules array.
	 *
	 * @param   array  $jaccessrules  An array of JAccessRule objects.
	 *
	 * @return  array
	 */
	private function JAccessRulestoArray($jaccessrules)
	{
		$rules = array();

		foreach ($jaccessrules as $action => $jaccess)
		{
			$actions = array();

			if ($jaccess)
			{
				foreach ($jaccess->getData() as $group => $allow)
				{
					$actions[$group] = ((bool)$allow);
				}
			}

			$rules[$action] = $actions;
		}

		return $rules;
	}

	/**
	 * Overloaded check function
	 *
	 * @return bool
	 */
	public function check()
	{
		// If there is an ordering column and this is a new row then get the next ordering value
		if (property_exists($this, 'ordering') && $this->id == 0)
		{
			$this->ordering = self::getNextOrder();
		}

		// Support for alias field
		$this->alias = trim($this->alias);
		if (empty($this->alias))
		{
			$this->alias = $this->title;
		}
		$this->alias = OutputFilter::stringURLSafe($this->alias);

		return parent::check();
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table.  The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update.  If not
	 *                            set the instance property value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 *
	 * @return   boolean  True on success.
	 *
	 * @since    1.0.4
	 *
	 * @throws Exception
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Sanitize input.
		ArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state  = (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			if ($this->$k)
			{
				$pks = array($this->$k);
			}
			// Nothing to set publishing state on, return false.
			else
			{
				throw new Exception(500, Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
			}
		}

		// Build the WHERE clause for the primary keys.
		$where = $k . '=' . implode(' OR ' . $k . '=', $pks);

		// Determine if there is checkin support for the table.
		if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time'))
		{
			$checkin = '';
		}
		else
		{
			$checkin = ' AND (checked_out = 0 OR checked_out = ' . (int) $userId . ')';
		}

		// Update the publishing state for rows with the given primary keys.
		$this->_db->setQuery(
			'UPDATE `' . $this->_tbl . '`' .
			' SET `state` = ' . (int) $state .
			' WHERE (' . $where . ')' .
			$checkin
		);
		$this->_db->execute();

		// If checkin is supported and all rows were adjusted, check them in.
		if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
		{
			// Checkin each row.
			foreach ($pks as $pk)
			{
				$this->checkin($pk);
			}
		}

		// If the JTable instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks))
		{
			$this->state = $state;
		}

		return true;
	}

	/**
	 * Define a namespaced asset name for inclusion in the #__assets table
	 *
	 * @return string The asset name
	 *
	 * @see Table::_getAssetName
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_tempus.rehearsal.' . (int) $this->$k;
	}

	/**
	 * Returns the parent asset's id. If you have a tree structure, retrieve the parent's id using the external key field
	 *
	 * @param   JTable   $table  Table name
	 * @param   integer  $id     Id
	 *
	 * @see Table::_getAssetParentId
	 *
	 * @return mixed The id on success, false on failure.
	 */
	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		// We will retrieve the parent-asset from the Asset-table
		$assetParent = Table::getInstance('Asset');

		// Default: if no asset-parent can be found we take the global asset
		$assetParentId = $assetParent->getRootId();

		// The item has the component as asset-parent
		$assetParent->loadByName('com_tempus');

		// Return the found asset-parent-id
		if ($assetParent->id)
		{
			$assetParentId = $assetParent->id;
		}

		return $assetParentId;
	}

	/**
	 * Delete a record by id
	 *
	 * @param   mixed  $pk  Primary key value to delete. Optional
	 *
	 * @return bool
	 */
	public function delete($pk = null)
	{
		$this->load($pk);
		$result = parent::delete($pk);

		return $result;
	}

	/**
	 * Método para transformar la fecha para almacenar en la BBDD
	 * @param	string	$oldDate	Fecha inicial
	 * @param	int		$newHour	La hora para el nuevo valor
	 * @param	int		$newMin		El minuto para el nuevo valor
	 * @param	string	$ampm		Valor am o pm
	 *
	 * @return	La nueva fecha formateada para guardar en la bbdd
	 */
	protected function getDate($oldDate, $newHour, $newMin, $ampm='pm')
	{
		if ($ampm === 'pm')
		{
			$newHour = (int)$newHour + 12;
			if ($newHour === 24)
			{
				$newHour = 0;
			}
		}
		$date = new DateTime($oldDate);

		$date->setTime($newHour, (int)$newMin, 0);

		return $date->format('Y-m-d H:i:s');
	}

	/*###newMethod###*/
}
