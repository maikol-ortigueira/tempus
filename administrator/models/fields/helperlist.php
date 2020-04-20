<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Tempus
 * @author     Maikol Fustes <maikol@maikol.eu>
 * @copyright  2020 Maikol Fustes
 * @license    Licencia Pública General GNU versión 2 o posterior. Consulte LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

use \Joomla\CMS\Language\Text;

/**
 * Clase que crea un listado seleccionable de valores en función del campo helper_getter
 * Busca los valores en la función get{helper_getter} del archivo tempus.php dentro de la carpeta helpers de la administración
 * Para añadir mas getter hay que crear una función estática pública en el archivo con el nombre get{nombre} que devuelva los datos en formato array
 * Si los datos del array no tienen índice se utilizará automáticamente índice numérico, siendo el cero el primer índice
 * Getters creados en el archivo hasta el momento: voices
 *
 * @since  1.6
 */
class JFormFieldHelperList extends \Joomla\CMS\Form\FormField
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'helperlist';
	protected $helper_getter;
	protected $translate;

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The field input markup.
	 *
	 * @since    1.6
	 */

	protected function getInput()
	{
		// helper getter
		$this->helper_getter = $this->getAttribute('helper_getter');

		// get the translatable condition
		$this->translate = $this->getAttribute('translate');

		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$attr .= $this->multiple ? ' multiple' : '';
		$attr .= $this->required ? ' required aria-required="true"' : '';
		$attr .= $this->autofocus ? ' autofocus' : '';

		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ((string) $this->readonly == '1' || (string) $this->readonly == 'true' || (string) $this->disabled == '1'|| (string) $this->disabled == 'true')
		{
			$attr .= ' disabled="disabled"';
		}

		// Initialize JavaScript field attributes.
		$attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';

		// Get the field options.
		$options = (array) $this->getOptions();

		// Create a read-only list (no name) with hidden input(s) to store the value(s).
		if ((string) $this->readonly == '1' || (string) $this->readonly == 'true')
		{
			$html[] = JHtml::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $this->value, $this->id);

			// E.g. form field type tag sends $this->value as array
			if ($this->multiple && is_array($this->value))
			{
				if (!count($this->value))
				{
					$this->value[] = '';
				}

				foreach ($this->value as $value)
				{
					$html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"/>';
				}
			}
			else
			{
				$html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"/>';
			}
		}
		else
		// Create a regular list passing the arguments in an array.
		{
			$listoptions = array();
			$listoptions['option.key'] = 'value';
			$listoptions['option.text'] = 'text';
			$listoptions['list.select'] = $this->value;
			$listoptions['id'] = $this->id;
			$listoptions['list.translate'] = false;
			$listoptions['option.attr'] = 'optionattr';
			$listoptions['list.attr'] = trim($attr);

			$html[] = JHtml::_('select.genericlist', $options, $this->name, $listoptions);
		}

		return implode($html);
	}

	protected function getOptions()
	{
		// Initialize variables.
		$options = array();
		$array = ucfirst($this->helper_getter);

		$getter = 'get' . $array;
		$optiones = TempusHelper::$getter();

		$optiones = $this->helper_getter === 'emailtemplates' ? array_keys($optiones) : $optiones;

		foreach ($optiones as $value => $text) {
			if ($this->translate)
			{
				$text = 'COM_TEMPUS_' . strtoupper($this->helper_getter) . '_' . strtoupper($text);
			}
			$option['value'] = $value;
			$option['text'] = Text::_($text);

			$options[$value] = (object) $option;
		}

		if ($this->helper_getter === 'emailtemplates')
		{
			$dbData = TempusHelper::getFieldValues('#__tempus_email_templates', array('title'), '1', 'state');

			foreach ($dbData as $key => $value) {
				unset($options[$value->title]);
			}

		}


		return $options;
	}
}