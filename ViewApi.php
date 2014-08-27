<?php

require_once 'ViewException.php';
require_once 'bean/DocumentBean.php';
require_once 'bean/SessionBean.php';
require_once 'ViewDocument.php';

/**
 * Class ViewApi
 * 
 * Webpdf View API implementation.
 * Allows you to easily work with the Webpdf View API,
 * uploading, deleting, and viewing documents.
 * 
 * @link http://api.webpdf365.com @endlink
 * 
 * @author jining_huang <jinping_huang@foxitsoftware.com>
 *
 */
class ViewApi {
	//private $apiServiceRootUrl = 'http://api.webpdf365.com/api/v1.0';
	private $apiServiceRootUrl = 'http://it-api.webpdf365.com/api/v1.0';
	private $apiDocumentUrl = '/documents';
	private $apiUploadDocumentUrl = '/documents';
	private $apiSessionUrl = '/sessions';
	private $apiKey;
	
	/**
	 * Initializes the ViewApi object.
	 * Ensures we have access to cURL,
	 * that the api_key is set,
	 * and sets various URLs needed for interacting with the API.
	 * 
	 * @param String $apiKey
	 * 	API Key for your Webpdf View Application.
	 * @param String $apiServiceRootUrl
	 *  API Service Root Url.
	 */
	public function __construct($apiKey, $apiServiceRootUrl = null) {
		// Ensure we have access to cURL.
		$curlInstalled = in_array('curl', get_loaded_extensions()) && function_exists('curl_version');
		if (!$curlInstalled) {
			throw new ViewException('cURL extension not found.');
		}
		
		if ($apiServiceRootUrl != null && !empty($apiServiceRootUrl)) {
			$this->apiServiceRootUrl = $apiServiceRootUrl;
		}
		
		$this->apiKey = $apiKey;
		$this->apiDocumentUrl = $this->apiServiceRootUrl . $this->apiDocumentUrl;
		$this->apiUploadDocumentUrl = $this->apiServiceRootUrl . $this->apiUploadDocumentUrl . '?api_key=' . $this->apiKey;
		$this->apiSessionUrl = $this->apiServiceRootUrl . $this->apiSessionUrl . '?api_key=' . $this->apiKey;
		return $this;
	}
	
