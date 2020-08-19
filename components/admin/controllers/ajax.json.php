<?php
/**
 * @version    3.2
 * @package    com_tempus
 * @author     Maikol Fustes <maikol.ortigueira@gmail.com>
 * @copyright  2019 Maikol Fustes
 * @license    Licencia Pública General GNU versión 2 o posterior. Consulte LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

// Import Joomla controllerform library
jimport('joomla.application.component.controller');

use \Joomla\CMS\Factory;
use \Joomla\CMS\Session\session;
//use \Joomla\CMS\Application\WebApplication;
use \Joomla\CMS\MVC\Controller\BaseController;

/**
 * Tempus Ajax Controller
 */
class TempusControllerAjax extends BaseController
{
	public function __construct($config)
	{
		parent::__construct($config);
		// make sure all json staff are set
		Factory::getDocument()->setMimeEncoding('application/json');
		JResponse::setHeader('Content-Disposition','attachment;filename="getajax.json"');
		JResponse::setHeader("Access-Control-Allow-Origin", "*");

		//Load the tasks
		$this->registerTask('getValues', 'ajax');
	}

	public function ajax()
	{
		$user		=	Factory::getUser();
		$jinput		=	Factory::getApplication()->input;

		// Check Token!
		$token 		=	session::getFormToken();
		$call_token =	$jinput->get('token', 0, 'ALNUM');

		if ($token == $call_token) {
			$task = $this->getTask();

			switch ($task) {
				case 'getValues':
					try
					{
						$fields 	=	explode(",", $jinput->get('fields', 0, 'STRING'));
						$table	=	str_replace('.', '_', $jinput->get('table', 'tempus', 'CMD'));
						$wherefield 	=	$jinput->get('wherefield', 'id', 'STRING');
						$wherevalue =	$jinput->get('wherevalue', 0, 'STRING');
						$condition =	$jinput->get('condition', "=", 'STRING');

						if ($wherevalue && $user->id != 0)
						{
							$result = $this->getModel('ajax')->getValues($fields, $table, $wherefield, $wherevalue, $condition);
						}
						else
						{
							$result = false;
						}
						if ($callback = $jinput->get('callback', null, 'CMD'))
						{
							echo $callback . "(" . json_encode($result) . ");";
						}
						elseif ($returnRaw)
						{
							echo json_encode($result);
						}
						else
						{
							echo "(" . json_encode($result) . ");";
						}
					}
					catch(Exception $e)
					{
						if ($callback = $jinput->get('callback', null, 'CMD'))
						{
							echo $callback . "(" . json_encode($e) . ");";
						}
						else
						{
							echo "(" . json_encode($e) . ");";
						}
					}
				break;
			}
		}
		else
		{
			if($callback = $jinput->get('callback', null, 'CMD'))
			{
				echo $callback . "(" . json_encode(false) . ");";
			}
			else
			{
				echo "(" . json_encode(false) . ");";
			}
		}
	}
}