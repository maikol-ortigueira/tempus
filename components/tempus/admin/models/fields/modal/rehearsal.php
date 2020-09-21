<?php
/**
 * @version    1.0.0
 * @package    Com_Tempus
 * @author     Maikol Fustes <maikol.ortigueira@gmail.com>
 * @copyright  2020 Maikol Fustes
 * @license    Licencia Pública General GNU versión 2 o posterior. Consulte LICENSE.txt
 */

use \Joomla\CMS\Form\FormField;
use \Joomla\CMS\Language\LanguageHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Session\Session;
use \Joomla\CMS\Factory;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

/**
 * Supports a modal rehearsal picker.
 *
 * @since  1.6
 */
class JFormFieldModal_Rehearsal extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $type = 'Modal_Rehearsal';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		$allowNew       = ((string) $this->element['new'] == 'true');
		$allowEdit      = ((string) $this->element['edit'] == 'true');
		$allowClear     = ((string) $this->element['clear'] != 'false');
		$allowSelect    = ((string) $this->element['select'] != 'false');
		$allowPropagate = ((string) $this->element['propagate'] == 'true');

		$languages = LanguageHelper::getContentLanguages(array(0, 1));

		// Load language
		Factory::getLanguage()->load('com_tempus', JPATH_ADMINISTRATOR);

		// The concert id
		$input = Factory::getApplication()->input;
		$concert_id = $input->get('id', 0, 'int');

		// Preparar los datos de la tabla
		$array = TempusHelper::getListValues(['start_date', 'end_date', 'id'], '#__tempus_rehearsals', 'concert_id', $concert_id);

		$rehearsals = array();

		foreach ($array as $key => $value) {
			$rehearsals[$key]['date'] = HTMLHelper::date($value['start_date'], Text::_('COM_TEMPUS_LIST_DATE_FORMAT'));
			$rehearsals[$key]['start']= HTMLHelper::date($value['start_date'], Text::_('COM_TEMPUS_LIST_TIME_FORMAT'));
			$rehearsals[$key]['end']= HTMLHelper::date($value['end_date'], Text::_('COM_TEMPUS_LIST_TIME_FORMAT'));
			$rehearsals[$key]['id']= $value['id'];
		}

		// The active rehearsal id field.
		$value = (int) $this->value > 0 ? (int) $this->value : '';

		// Create the modal id.
		$modalId = 'Rehearsal_' . $this->id;

		// Add the modal field script to the document head.
		HTMLHelper::_('jquery.framework');
		HTMLHelper::_('script', 'system/modal-fields.js', array('version' => 'auto', 'relative' => true));

		// Setup variables for display.
		$linkRehearsal  = 'index.php?option=com_tempus&amp;view=rehearsal&amp;layout=modal&amp;tmpl=component&amp;' . Session::getFormToken() . '=1';
		$linkEditRehearsal  = 'index.php?option=com_tempus&amp;view=rehearsal&amp;' . Session::getFormToken() . '=1';


		if (isset($this->element['language']))
		{
			$linkRehearsal  .= '&amp;forcedLanguage=' . $this->element['language'];
			$linkEditRehearsal  .= '&amp;forcedLanguage=' . $this->element['language'];
			$modalTitle    = Text::_('COM_TEMPUS_CHANGE_REHEARSAL') . ' &#8212; ' . $this->element['label'];
		}
		else
		{
			$modalTitle    = Text::_('COM_TEMPUS_CHANGE_REHEARSAL');
		}

		$urlEdit   = $linkEditRehearsal . '&amp;task=rehearsal.edit&amp;id=';
		$urlNew    = $linkRehearsal . '&amp;task=rehearsal.add';

		if ($value)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('title'))
				->from($db->quoteName('#__tempus_rehearsals'))
				->where($db->quoteName('id') . ' = ' . (int) $value);
			$db->setQuery($query);

			try
			{
				$title = $db->loadResult();
			}
			catch (RuntimeException $e)
			{
				JError::raiseWarning(500, $e->getMessage());
			}
		}

		$title = empty($title) ? Text::_('COM_TEMPUS_SELECT_AN_REHEARSAL') : htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		$html = '<fieldset>';
