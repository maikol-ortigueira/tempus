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

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Object\CMSObject;

/**
 * Dropbox helper.
 *
 * @since  1.6
 */
class DropboxHelper
{
    protected static $rpcEndpoint = 'https://api.dropboxapi.com/2';
    protected static $authEndpoint = 'https://api.dropboxapi.com';   
    protected static $contentEndpoint = 'https://content.dropboxapi.com';
    protected static $lastErrorCode = 0;
	protected static $lastCurlError = 0;
	
	protected static function postCurl($url, $data = "", $access_token = "")
	{
		if(empty($data))
		{
		   return false;   
		}
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, 1);
		$headers = array();
		if(!empty($data))
		{
		  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		  if(!empty($access_token))
		  {
			  //if $access token is empty then this is authentication call and for some reason it won't access json
			  $headers[] = 'Content-Type: application/json';
		  }
		}
		
		if(!empty($access_token))
		{
			$headers[] = 'Authorization: Bearer '. $access_token;
		}
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		self::$lastCurlError = curl_errno($ch);
		if(!self::$lastCurlError)
		{
		   $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		   if((int)$http_code != 200)
		   {
			   self::$lastErrorCode = (int)$http_code;
			   $errors = json_decode($result);
			   $app = JFactory::getApplication();
			   if(isset($errors->error))
			   {
				 $app->enqueueMessage($errors->error . ' : '. $errors->error_description);
			   }
			   else
			   {
				 $app->enqueueMessage($result);
			   }
			   $return = false;
		   }
		   else
		   {
			   $return = $result;
		   }
		}
		else
		{
			$return = false;
		}
 
 
		
		curl_close($ch);
		return $return;
		
	}

    public static function filesUpload($token, $src, $options = array())
	{
		$url = self::$contentEndpoint . '/2/files/upload';
		$ch = curl_init($url);
		$aPostData = $options;
		
		$fp = fopen($src, 'rb');
        $filesize = filesize($src);
		$data = fread($fp, $filesize);
		
		
		$aOptions = array(
			CURLOPT_POST => true,
			CURLOPT_HTTPHEADER => array('Content-Type: application/octet-stream', 
			  'Authorization: Bearer ' . $token,
			  'Dropbox-API-Arg: ' . json_encode($aPostData)
			) ,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POSTFIELDS => $data
		);

		curl_setopt_array($ch, $aOptions);
		$result = curl_exec($ch);		

	   self::$lastCurlError = curl_errno($ch);
	   if(!self::$lastCurlError)
	   {
		  $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		  if((int)$http_code != 200)
		  {
			  self::$lastErrorCode = (int)$http_code;
			  $errors = json_decode($result);
			  $app = JFactory::getApplication();
			  if(isset($errors->error_summary))
			  {
			    $app->enqueueMessage($errors->error_summary, "warning");
			  }
			  else
			  {
			    $app->enqueueMessage($result, "warning");
			  }
			  $return = false;
		  }
		  else
		  {
			  $return = $result;
		  }
	   }
	   else
	   {
		   $return = false;
	   }


	   
	   curl_close($ch);
	   return $return;	 
	 
	}

	public static function oauth2Token($params, $auth_code)
	{
		$data = array();
		$data["code"] = $auth_code;
		$data["grant_type"] = 'authorization_code';
		$data["client_id"] = $params->get("access_key");
		$data["client_secret"] = $params->get("secret_key");
		
		$encoded_data = http_build_query($data);
		$url = self::$authEndpoint.'/oauth2/token';
		
		$response = self::postCurl($url, $encoded_data);
		return $response;
		
	}
}

