<?php
/**
 * @version    1.0.0
 * @package    Com_Tempus
 * @author     Maikol Fustes <maikol.ortigueira@gmail.com>
 * @copyright  2020 Maikol Fustes
 * @license    Licencia Pública General GNU versión 2 o posterior. Consulte LICENSE.txt
 */

 use Joomla\CMS\Factory;
 use Joomla\CMS\Date\Date;

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Singer controller class.
 *
 * @since  1.6
 */
class TempusControllerSinger extends \Joomla\CMS\MVC\Controller\FormController
{
	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->view_list = 'singers';
		parent::__construct();
	}

	public function saveProfile($close = false)
	{
		$data = $this->input->get('jform', 'ARRAY', Array());
		$userId = $this->input->get('id');

		if ($userId && isset($data['tempus']) && count($data['tempus']))
		{
			try
			{
				$db = Factory::getDbo();

				$keys = array_keys($data['tempus']);

				foreach ($keys as &$key)
				{
					$key = 'tempus.' . $key;
					$key = $db->quote($key);
				}

				$query = $db->getQuery(true)
					->delete($db->quoteName('#__user_profiles'))
					->where($db->quoteName('user_id') . ' = ' . (int) $userId)
					->where($db->quoteName('profile_key') . ' IN (' . implode(',', $keys) . ')');
				$db->setQuery($query);
				$db->execute();

				$query = $db->getQuery(true)
					->select($db->quoteName('ordering'))
					->from($db->quoteName('#__user_profiles'))
					->where($db->quoteName('user_id') . ' = ' . (int) $userId);
				$db->setQuery($query);
				$usedOrdering = $db->loadColumn();

				$tuples = array();
				$order = 1;

				foreach ($data['tempus'] as $k => $v)
				{
					while (in_array($order, $usedOrdering))
					{
						$order++;
					}

					$tuples[] = '(' . $userId . ', ' . $db->quote('tempus.' . $k) . ', ' . $db->quote(json_encode($v)) . ', ' . ($order++) . ')';
				}

				$db->setQuery('INSERT INTO #__user_profiles VALUES ' . implode(', ', $tuples));
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				$this->_subject->setError($e->getMessage());

				return false;
			}
		}
		if ($close) 
		{
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend(), false));
		}
		else
		{
			$this->setRedirect('index.php?option=com_tempus&view=singer&layout=edit&id=' . $userId);
		}
	}

	public function saveProfileAndClose()
	{
		$this->saveProfile(true);
	}
}
