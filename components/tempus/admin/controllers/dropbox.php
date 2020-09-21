<?php
/**
 * @version    1.0.0
 * @package    Com_Tempus
 * @author     Maikol Fustes <maikol.ortigueira@gmail.com>
 * @copyright  2020 Maikol Fustes
 * @license    Licencia Pública General GNU versión 2 o posterior. Consulte LICENSE.txt
 */

use \Joomla\CMS\MVC\Controller\BaseController;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Session\Session;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Table\Table;

// No direct access
defined('_JEXEC') or die;

JLoader::register('DropboxHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/dropbox.php');

/**
 * Dropbox controller class.
 *
 * @since  1.6
 */
class TempusControllerDropbox extends BaseController
{
	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct()
	{
		parent::__construct();
	}

	public function connect()
	{
		//Session::checkToken() or die('Invalid Token');
		if (!Factory::getUser()->authorise('core.admin', 'com_tempus'))
		{
			$msg = Text::_( 'No tienes suficientes permisos para realizar esta acción' );
			$link = 'index.php?option=com_config&view=component&component=com_tempus#file_system';
			$this->setRedirect($link, $msg);
			return;
		}

		$params = $this->getParams();
		//get the token
		$response = DropboxHelper::oauth2Token($params);
		if($response)
		{
			$response = json_decode($response);

			$access_token = $response->access_token;

			$new_params = array();
			$new_params['oauth2Token_dropbox'] = $access_token;

			$this->setParams($new_params);

			$link = 'index.php?option=com_config&view=component&component=com_tempus#file_system';
			$this->setRedirect($link);
		}
		else
		{
			JError::raiseWarning(100, Text::_('Ha habido un error en la solicitud del token a dropbox'));
			$link = 'index.php?option=com_config&view=component&component=com_tempus#file_system';
			$this->setRedirect($link);
		}
	}

	public function disconnect()
	{
		//JSession::checkToken('get') or die('Invalid Token');
		if (!Factory::getUser()->authorise('core.admin', 'com_amazons3'))
		{
			$msg = JText::_( 'No tienes suficientes permisos para realizar esta acción' );
			$link = 'index.php?option=com_config&view=component&component=com_tempus#file_system';
			$this->setRedirect($link, $msg);
			return;

		}

		$params = $this->getParams();
		$token = $params['code'];
		if(empty($token))
		{
				//no sense in doing anything further
			$link = 'index.php?option=com_tempus';
			$this->setRedirect($link);
			return;
		}


		DropboxHelper::authTokenRevoke($token);

		// Borrar los datos de token de la configuración global
		$new_params = array();
		$new_params['oauth2Token_dropbox'] = "";
		$new_params['token_dropbox'] = "";

		$this->setParams($new_params);

		$link = 'index.php?option=com_config&view=component&component=com_tempus#file_system';
		$this->setRedirect($link);

	}

	protected function getParams()
	{
		$params = ComponentHelper::getParams('com_tempus');

		$return = array();
		$return['client_id'] = $params->get('pbk_dropbox');
		$return["grant_type"] = 'authorization_code';
		$return['client_secret'] = $params->get('prk_dropbox');
		$return['code'] = $params->get('token_dropbox');

		return $return;
	}

	/**
	 * Método para asignar y guardar valores globales del componente
	 *
	 * @param  $options 	array 	Variables a modificar ['variable a modificar'] => ['nuevo valor de la variable']
	 *
	 * @return void
	 */
	protected function setParams($options)
	{
		// Establecer el valor de la variable global
		$comp_params = ComponentHelper::getParams('com_tempus');

		foreach ($options as $variable => $new_value) {
			$comp_params->set($variable, $new_value);
		}

		// Guardar los datos de las variables globales
		$componentId = ComponentHelper::getComponent('com_tempus')->id;
		// Instanciar la tabla extensions
		$table = Table::getInstance('extension');
		$table->load($componentId);

		$table->bind(array('params' => $comp_params->toString()));

		// Check for error
		if (!$table->check())
		{
			$this->setError(Text::_('Ha habido problemas en la comprobación de la variables globales pasadas.'));
			return false;
		}

		// Save new component params to database
		if (!$table->store())
		{
			$this->setError(Text::_('Ha habido un error guardando las variables globales nuevas'));
			return false;
		}

		return true;
	}
}
