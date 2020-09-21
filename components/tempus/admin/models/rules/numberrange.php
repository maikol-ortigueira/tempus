<?php
/**
 * @package    Joomla.component.client
 *
 * @created    3th February 2020
 * @author     Maikol Fustes <https://www.maikol.eu>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;

/**
 * Form Rule (Numberrange) class for the Joomla Platform.
 */
class JFormRuleNumberrange extends FormRule
{
	/**
	 * Método para comprobar valores mínimo y máximo de un campo tipo numérico.
	 * Podemos utilizar cualquier campo del archivo xml
	 * El valor validate debe ser = "numberrange"
	 *
	 * Podemos indicar el valor mínimo con min="0". Por defecto el valor será 0
	 * Podemos indicar el valor máximo con max="100". No tiene ningún valor por defecto.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 * @param   Registry           $input    An optional Registry object with the entire data set to validate against the entire form.
	 * @param   Form               $form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @since   11.1
	 */
	public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
	{
		$min = $element['min'] !=='' ? (int)$element['min'] : 0;
		$max = $element['max'];

		if ($duplicate)
		{
			return false;
		}

		return true;
	}
}
