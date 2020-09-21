<?php

/**
 * @version    1.0.0
 * @package    Com_Tempus
 * @author     Maikol Fustes <maikol.ortigueira@gmail.com>
 * @copyright  2020 Maikol Fustes
 * @license    Licencia Pública General GNU versión 2 o posterior. Consulte LICENSE.txt
 */

use Joomla\CMS\Component\ComponentHelper;

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Tempus records.
 *
 * @since  1.6
 */
class TempusModelSingers extends \Joomla\CMS\MVC\Model\ListModel
{
	/**
	 * Component Params
	 *
	 * @var string
	 */
	protected $params = '';

	/**
	* Constructor.
	*
	* @param   array  $config  An optional associative array of configuration settings.
	*
	* @see        JController
	* @since      1.6
	*/
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'u.`id`',
				'name', 'u.`name`',
				'username', 'u.`username`',
				'email', 'u.`email`',
			);
		}

		parent::__construct($config);

		$this->params = ComponentHelper::getParams($this->option);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function populateState($ordering = null, $direction = null)
	{
        // List state information.
        parent::populateState("u.id", "ASC");

        $context = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $context);

        // Split context into component and optional section
        $parts = FieldsHelper::extract($context);

        if ($parts)
        {
            $this->setState('filter.component', $parts[0]);
            $this->setState('filter.section', $parts[1]);
        }
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return   string A store id.
	 *
	 * @since    1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');
		$id .= ':' . $this->getState('filter.range');

		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
		$musGroup = $this->params->get('musician_group');
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select', 'DISTINCT u.*'
			)
		);
		$query->from('`#__users` AS u');

		// Join over the users_profile
		// Get the singer voice
		$query->select('voice.profile_value AS voice');
		$query->join('LEFT', '#__user_profiles AS voice ON voice.user_id=u.id AND voice.profile_key="tempus.range"');
		// Get the singer alias
		$query->select('alias.profile_value AS alias');
		$query->join('LEFT', '#__user_profiles AS alias ON alias.user_id=u.id AND alias.profile_key="tempus.alias"');

		// Join over the usergroup_map
		$query->select('ug.group_id AS uGroup');
		$query->join('LEFT', '#__user_usergroup_map AS ug ON ug.user_id=u.id');

		// Select only musicians
		$query->where('ug.group_id = ' . $musGroup);

		// Filter by published state
		$published = $this->getState('filter.block');

		if (is_numeric($published))
		{
			if ($published == 2)
			{
				$query->where('(u.block IN (0, 1))');
			}
			else
			{
				$query->where('u.block = ' . (int) $published);
			}
		}
		elseif ($published === '')
		{
			$query->where('u.block = 0');
		}

		// Filter by range state
		$range = $this->getState('filter.range');

		if ($range != '')
		{
			$query->where('voice.profile_key = "tempus.range" AND voice.profile_value = ' . $range);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('u.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');

			}
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', "up.tempus.range");
		$orderDirn = $this->state->get('list.direction', "ASC");

/*				if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}
*/
$myQuery = $db->replacePrefix((string) $query);
		return $query;
	}

	/**
	 * Get an array of data items
	 *
	 * @return mixed Array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();

		foreach ($items as $item){
			$item->voice = json_decode($item->voice);
			$item->alias = json_decode($item->alias);
		}

		return $items;
	}
}