//		$html .= '<legend>' . Text::_('COM_TEMPUS_CONCERT_REHEARSALS_FIELDSET');
//		$html .= '</legend>';
		$html .= '</fieldset>';

		// The rehearsal table list
		if ((isset($rehearsals)) && !empty($rehearsals))
		{
			// Comienzo de la tabla
			$html .= '<table class="table table-striped">';
			// Cabecera
			$html .= '<thead><tr><th>'
				. Text::_('COM_TEMPUS_CONCERT_REHEARSALS_LIST_DATE_TITLE')
				. '</th><th>'
				. Text::_('COM_TEMPUS_CONCERT_REHEARSALS_LIST_START_TITLE')
				. '</th><th>'
				. Text::_('COM_TEMPUS_CONCERT_REHEARSALS_LIST_END_TITLE')
				. '</th></tr></thead>';
		}
		else
		{
			$html .= Text::_('<p>No hay ensayos asignados todavía para este concierto.</p><p>Pulsa el botón crear para añadir.</p>');
			$html .= '<p></p>';
		}
		// El cuerpo de la tabla
		// Etiqueta de inicio de cuerpo
		$html .= '<tbody>';

		// Los ensayos
		foreach ($rehearsals as $rehearsal ) {

			$option = $input->get('option');
			$view = $input->get('view');
			$id = $input->get('id');
			$layout = $input->get('layout');

			$return = '&return=' . urlencode(base64_encode('index.php?option='.$option.'&view='.$view.'&id='.$id.'&layout='.$layout));

			// Inicio de fila
			$html .= '<tr>';
			// Fecha del ensayo
			if ($allowEdit){
				$html .= '<td>'
					. '<a'
					. ' href="' . $urlEdit . $rehearsal['id'] . $return . '"'
					. ' id="' . $rehearsal['id'] . '_edit"'
					. ' title="' . HTMLHelper::tooltipText('COM_TEMPUS_EDIT_REHEARSAL') . '">'
					. '<span class="icon-edit" aria-hidden="true"></span> '
					. '<span class="label label-important">' . $rehearsal['date'] . '</span>'
					. '</a>'
					. '</td>'
				;
			}
			else
			{
				$html .= '<td>'
					. '<span class="badge badge-important">' . $rehearsal['date'] . '</span>'
					. '</td>'
				;
			}
			// Hora de inicio
			$html .= '<td>'
				. $rehearsal['start']
				. '</td>';
			// Hora de finalización
			$html .= '<td>'
				. $rehearsal['end']
				. '</td>';
			// Cierre de fila
			$html .= '</tr>';
		}

		// Cierre del cuerpo
		$html .= '</tbody>';
		// Cierre de la tabla
		$html .= '</table>';
		$html .= '<hr>';

		// The current rehearsal display field.
		$html  .= '<span class="input">';

		// New rehearsal button
		if ($allowNew)
		{
			$html .= '<button'
				. ' type="button"'
				. ' class="btn hasTooltip' . ($value ? ' hidden' : '') . '"'
				. ' id="' . $this->id . '_new"'
				. ' data-toggle="modal"'
				. ' data-target="#ModalNew' . $modalId . '"'
				. ' title="' . HTMLHelper::tooltipText('COM_TEMPUS_NEW_REHEARSAL') . '">'
				. '<span class="icon-new" aria-hidden="true"></span> ' . Text::_('JACTION_CREATE')
				. '</button>';
		}

		$html .= '</span>';

		// New rehearsal modal
		if ($allowNew)
		{
			$html .= HTMLHelper::_(
				'bootstrap.renderModal',
				'ModalNew' . $modalId,
				array(
					'title'       => Text::_('COM_TEMPUS_NEW_REHEARSAL'),
					'backdrop'    => 'static',
					'keyboard'    => false,
					'closeButton' => false,
					'url'         => $urlNew,
					'height'      => '400px',
					'width'       => '800px',
					'bodyHeight'  => '70',
					'modalWidth'  => '80',
					'footer'      => '<button type="button" class="btn"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'rehearsal\', \'cancel\', \'item-form\'); return false;">'
							. Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
							. '<button type="button" class="btn btn-primary"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'rehearsal\', \'save\', \'item-form\'); return false;">'
							. Text::_('JSAVE') . '</button>'
							. '<button type="button" class="btn btn-success"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'rehearsal\', \'apply\', \'item-form\'); return false;">'
							. Text::_('JAPPLY') . '</button>',
				)
			);
		}

		// Edit rehearsal modal
		if ($allowEdit)
		{
			$html .= HTMLHelper::_(
				'bootstrap.renderModal',
				'ModalEdit' . $modalId,
				array(
					'title'       => Text::_('COM_TEMPUS_EDIT_REHEARSAL'),
					'backdrop'    => 'static',
					'keyboard'    => false,
					'closeButton' => true,
					'url'         => $urlEdit,
					'height'      => '400px',
					'width'       => '800px',
					'bodyHeight'  => '70',
					'modalWidth'  => '80',
					'footer'      => '<button type="button" class="btn"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'rehearsal\', \'cancel\', \'item-form\'); return false;">'
							. Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
							. '<button type="button" class="btn btn-primary"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'rehearsal\', \'save\', \'item-form\'); return false;">'
							. Text::_('JSAVE') . '</button>'
							. '<button type="button" class="btn btn-success"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'rehearsal\', \'apply\', \'item-form\'); return false;">'
							. Text::_('JAPPLY') . '</button>',
				)
			);
		}

		// Note: class='required' for client side validation.
		$class = $this->required ? ' class="required modal-value"' : '';

		$html .= '<input type="hidden" id="' . $this->id . '_id" ' . $class . ' data-required="' . (int) $this->required . '" name="' . $this->name
			. '" data-text="' . htmlspecialchars(Text::_('COM_TEMPUS_SELECT_AN_REHEARSAL'), ENT_COMPAT, 'UTF-8') . '" value="' . $value . '" />';

		return $html;
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   3.4
	 */
	protected function getLabel()
	{
		return str_replace($this->id, $this->id . '_id', parent::getLabel());
	}
}
