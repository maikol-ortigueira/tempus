<?php
/**
 * @version    1.0.0
 * @package    Com_Tempus
 * @author     Maikol Fustes <maikol.ortigueira@gmail.com>
 * @copyright  2020 Maikol Fustes
 * @license    Licencia Pública General GNU versión 2 o posterior. Consulte LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Tempus Component Route Helper.
 *
 * @since  1.5
 */
abstract class TempusHelperRoute
{
	/**
	 * Get the song route.
	 *
	 * @param   integer  $id        The route of the song item.
	 * @param   integer  $catid     The category ID.
	 * @param   integer  $language  The language code.
	 * @param   string   $layout    The layout value.
	 *
	 * @return  string  The song route.
	 *
	 * @since   1.5
	 */
	public static function getSongRoute($id, $catid = 0, $language = 0, $layout = null)
	{
		// Create the link
		$link = 'index.php?option=com_tempus&view=song&id=' . $id;

		if ((int) $catid > 1)
		{
			$link .= '&catid=' . $catid;
		}

		if ($language && $language !== '*' && JLanguageMultilang::isEnabled())
		{
			$link .= '&lang=' . $language;
		}

		if ($layout)
		{
			$link .= '&layout=' . $layout;
		}

		return $link;
	}

	/**
	 * Get the category route.
	 *
	 * @param   integer  $catid     The category ID.
	 * @param   integer  $language  The language code.
	 * @param   string   $layout    The layout value.
	 *
	 * @return  string  The song route.
	 *
	 * @since   1.5
	 */
	public static function getCategoryRoute($catid, $language = 0, $layout = null)
	{
		if ($catid instanceof JCategoryNode)
		{
			$id = $catid->id;
		}
		else
		{
			$id = (int) $catid;
		}

		if ($id < 1)
		{
			return '';
		}

		$link = 'index.php?option=com_tempus&view=category&id=' . $id;

		if ($language && $language !== '*' && JLanguageMultilang::isEnabled())
		{
			$link .= '&lang=' . $language;
		}

		if ($layout)
		{
			$link .= '&layout=' . $layout;
		}

		return $link;
	}

	/**
	 * Get the form route.
	 *
	 * @param   integer  $id  The form ID.
	 *
	 * @return  string  The song route.
	 *
	 * @since   1.5
	 */
	public static function getFormRoute($id)
	{
		return 'index.php?option=com_tempus&task=song.edit&a_id=' . (int) $id;
	}

	/**
	 * Get the concert route.
	 *
	 * @param   integer  $id        The route of the concert item.
	 * @param   integer  $catid     The category ID.
	 * @param   integer  $language  The language code.
	 * @param   string   $layout    The layout value.
	 *
	 * @return  string  The concert route.
	 *
	 * @since   1.5
	 */
	public static function getConcertRoute($id, $catid = 0, $language = 0, $layout = null)
	{
		// Create the link
		$link = 'index.php?option=com_tempus&view=concert&id=' . $id;

		if ((int) $catid > 1)
		{
			$link .= '&catid=' . $catid;
		}

		if ($language && $language !== '*' && JLanguageMultilang::isEnabled())
		{
			$link .= '&lang=' . $language;
		}

		if ($layout)
		{
			$link .= '&layout=' . $layout;
		}

		return $link;
	}
	/**
	 * Get the rehearsal route.
	 *
	 * @param   integer  $id        The route of the rehearsal item.
	 * @param   integer  $catid     The category ID.
	 * @param   integer  $language  The language code.
	 * @param   string   $layout    The layout value.
	 *
	 * @return  string  The rehearsal route.
	 *
	 * @since   1.5
	 */
	public static function getRehearsalRoute($id, $catid = 0, $language = 0, $layout = null)
	{
		// Create the link
		$link = 'index.php?option=com_tempus&view=rehearsal&id=' . $id;

		if ((int) $catid > 1)
		{
			$link .= '&catid=' . $catid;
		}

		if ($language && $language !== '*' && JLanguageMultilang::isEnabled())
		{
			$link .= '&lang=' . $language;
		}

		if ($layout)
		{
			$link .= '&layout=' . $layout;
		}

		return $link;
	}
	/*###getViewRoute-new-view###*/
}
