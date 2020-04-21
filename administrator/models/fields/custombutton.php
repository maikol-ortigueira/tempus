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

use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Language\Text;

/**
 * Class to generate a button for use in forms
 *
 * Optional fields are:
 * button_text 		Text that will be shown in the button.
 * button_icon 		Button icon. see https://getbootstrap.com/2.3.2/base-css.html#icons Just add the name from "icon-".
 * button_tooltip 	Message that will be shown when passing the mouse over the button.
 * button_class 	Button type class. The options are primary, info, success, warning, danger, inverse, link. See https://getbootstrap.com/2.3.2/base-css.html#buttons.
 * button_size 		Size of the button. The options are large, small, mini.
 * button_href 		URL where the button sends us. Default #.
 * href_cond		Is the href conditioned by other field?. Where from? Options: config, TODO more options
 * cond_field		Field where the conditional value comes from
 * translate 		If you want to translate the tooltip and button_text strings this attribute must have the value "true".
 * onclick 			Javascript action that is executed when the button is pressed.
 * message 			Message to use on appended div. Used to show on conditional field only
 * message_append 	Message to use on sprintf of message
 * message_class 	Message text class. Default 'none'. See https://getbootstrap.com/2.3.2/base-css.html#emphasis
 * display_button 	Display state of the button, by default style="display:true"
 * desplay_text 	Display state of the message, by default style="display: none"
 *
 * @since  1.6
 */
class JFormFieldCustombutton extends \Joomla\CMS\Form\FormField
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'custombutton';

	protected $button_text;
	protected $button_icon;
	protected $button_tooltip;
	protected $button_class;
	protected $button_size;
	protected $button_href;
	protected $href_cond;
	protected $cond_field;
	protected $onclick;
	protected $translate;
	protected $class;
	protected $message;
	protected $message_append;
	protected $message_class;
	protected $display_button;
	protected $display_text;

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The field input markup.
	 *
	 * @since    1.6
	 */
	protected function getInput()
	{

		// Text to add to the button
		$this->button_text = $this->getAttribute('button_text');

		// button icon
		$this->button_icon = $this->getAttribute('button_icon');

		// Button tooltip
		$this->button_tooltip = $this->getAttribute('button_tooltip');

		// button type class
		$this->button_class = $this->getAttribute('button_class');

		// Button size
		$this->button_size = $this->getAttribute('button_size');

		// Button link URL
		$this->button_href = $this->getAttribute('button_href');

		// Condition source
		$this->href_cond = $this->getAttribute('href_cond');

		// Condition field source
		$this->cond_field = $this->getAttribute('cond_field');

		// Function onclick
		$this->onclick = $this->getAttribute('onclick');

		// Translate the string
		$this->translate = $this->getAttribute('translate');

		// Button class
		$this->class = $this->getAttribute('class');

		// Append text Message
		$this->message = $this->getAttribute('message');

		// Append text Message append text
		$this->message_append = $this->getAttribute('message_append');

		// Append text class
		$this->message_class = $this->getAttribute('message_class');

		// Display button
		$this->display_button = $this->getAttribute('display_button');

		// Display text
		$this->display_text = $this->getAttribute('display_text');

		// Source generation
		$class = 'btn';

		// If we have a button type
		if ($this->button_class != "") {
			$class .= ' btn-' . $this->button_class;
		}

		// Do we have a class?
		if ($this->class != "") {
			$class .= ' ' . $this->class;
		}

		// Do we have a button size?
		if ($this->button_size != "") {
			$class .= ' btn-' . $this->button_size;
		}

		$href_link = "";
		// Prepare the conditional href
		if ($this->href_cond == "config")
		{
			$params = ComponentHelper::getParams('com_tempus');
			$href_link = $params->get($this->cond_field);
		}

		// HTML beginning
		$html = '<a';

		// Display style. By default display true
		$html .= (empty($this->display_button)) ? '' : ' style="display:' . $this->display_button . '"' ;

		// Button Id
		$html .= ' id="' . $this->id . '"';


		// Do we have a tooltip?
		if ($this->button_tooltip != "")
		{
			// Add hasTooltip class
			$class .= ' hasTooltip';

			if ($this->translate)
			{
				$this->button_tooltip = Text::_($this->button_tooltip);
			}

			$html .= ' data-toggle="tooltip"';
			$html .= ' title="' . $this->button_tooltip . '"';
		}

		// Class data
		$html .= ' class="' . $class . '"';

		// URL data
		$href = '#';

		// Do we have a href link?
		if ($this->button_href != "")
		{
			$href = $this->button_href . $href_link;
		}

		$html .= ' href="' . $href . '"';

		// Do we have onclick funtion?
		if ($this->onclick != "")
		{
			$html .= ' onclick="return ' . $this->onclick . '"';
		}

		// Close <a> tag
		$html .= '>';


		// Button icon
		if ($this->button_icon != "")
		{
			$html .= '<i class="icon-' . $this->button_icon . '"></i>';
		}

		// Button text
		if ($this->button_text != "")
		{
			if ($this->translate == true)
			{
				$this->button_text = Text::_($this->button_text);
			}
			$html .= ' ' . $this->button_text;
		}

		$msgClass="";
		// Message class
		if (!empty($this->message_class)) {
			$preClass = (substr($this->msg_class, 0, 5) === "text-") ? '' : 'text-' ;
			$msgClass = $preClass . $this->message_class;
		}

		$display_text = (empty($this->display_text)) ? 'none' : $this->display_text ;

		// </a> closing tag
		$html .= '</a>';
		// Place to append a text
		$html .= '<div id="' . $this->id . '_text" class="'. $msgClass .'" style="display:' . $display_text . '">';
		$html .= Text::sprintf($this->message, $this->message_append);
		$html .= '</div>';

		return $html;
	}

	protected function getLabel()
	{
		return false;
	}
}
