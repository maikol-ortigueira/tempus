<?php
/**
 * @version    1.0.0
 * @package    Com_Tempus
 * @author     Maikol Fustes <maikol.ortigueira@gmail.com>
 * @copyright  2020 Maikol Fustes
 * @license    Licencia Pública General GNU versión 2 o posterior. Consulte LICENSE.txt
 */

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Tempus model.
 *
 * @since  1.6
 */
class TempusModelRehearsal extends AdminModel
{
	/**
	 * @var      string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_TEMPUS';

	/**
	 * @var   	string  	Alias to manage history control
	 * @since   3.2
	 */
	public $typeAlias = 'com_tempus.rehearsal';

	/**
	 * @var null  Item data
	 * @since  1.6
	 */
	protected $item = null;

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return    JTable    A database object
	 *
	 * @since    1.6
	 */
	public function getTable($type = 'Rehearsal', $prefix = 'TempusTable', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm  A JForm object on success, false on failure
	 *
	 * @since    1.6
     *
     * @throws
	 */
	public function getForm($data = array(), $loadData = true)
	{
            // Initialise variables.
            $app = Factory::getApplication();

            // Get the form.
            $form = $this->loadForm(
                    'com_tempus.rehearsal', 'rehearsal',
                    array('control' => 'jform',
                            'load_data' => $loadData
                    )
			);
			$params = ComponentHelper::getParams('com_tempus');

			$form->setFieldAttribute('rehearsal_subject', 'default', $params->get('rehearsal_subject'));
			$form->setFieldAttribute('rehearsal_body', 'default', $params->get('rehearsal_body'));

			if (empty($form))
            {
                return false;
            }

            return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return   mixed  The data for the form.
	 *
	 * @since    1.6
     *
     * @throws
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_tempus.edit.rehearsal.data', array());

		if (empty($data))
		{
			if ($this->item === null)
			{
				$this->item = $this->getItem();
			}
			$data = $this->item;
 		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);
		if ($item AND property_exists($item, 'songs_id'))
		{
			$item->songs_id = explode(',', $item->songs_id);
		}

		if ($item AND property_exists($item, 'convocation'))
		{
			$registry = new Registry($item->convocation);
			$voices = $registry->toArray();
			$convocation = array();

			foreach ($voices as $voice => $members) {
				$convocation[$voice.'_ids'] = explode(',' ,$members);
			}

			$item->convocation = $convocation;
		}

		if ($item AND property_exists($item, 'start_date'))
		{
			$startDate = new DateTime($item->start_date);

			$item->rehearsal_date = $startDate->format('d-m-Y');
			$item->start_hour = (int) $startDate->format('h');
			$item->start_minute = $startDate->format('i');
			$item->start_ampm = strtolower($startDate->format('A'));
		}

		if ($item AND property_exists($item, 'end_date'))
		{
			$endDate = new DateTime($item->end_date);

			$item->end_hour = (int) $endDate->format('h');
			$item->end_minute = $endDate->format('i');
			$item->end_ampm = strtolower($endDate->format('A'));
		}

		return $item;
	}

	/**
	 * Method to duplicate an Rehearsal
	 *
	 * @param   array  &$pks  An array of primary key IDs.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @throws  Exception
	 */
	public function duplicate(&$pks)
	{
		$user = Factory::getUser();

		// Access checks.
		if (!$user->authorise('core.create', 'com_tempus'))
		{
			throw new Exception(Text::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
		}

		$dispatcher = JEventDispatcher::getInstance();
		$context    = $this->option . '.' . $this->name;

		// Include the plugins for the save events.
		PluginHelper::importPlugin($this->events_map['save']);

		$table = $this->getTable();

		foreach ($pks as $pk)
		{

			if ($table->load($pk, true))
			{
				// Reset the id to create a new record.
				$table->id = 0;

				if (!$table->check())
				{
					throw new Exception($table->getError());
				}

				// Trigger the before save event.
				$result = $dispatcher->trigger($this->event_before_save, array($context, &$table, true));

				if (in_array(false, $result, true) || !$table->store())
				{
					throw new Exception($table->getError());
				}

				// Trigger the after save event.
				$dispatcher->trigger($this->event_after_save, array($context, &$table, true));
			}
			else
			{
				throw new Exception($table->getError());
			}
		}

		// Clean cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   JTable  $table  Table Object
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id))
		{
			// Set ordering to the last item if not set
			if (@$table->ordering === '')
			{
				$db = Factory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__tempus_rehearsals');
				$max             = $db->loadResult();
				$table->ordering = $max + 1;
			}
		}
	}

	/*###newMethod###*/
}
