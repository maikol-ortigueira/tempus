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

		// The active rehearsal id field.
		$value = (int) $this->value > 0 ? (int) $this->value : '';

		// Create the modal id.
		$modalId = 'Rehearsal_' . $this->id;

		// Add the modal field script to the document head.
		HTMLHelper::_('jquery.framework');
		HTMLHelper::_('script', 'system/modal-fields.js', array('version' => 'auto', 'relative' => true));

		// Script to proxy the select modal function to the modal-fields.js file.
		if ($allowSelect)
		{
			static $scriptSelect = null;

			if (is_null($scriptSelect))
			{
				$scriptSelect = array();
			}

			if (!isset($scriptSelect[$this->id]))
			{
				Factory::getDocument()->addScriptDeclaration("
				function jSelectRehearsal_" . $this->id . "(id, title, catid, object, url, language) {
					window.processModalSelect('Rehearsal', '" . $this->id . "', id, title, catid, object, url, language);
				}
				");

				Text::script('JGLOBAL_ASSOCIATIONS_PROPAGATE_FAILED');

				$scriptSelect[$this->id] = true;
			}
		}

		// Setup variables for display.
		$linkRehearsals = 'index.php?option=com_tempus&amp;view=rehearsals&amp;layout=modal&amp;tmpl=component&amp;' . Session::getFormToken() . '=1';
		$linkRehearsal  = 'index.php?option=com_tempus&amp;view=rehearsal&amp;layout=modal&amp;tmpl=component&amp;' . Session::getFormToken() . '=1';

		if (isset($this->element['language']))
		{
			$linkRehearsals .= '&amp;forcedLanguage=' . $this->element['language'];
			$linkRehearsal  .= '&amp;forcedLanguage=' . $this->element['language'];
			$modalTitle    = Text::_('COM_TEMPUS_CHANGE_REHEARSAL') . ' &#8212; ' . $this->element['label'];
		}
		else
		{
			$modalTitle    = Text::_('COM_TEMPUS_CHANGE_REHEARSAL');
		}

		$urlSelect = $linkRehearsals . '&amp;function=jSelectRehearsal_' . $this->id;
		$urlEdit   = $linkRehearsal . '&amp;task=rehearsal.edit&amp;id=\' + document.getElementById("' . $this->id . '_id").value + \'';
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

		// The current rehearsal display field.
		$html  = '<span class="input-prepend">';
		//$html .= '<input class="input-medium" id="' . $this->id . '_name" type="text" value="' . $title . '" disabled="disabled" size="35" />';

		// Select rehearsal button
		if ($allowSelect)
		{
			$html .= '<button'
				. ' type="button"'
				. ' class="btn hasTooltip' . ($value ? ' hidden' : '') . '"'
				. ' id="' . $this->id . '_select"'
				. ' data-toggle="modal"'
				. ' data-target="#ModalSelect' . $modalId . '"'
				. ' title="' . HTMLHelper::tooltipText('COM_TEMPUS_CHANGE_REHEARSAL') . '">'
				. '<span class="icon-file" aria-hidden="true"></span> ' . Text::_('JSELECT')
				. '</button>';
		}

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

		// Edit rehearsal button
		if ($allowEdit)
		{
			$html .= '<button'
				. ' type="button"'
				. ' class="btn hasTooltip' . ($value ? '' : ' hidden') . '"'
				. ' id="' . $this->id . '_edit"'
				. ' data-toggle="modal"'
				. ' data-target="#ModalEdit' . $modalId . '"'
				. ' title="' . HTMLHelper::tooltipText('COM_TEMPUS_EDIT_REHEARSAL') . '">'
				. '<span class="icon-edit" aria-hidden="true"></span> ' . Text::_('JACTION_EDIT')
				. '</button>';
		}

		// Clear rehearsal button
		if ($allowClear)
		{
			$html .= '<button'
				. ' type="button"'
				. ' class="btn' . ($value ? '' : ' hidden') . '"'
				. ' id="' . $this->id . '_clear"'
				. ' onclick="window.processModalParent(\'' . $this->id . '\'); return false;">'
				. '<span class="icon-remove" aria-hidden="true"></span>' . Text::_('JCLEAR')
				. '</button>';
		}

		// Propagate rehearsal button
		if ($allowPropagate && count($languages) > 2)
		{
			// Strip off language tag at the end
			$tagLength = (int) strlen($this->element['language']);
			$callbackFunctionStem = substr("jSelectRehearsal_" . $this->id, 0, -$tagLength);

			$html .= '<a'
			. ' class="btn hasTooltip' . ($value ? '' : ' hidden') . '"'
			. ' id="' . $this->id . '_propagate"'
			. ' href="#"'
			. ' title="' . HTMLHelper::tooltipText('JGLOBAL_ASSOCIATIONS_PROPAGATE_TIP') . '"'
			. ' onclick="Joomla.propagateAssociation(\'' . $this->id . '\', \'' . $callbackFunctionStem . '\');">'
			. '<span class="icon-refresh" aria-hidden="true"></span>' . Text::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_BUTTON')
			. '</a>';
		}

		$html .= '</span>';

		// Select rehearsal modal
		if ($allowSelect)
		{
			$html .= HTMLHelper::_(
				'bootstrap.renderModal',
				'ModalSelect' . $modalId,
				array(
					'title'       => $modalTitle,
					'url'         => $urlSelect,
					'height'      => '400px',
					'width'       => '800px',
					'bodyHeight'  => '70',
					'modalWidth'  => '80',
					'footer'      => '<button type="button" class="btn" data-dismiss="modal">' . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>',
				)
			);
		}

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
					'closeButton' => false,
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