	/**
	 * Upload a new file  the View API for conversion.
	 * Files can be uploaded either through a publicly accessible URL or
	 * through a multipart POST.
	 * 
	 * @param ViewDocument $doc
	 *  
	 * @return DocumentBean
	 * 	The response is an object converted from JSON
	 * @throws ViewException
	 */
	public function upload(ViewDocument &$doc) {
		// To upload we must POST the url or file.
		$tmpFilePath = "";
		$curl_params[CURLOPT_CUSTOMREQUEST] = 'POST';
		$curl_params[CURLOPT_URL] = $this->apiUploadDocumentUrl;
		$post_fields = array();
		if (!empty($doc->fileUrl)) {
			// We are doing a URL Upload.
			$post_fields = json_encode(array(
				'fileName' => $doc->fileName ? $doc->fileName: basename($doc->fileUrl),
				'url' => $doc->fileUrl
			));
			$curl_params[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
		} elseif (!empty($doc->filePath)) {
			// To upload we must use the special upload URL.
			$curl_params[CURLOPT_HTTPHEADER][] = 'Content-Type: multipart/form-data';
			$post_fields['file'] = '@' . $doc->filePath;
			//$post_fields['file'] = '@' . mb_convert_encoding($doc->filePath,"gb2312","utf-8");
		} elseif (!empty($doc->fileData)) {
			$curl_params[CURLOPT_HTTPHEADER][] = 'Content-Type: multipart/form-data';
			$tmpFilePath = sys_get_temp_dir();
			$lastChar = substr($tmpFilePath, -1);
			if ($lastChar != '\\' && $lastChar != '/') {
				$tmpFilePath = $tmpFilePath . '/';
			}
			if (empty($doc->fileName)) {
				$tmpFilePath = $tmpFilePath . time() . '.pdf';
			} else {
				$filename = trim($doc->fileName);
				$ext = strtolower(substr($filename, -4));
				if ( $ext != ".pdf" ) {
					$filename = $filename . '.pdf';
				}
				$tmpFilePath = $tmpFilePath . $filename;
			}
			
			$fs = fopen($tmpFilePath, 'x+');
			fwrite($fs, $doc->fileData);
			fclose($fs);
			$post_fields['file'] = '@' . $tmpFilePath;
		} else {
			throw new ViewException('Missing file information. url or file path, or file data must be set.');
		}

		$curl_params[CURLOPT_POSTFIELDS] = $post_fields;
		
		// Upload the file.
		$result = $this->httpRequest($curl_params);
		$documentBean = new DocumentBean;
		$documentBean->error = $result->response->error;
		$documentBean->msg = $result->response->msg;
		$documentBean->docId = $result->response->docId;
		$documentBean->name = $result->response->name;
		if (!empty($tmpFilePath)) {
			unlink($tmpFilePath);
		}
		return $documentBean;
	}
	
	/**
	 * Removes a document completely from the View API servers.
	 * 
	 * @param String docId
	 * 	The docId of the file to delete
	 * 
	 * @throws ViewException
	 * @return BaseBean 
	 * 	The response is an object converted from JSON
	 */
	public function delete($docId) {
		if (empty($docId)) {
			throw new ViewException('Missing required docid.');
		}
		$apideleteUrl = $this->apiDocumentUrl . '/' . $docId . '?api_key=' . $this->apiKey;
		$curl_params[CURLOPT_URL] = $apideleteUrl;
		$curl_params[CURLOPT_CUSTOMREQUEST] = 'DELETE';
		$curl_params[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
		
		// Removes the file.
		$result = $this->httpRequest($curl_params);
		$baseBean = new BaseBean;
		$baseBean->error = $result->response->error;
		$baseBean->msg = $result->response->msg;
		return $baseBean;
	}
	
	/**
	 * Creates a session for a single document.
	 * Sessions can only be created for documents that have a status of done
	 * 
	 * @param String docId
	 * 	The docId of the file to view
	 * 
	 * @param Map<String, Object> params
	 * 	A key-value pair of POST params
	 * 		Integer expiry -- 以当前时间开始的session过期时间，单位为分钟，默认值为60,不能够等于负数与0
	 * 		Boolean infinite -- SessionId是否长期有效的标志, 默认为false
	 * 
	 * @return SessionBean
	 * 	The response is an object converted from JSON
	 * @throws ParameterTypeException 
	 */
	public function view($docId, $params = array()) {
		if (empty($docId)) {
			throw new ViewException('Missing required docid.');
		}
		$curl_params[CURLOPT_URL] = $this->apiSessionUrl;
		$curl_params[CURLOPT_CUSTOMREQUEST] = 'POST';
		$curl_params[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
		$params['docId'] = $docId;
		$curl_params[CURLOPT_POSTFIELDS] = json_encode($params);
		
		$result = $this->httpRequest($curl_params);
		$sessionBean = new SessionBean;
		$viewUrl = new ViewUrl;
		$viewUrl->view = $result->response->urls->view;
		$viewUrl->assets = $result->response->urls->assets;
		$sessionBean->error = $result->response->error;
		$sessionBean->msg = $result->response->msg;
		$sessionBean->urls = $viewUrl;
		$sessionBean->sessionId = $result->response->sessionId;
		$sessionBean->expiryDate = $result->response->expiryDate;
		$sessionBean->infinite = $result->response->infinite;
		return $sessionBean;
	}
	
	/**
	 * Makes an HTTP request to the Webpdf View API and returns the result.
	 *
	 * @param array $curl_params
	 *  Array of CURLOPT params.
	 *
	 * @throws ViewException
	 * @return array $response
	 */
	private function httpRequest($curl_params = array()) {
		$curl = curl_init();
		
		// Return the result of the curl_exec().
		$curl_params[CURLOPT_RETURNTRANSFER] = TRUE;
		$curl_params[CURLOPT_FOLLOWLOCATION] = TRUE;
		
		// Set other CURL_OPT params.
		foreach ($curl_params as $curl_opt => $val) {
		  curl_setopt($curl, $curl_opt, $val);
		}
		
		// Get the response.
		$response = curl_exec($curl);
		
		// Ensure our request didn't have errors.
		if ($error = curl_error($curl)) {
		  throw new ViewException($error);
		}
		
		// Close and return the curl response.
		$result = $this->parseResponse($curl, $response);
		curl_close($curl);
		if ($result->headers->code != 200 || (is_object($result->response) && $result->response->error != 0)) {
		  throw new ViewException('Error: ' . $result->response->error . ';Msg: ' . $result->response->msg, $result->headers->code);
		}
		return $result;
	}
	
	/**
	 * Parses the response in a more friendly format.
	 *
	 * @param $curl
	 * @param string $response
	 * @return object
	 */
	private function parseResponse($curl, $response = '') {
		$headers = new stdClass();
		$headers->code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if ($decoded = json_decode($response)) {
			$body = $decoded;
		}
		else {
			$body = $response;
		}
		return (object) array('headers' => $headers, 'response' => $body);
	}
}