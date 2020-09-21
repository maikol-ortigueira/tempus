<?php

/**
 * @version    1.0.0
 * @package    Com_Tempus
 * @author     Maikol Fustes <maikol.ortigueira@gmail.com>
 * @copyright  2020 Maikol Fustes
 * @license    Licencia Pública General GNU versión 2 o posterior. Consulte LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;

/**
 * Email helper.
 *
 * @since  1.6
 */
class EmailHelper
{

    /**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   array     $data                  The data to send in the email.
	 * @param   stdClass  $contact               The user information to send the email to
	 *
	 * @return  boolean  True on success sending the email, false on failure.
	 *
	 * @since   1.6.4
	 */
	public static function sendEmail($data, $contact, $copy_email_activated)
	{
		$app = Factory::getApplication();
		$params = ComponentHelper::getParams('com_tempus');

		if ($contact->email_to == '' && $contact->user_id != 0)
		{
			$contact_user      = User::getInstance($contact->user_id);
			
			$contact->email_to = $contact_user->get('email');
		}

		$mailfrom = $params->get('mailfrom');
		$fromname = $params->get('fromname');
		$sitename = $app->get('sitename');

		$name    = $data['contact_name'];
		$email   = PunycodeHelper::emailToPunycode($data['contact_email']);
		$subject = $data['contact_subject'];
		$body    = $data['contact_message'];

		// Prepare email body
		$prefix = Text::sprintf('COM_CONTACT_ENQUIRY_TEXT', Uri::base());
		$body   = $prefix . "\n" . $name . ' <' . $email . '>' . "\r\n\r\n" . stripslashes($body);

		$mail = Factory::getMailer();
		$mail->addRecipient($contact->email_to);
		$mail->addReplyTo($email, $name);
		$mail->setSender(array($mailfrom, $fromname));
		$mail->setSubject($sitename . ': ' . $subject);
		$mail->setBody($body);
		$sent = $mail->Send();

		// If we are supposed to copy the sender, do so.

		// Check whether email copy function activated
		if ($copy_email_activated == true && !empty($data['contact_email_copy']))
		{
			$copytext    = Text::sprintf('COM_CONTACT_COPYTEXT_OF', $contact->name, $sitename);
			$copytext    .= "\r\n\r\n" . $body;
			$copysubject = Text::sprintf('COM_CONTACT_COPYSUBJECT_OF', $subject);

			$mail = Factory::getMailer();
			$mail->addRecipient($email);
			$mail->addReplyTo($email, $name);
			$mail->setSender(array($mailfrom, $fromname));
			$mail->setSubject($copysubject);
			$mail->setBody($copytext);
			$sent = $mail->Send();
		}

		return $sent;
	}
	
	public static function setBody($data, $type = 'rehearsal')
	{
		$params = ComponentHelper::getParams('com_tempus');

		$body = $params->get($type . '_body');

		foreach ($data as $key => $value) {
			$replace = '{' . $key . '}';
			$body = str_replace($replace, $value, $body);
		}

		return $body;
	}

	public static function setSubject ($data, $type = 'rehearsal')
	{
		$params = ComponentHelper::getParams('com_tempus');

		$subject = $params->get($type . '_subject');

		foreach ($data as $key => $value) {
			$replace = '{' . $key . '}';
			$subject = str_replace($replace, $value, $subject);
		}

		return $subject;
	}
}