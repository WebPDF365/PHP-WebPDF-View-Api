<?php

require_once 'ViewApi.php';

/**
 * Manipulate documents using the API.
 */
$api_key = "YOUR_API_KEY";
$viewApi = new ViewApi($api_key);

/**
 *  Create new document we want to upload.
 *  Use URL.
 */
$viewDocumentByURL = new ViewDocument(array(
	'fileName' => 'test document', 
	'fileUrl' => 'URL_TO_FILE'
	));

$documentBeanByURL;
try {
	$documentBeanByURL = $viewApi->upload($viewDocumentByURL);
	if ($documentBeanByURL->error === 0) {
		echo sprintf("Uploaded successfully, docId:%s", $documentBeanByURL->docId),'<br/>';
	} else {
		echo sprintf("Uploaded failed, error:%d, msg:%s", $documentBeanByURL->error, $documentBeanByURL->msg),'<br/>';
		exit();
	}
} catch (ViewException $e) {
	echo $e->getMessage(),'http code:',$e->getCode(),'<br/>';
	exit();
} catch (Exception $e) {
	echo $e->getMessage(),'<br/>';
	exit();
}

/**
 *  Create new document we want to upload.
 *  Use Path.
 */
$viewDocumentByPath = new ViewDocument(array(
	'filePath' => 'PATH_TO_FILE'
	));
$documentBeanByPath;
try {
	$documentBeanByPath = $viewApi->upload($viewDocumentByPath);
	if ($documentBeanByPath->error === 0) {
		echo sprintf("Uploaded successfully, docId:%s", $documentBeanByPath->docId),'<br/>';
	} else {
		echo sprintf("Uploaded failed, error:%d, msg:%s", $documentBeanByPath->error, $documentBeanByPath->msg),'<br/>';
		exit();
	}
} catch (ViewException $e) {
	echo $e->getMessage(),'http code:',$e->getCode(),'<br/>';
	exit();
} catch (Exception $e) {
	echo $e->getMessage();
	exit();
}

/**
 *  Create new document we want to upload.
 *  Use document data.
 */
$BYTEDATA_TO_FILE = array(0x10,0x10,0x10,0x10,0x10);
$viewDocumentByData = new ViewDocument(array(
	'fileName' => 'test document2', 
	'fileData' => $BYTEDATA_TO_FILE
	));
$documentBeanByData;
try {
	$documentBeanByData = $viewApi->upload($viewDocumentByData);
	if ($documentBeanByData->error === 0) {
		echo sprintf("Uploaded successfully, docId:%s", $documentBeanByData->docId),'<br/>';
	} else {
		echo sprintf("Uploaded failed, error:%d, msg:%s", $documentBeanByData->error, $documentBeanByData->msg),'<br/>';
		exit();
	}
} catch (ViewException $e) {
	echo $e->getMessage(),'http code:',$e->getCode(),'<br/>';
	exit();
} catch (Exception $e) {
	echo $e->getMessage();
	exit();
}

/**
 * Delete the document.
 */
try {
	$deleteBean = $viewApi->delete($documentBeanByData->docId);
	if ($deleteBean->error === 0) {
		echo sprintf("Deleted successfully, docId:%s", $documentBeanByData->docId),'<br/>';
	} else {
		echo sprintf("Deleted failed, error:%d, msg:%s", $deleteBean->error, $deleteBean->msg),'<br/>';
	}
} catch (ViewException $e) {
	echo $e->getMessage(),'http code:',$e->getCode(),'<br/>';
	exit();
} catch (Exception $e) {
	echo $e->getMessage();
	exit();
}

/**
 * View Documents using the API.
 */
$sessionBean = null;
try {
	$sessionBean = $viewApi->view($documentBeanByURL->docId, array('expiry' => 120));
	if ($sessionBean->error === 0) {
		echo sprintf("View successfully, docId:%s, sessionId:%s", $documentBeanByURL->docId, $sessionBean->sessionId),'<br/>';
	} else {
		echo sprintf("View failed, error:%d, msg:%s", $sessionBean->error, $sessionBean->msg),'<br/>';
		exit();
	}
} catch (ViewException $e) {
	echo $e->getMessage(),'http code:',$e->getCode(),'<br/>';
	exit();
} catch (Exception $e) {
	echo $e->getMessage();
	exit();
}

$html = '<iframe src="' . $sessionBean->urls->view . '" width="800px" height="600px"></iframe>';
echo $html;

/**
 * Get session information based on session ID
 */
$sessionInfoBean = $viewApi->getSessionInfo($sessionBean->sessionId);
if ($sessionInfoBean->error === 0) {
    echo sprintf("Get session information based on session ID successfully,sessionId:%s, createDate:%s", $sessionInfoBean->sessionId, $sessionInfoBean->createDate),'<br/>';
} else {
    echo sprintf("Get session information based on session ID failed, error:%d", $sessionInfoBean->error),'<br/>';
    exit();
}

/**
 * Search session information based on document ID
 */
$sessionInfoListBean = $viewApi->getSessionInfoByDocId($documentBeanByURL->docId);
if ($sessionInfoListBean->error === 0) {
    echo sprintf("Search session information based on document ID successfully:"),'<br/>';
    foreach($sessionInfoListBean->sessionList as $value) {
        echo sprintf("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;sessionId:%s, createDate:%s", $value->sessionId, $value->createDate),'<br/>';
    }
} else {
    echo sprintf("Search session information based on document ID failed, error:%d", $sessionInfoListBean->error),'<br/>';
    exit();
}

/**
 * Delete session information based on session ID
 */
$deleteSessionBean = $viewApi->deleteSession($sessionBean->sessionId);
if ($deleteSessionBean->error === 0) {
    echo sprintf("Delete session information based on session ID successfully,sessionId:%s", $sessionBean->sessionId),'<br/>';
} else {
    echo sprintf("Delete session information based on session ID failed, error:%d", $deleteSessionBean->error),'<br/>';
    exit();
}

/**
 * Delete session information based on document ID
 */
$deleteSessionByDocIdBean = $viewApi->deleteSessionByDocId($documentBeanByURL->docId);
if ($deleteSessionByDocIdBean->error === 0) {
    echo sprintf("Delete session information based on session ID successfully,docId:%s", $documentBeanByURL->docId),'<br/>';
} else {
    echo sprintf("Delete session information based on session ID failed, error:%d", $deleteSessionByDocIdBean->error),'<br/>';
    exit();
}

