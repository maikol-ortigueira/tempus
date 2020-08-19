<?php
/**
 * @version    1.0.0
 * @package    Com_Tempus
 * @author     Maikol Fustes <maikol.ortigueira@gmail.com>
 * @copyright  2020 Maikol Fustes
 * @license    Licencia Pública General GNU versión 2 o posterior. Consulte LICENSE.txt
 */

 use \Joomla\CMS\MVC\Controller\FormController;
 use \Joomla\CMS\Factory;

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Rehearsal controller class.
 *
 * @since  1.6
 */
class TempusControllerRehearsal extends FormController
{
	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->view_list = 'rehearsals';
		parent::__construct();
	}

	public function save($key = null, $urlVar = null)
	{
		$app = JFactory::getApplication();

		parent::save($key, $urlVar);
	}

	/*###newMethod###*/
}
