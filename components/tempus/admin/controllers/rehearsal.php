<?php
/**
 * @version    1.0.0
 * @package    Com_Tempus
 * @author     Maikol Fustes <maikol.ortigueira@gmail.com>
 * @copyright  2020 Maikol Fustes
 * @license    Licencia Pública General GNU versión 2 o posterior. Consulte LICENSE.txt
 */

 use Joomla\CMS\MVC\Controller\FormController;
 use Joomla\CMS\Factory;
 use Joomla\CMS\Router\Route;

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Rehearsal controller class.
 *
 * @since  1.6
 */
class TempusControllerRehearsal extends FormController
{
	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->view_list = 'rehearsals';
		parent::__construct();
	}

	public function save($key = null, $urlVar = null)
	{
		$app = Factory::getApplication();

		parent::save($key, $urlVar);
	}

	public function sendEmails()
	{
		$data = $this->input->get('jform', array(), 'ARRAY');

		foreach ($data['convocation'] as $voice => $ids)
		{
			foreach ($ids as $id) {
				$bodyData = array();
				$profile = TempusHelper::getUserProfile($id);
				$contact = Factory::getUser($id);

				// Get the songs
				if ($data['songs_id'] && count($data['songs_id']) > 0)
				{
					$bodyData['song_names'] = '<ul>';
					$bodyData['song_files'] = '<ul>';
					foreach ($data['songs_id'] as $song) {
						$song = TempusHelper::get('song', $song);
						$bodyData['song_names'] .= '<li>' . $song->title . ' - ' . $song->author . '</li>';
					}

					$bodyData['song_names'] .= '</ul>';
					$bodyData['song_files'] .= '</ul>';
				}

				$bodyData['name'] = $contact->name;
				$bodyData['email'] = $contact->email;
				$bodyData['alias'] = $profile->alias;
				$bodyData['surname'] = $profile->surname;
				$bodyData['voice'] = $profile->range;
				$bodyData['start_time'] = $data['start_hour'] . ':' . $data['start_minute'] . ' ' . $data['start_ampm'];
				$bodyData['end_time'] = $data['end_hour'] . ':' . $data['end_minute'] . ' ' . $data['end_ampm'];
				$bodyData['date'] = $data['rehearsal_date'];
				$bodyData['location'] = ''; // Pendiente de meterlo en el formulario de ensayos la localización del ensayo
				$bodyData['concert'] = TempusHelper::getField($data['concert_id'], '#__tempus_concerts', 'title')[0];
				$bodyData['note'] = $data['note'];

			}

			$singer = array();
			$singer['contact_message'] = EmailHelper::setBody($bodyData);
			$singer['contact_subject'] = EmailHelper::setSubject($bodyData);

		}


		$this->setRedirect(
			Route::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_item
				. $this->getRedirectToItemAppend($data['id']), false
			)
		);

	}
}
