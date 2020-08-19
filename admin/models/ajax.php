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

// Import Joomla modellist library
jimport('joomla.application.component.modellist');

use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Factory;

/**
 * Tempus Ajax Model
 */
class TempusModelAjax extends \Joomla\CMS\MVC\Model\ListModel
{
	protected $app_params;

	public function __construct()
	{
		parent::__construct();

		// get the params
		$this->app_params = ComponentHelper::getParams('com_tempus');
	}

	/**
	 * Method to retrieve values from a table
	 * @param  array  $fields     The fields to return the values from
	 * @param  string $table      The table name without the prefix
	 * @param  string $whereField The field to make the where condition
	 * @param  string $equal      The type of condition
	 * @param  string $whereValue The value of the condition field
	 * @return object             A list with the $fields
	 */
	public function getValues($fields=array(), $table, $whereField = 'id', $whereValue, $equal = '=')
	{
		// Get the connection
		$db = Factory::getDbo();

		if ($equal === '=' || $equal === '<>')
		{
			// Si $equal tiene el valor "="
			$where = $db->quoteName($whereField) . ' ' . $equal . ' ' . $db->quote($whereValue);
		}
		elseif ($equal === 'find_in_set') {
			// Si tenemos que buscar un valor en un campo que tiene los datos separados por comas
			$where = ' FIND_IN_SET(' .$whereValue . ',' . $whereField . ')';
		}

		$table = $db->quoteName('#__' . $table);
		// Create a new query object
		$query = $db->getQuery(true);

		$query->select($db->quoteName($fields));

		$query->from($table);
		$query->where($where);

		$db->setQuery($query);


		// Load the results
		$results = $db->loadObjectList();

		return $results;
	}

	/**
	 * Method get options to a select list
	 * @param  array 	$fields     fields that should be retrieved from table. The first value must be the value_field and the second must be the text_field
	 * @param  string $table      table name to look into. Doted separated
	 * @param  string $whereField the conditional table field
	 * @param  string $condition  the condition
	 * @param  string $whereValue the value to look for in the whereField
	 * @return string             html options
	 */
	public function getOptions($fields, $table, $whereField, $condition = '=', $whereValue)
	{
		// Retrieve the values from the table
		$results = $this->getValues($fields, $table, $whereField, $condition, $whereValue);

		$html = '<option value=""></option>';

		// Supose that value_field is $fields[0] and text_field is $fields[1]
		foreach ($results as $result) {
			$html.= '<option value="'.$result->{$fields[0]}.'">'.$result->{$fields[1]}.'</option>';
		}

		return $html;
	}

	public function getValue($field, $table, $whereField = 'id', $equal = '=', $whereValue)
	{
		// Get the connection
		$db = Factory::getDbo();

		$where = $db->quoteName($whereField) . ' ' . $equal . ' ' . $db->quote($whereValue);
		$table = $db->quoteName('#__' . $table);
		// Create a new query object
		$query = $db->getQuery(true);

		$query->select($db->quoteName($field));

		$query->from($table);
		$query->where($where);

		$db->setQuery($query);

		// Load the result
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * Método que prepara los datos para enviar un correo electrónico
	 * @param  array  $emailTo       	Valores de los ids de los receptores del email
	 * @param  string $emailToTable  	Nombre de la tabla en donde se buscarán los valores de $emailTo
	 * @param  string $emailTemplate 	Nombre de la plantilla de email.
	 * @param  string $attachment    	Ruta del fichero que deseemos adjuntar al correo.
	 * @return void
	 */
	public static function sendEmail($data)
	{
		switch ($data['email_template']) {
			case 'send_to_translate':
				$emailData = EmailHelper::getToTranslateEmailData($data);

				break;

			default:
				# code...
				break;
		}
		// Configurar los datos del emisor del correo
		$params = ComponentHelper::getParams('com_tempus');

		$sender[] = $params->get('from_email');
		$sender[] = $params->get('from_name');

		/****************** ACUÉRDATE DE BORRAR ESTAS LINEAS ******************/
		echo '<pre>'; print_r($emailData);echo '</pre>';jexit();
		// Enviamos el correo
		$mailStatus = EmailHelper::sendEmail($emailData['emailsTo'], $sender, $emailData['body'], $subject = "", $html = true);

		return $mailStatus;
	}
}