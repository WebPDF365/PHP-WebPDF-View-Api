<?php

require_once 'ViewException.php';
require_once 'bean/DocumentBean.php';
require_once 'bean/SessionBean.php';
require_once 'bean/SessionBean.php';
require_once 'bean/SessionInfoBean.php';
require_once 'bean/SessionInfoListBean.php';
require_once 'ViewDocument.php';

/**
 * Class ViewApi
 * 
 * WebPDF Cloud API implementation.
 * Allows you to easily work with the WebPDF Cloud API,
 * uploading, deleting, and viewing documents.
 * 
 * @link http://api.webpdf365.com @endlink
 * 
 * @author jining_huang <jinping_huang@foxitsoftware.com>
 *
 */
class ViewApi {
	private $apiServiceRootUrl = 'https://api.webpdf365.com/api/v1.0';
	private $apiDocumentUrl = '/documents/%docId%?api_key=';
	private $apiUploadDocumentUrl = '/documents?api_key=';
	private $apiSessionUrl = '/sessions?api_key=';
	private $apiSessionInfoUrl = '/sessions/%sessionId%?api_key=';
	private $apiSessionInfoByDocIdUrl = '/%docId%/sessions?api_key=';
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
		$this->apiDocumentUrl = $this->apiServiceRootUrl . $this->apiDocumentUrl .  $this->apiKey;
		$this->apiUploadDocumentUrl = $this->apiServiceRootUrl . $this->apiUploadDocumentUrl . $this->apiKey;
		$this->apiSessionUrl = $this->apiServiceRootUrl . $this->apiSessionUrl . $this->apiKey;
		$this->apiSessionInfoUrl = $this->apiServiceRootUrl . $this->apiSessionInfoUrl . $this->apiKey;
		$this->apiSessionInfoByDocIdUrl = $this->apiServiceRootUrl . $this->apiSessionInfoByDocIdUrl . $this->apiKey;
		return $this;
	}
	
	/**
	 * Upload a new file the View API for conversion.
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
		$documentBean = new DocumentBean();
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
	 * @param String $docId
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
		$apideleteUrl = str_replace('%docId%', $docId, $this->apiDocumentUrl);
		$curl_params[CURLOPT_URL] = $apideleteUrl;
		$curl_params[CURLOPT_CUSTOMREQUEST] = 'DELETE';
		$curl_params[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
		
		// Removes the file.
		$result = $this->httpRequest($curl_params);
		$baseBean = new BaseBean();
		$baseBean->error = $result->response->error;
		$baseBean->msg = $result->response->msg;
		return $baseBean;
	}
	
	/**
	 * Create a session for a single document.
	 * Sessions can only be created for documents that have a status of done
	 * 
	 * @param String $docId
	 * 	The docId of the file to view
	 * 
	 * @param Map<String, Object> $params
	 * 	A key-value pair of POST params
	 * 		Long expiry -- Expiry time of the session starting from current time, in minutes, default is 60, can not be negative number or 0.
	 * 		Boolean infinite -- Whether SessionId is always valid, default is false.
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
		$sessionBean = new SessionBean();
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
     * Get session information based on session ID
     *
     * @param String $sessionId
     * 	The sessionId of the Session ID to get session information
     *
     * @return SessionInfoBean
     * 	The response is an object converted from JSON
     */
    public function getSessionInfo($sessionId) {
        if (empty($sessionId)) {
            throw new ViewException('Missing required sessionId.');
        }
        $curl_params[CURLOPT_URL] = str_replace('%sessionId%', $sessionId, $this->apiSessionInfoUrl);
        $curl_params[CURLOPT_CUSTOMREQUEST] = 'GET';

        $result = $this->httpRequest($curl_params);
        $sessionInfoBean = new SessionInfoBean();
        $sessionInfoBean->error = $result->response->error;
        $sessionInfoBean->sessionId = $result->response->sessionId;
        $sessionInfoBean->docId = $result->response->docId;
        $sessionInfoBean->expiry = $result->response->expiry;
        $sessionInfoBean->expiryDate = $result->response->expiryDate;
        $sessionInfoBean->createDate = $result->response->createDate;
        $sessionInfoBean->infinite = $result->response->infinite;
        return $sessionInfoBean;
    }

	/**
	 * Delete session information based on session ID
	 *
	 * @param String $sessionId
	 * 	The sessionId of the Session ID to delete
	 *
	 * @return BaseBean
	 * 	The response is an object converted from JSON
	 */
	public function deleteSession($sessionId) {
	    if (empty($sessionId)) {
            throw new ViewException('Missing required sessionId.');
        }
	    $curl_params[CURLOPT_URL] = str_replace('%sessionId%', $sessionId, $this->apiSessionInfoUrl);
        $curl_params[CURLOPT_CUSTOMREQUEST] = 'DELETE';
        $curl_params[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';

        $result = $this->httpRequest($curl_params);
        $baseBean = new BaseBean();
        $baseBean->error = $result->response->error;
        return $baseBean;
	}

	/**
	 * Search session information based on document ID
	 *
	 * @param String $docId
	 *  The docId of the file to search session information
	 *
	 * @return SessionInfoListBean
	 *  The response is an object converted from JSON
	 */
	public function getSessionInfoByDocId($docId) {
	    if (empty($docId)) {
            throw new ViewException('Missing required docid.');
        }
        $curl_params[CURLOPT_URL] = str_replace('%docId%', $docId, $this->apiSessionInfoByDocIdUrl);
        $curl_params[CURLOPT_CUSTOMREQUEST] = 'GET';

        $result = $this->httpRequest($curl_params);
        $sessionInfoListBean = new SessionInfoListBean();
		$sessionInfoListBean->sessionList = array();
        $sessionInfoListBean->error = $result->response->error;
        foreach($result->response->sessionList as $value) {
            $sessionInfo = new SessionInfo();
            $sessionInfo->sessionId = $value->sessionId;
            $sessionInfo->docId =$value->docId;
            $sessionInfo->expiry = $value->expiry;
            $sessionInfo->expiryDate = $value->expiryDate;
            $sessionInfo->createDate = $value->createDate;
            $sessionInfo->infinite = $value->infinite;
            array_push($sessionInfoListBean->sessionList, $sessionInfo);
        }
        return $sessionInfoListBean;
	}

	/**
	 * Delete session information based on document ID
	 *
	 * @param String $docId
	 * 	The docId of the file to delete session
	 *
	 * @return BaseBean
	 * 	The response is an object converted from JSON
	 */
	public function deleteSessionByDocId($docId) {
	    if (empty($docId)) {
            throw new ViewException('Missing required docid.');
        }
        $curl_params[CURLOPT_URL] = str_replace('%docId%', $docId, $this->apiSessionInfoByDocIdUrl);
        $curl_params[CURLOPT_CUSTOMREQUEST] = 'DELETE';
        $curl_params[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';

        $result = $this->httpRequest($curl_params);
        $baseBean = new BaseBean();
        $baseBean->error = $result->response->error;
        return $baseBean;
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
		$curl_params[CURLOPT_SSL_VERIFYPEER] = FALSE;
		$curl_params[CURLOPT_SSL_VERIFYHOST] = FALSE;
		
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