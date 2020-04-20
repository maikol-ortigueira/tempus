<?php
/**
 * @version    1.0.0
 * @package    Com_Tempus
 * @author     Maikol Fustes <maikol.ortigueira@gmail.com>
 * @copyright  2020 Maikol Fustes
 * @license    Licencia Pública General GNU versión 2 o posterior. Consulte LICENSE.txt
 */

 use \Joomla\CMS\Factory;
 use \Joomla\CMS\Form\FormField;

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

/**
 * Supports an HTML select list of categories
 *
 * @since  1.6
 */
class JFormFieldAppendText extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'text';

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The field input markup.
	 *
	 * @since    1.6
	 */
	public function getInput()
	{
		$class = $this->element['class'];

		$html = array();

		// Initialize variables.
		$html[] = '<div class="'. $class. '">';
		$html[] = '<p id="' . $this->id . '" name="' . $this->name . '">' . $this->value . '</p>';
		$html[] = '</div>';

		return implode($html);
	}

	public function getLabel()
	{
		return '';
	}
}
