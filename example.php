<?php

require_once 'ViewApi.php';

/**
 * Manipulate documents using the API.
 */
//$api_key = "YOUR_API_KEY";
$api_key = "e0dbfa00b9a743a1ad1bb398545bed52";
$viewApi = new ViewApi($api_key);

/**
 *  Create new document we want to upload.
 *  用URL的方式
 */
/*$viewDocumentByURL = new ViewDocument(array(
	'fileName' => 'test document', 
	'fileUrl' => 'URL_TO_FILE'
	));*/
$viewDocumentByURL = new ViewDocument(array(
	'fileName' => 'f886haoc.pdf', 
	'fileUrl' => 'http://www.irs.gov/pub/irs-pdf/f886haoc.pdf'
	));

$documentBeanByURL;
try {
	$documentBeanByURL = $viewApi->upload($viewDocumentByURL);
	if ($documentBeanByURL->error === 0) {
		echo sprintf("Uploaded successfully, docId:%s", $documentBeanByURL->docId),'<br/>';
	} else {
		echo sprintf("Uploaded failed, error:%s, msg:%s", $documentBeanByURL->error, $documentBeanByURL->msg),'<br/>';
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
 *  用Path的方式
 */
/*$viewDocumentByPath = new ViewDocument(array(
	'filePath' => 'PATH_TO_FILE'
	));*/
$viewDocumentByPath = new ViewDocument(array(
	'filePath' => 'C:\\Users\\jining_huang\\Desktop\\1.pdf'
	));
$documentBeanByPath;
try {
	$documentBeanByPath = $viewApi->upload($viewDocumentByPath);
	if ($documentBeanByPath->error === 0) {
		echo sprintf("Uploaded successfully, docId:%s", $documentBeanByPath->docId),'<br/>';
	} else {
		echo sprintf("Uploaded failed, error:%s, msg:%s", $documentBeanByPath->error, $documentBeanByPath->msg),'<br/>';
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
 *  用文件数据的方式
 */
$BYTEDATA_TO_FILE = array(0x10,0x10,0x10,0x10,0x10);
$viewDocumentByData = new ViewDocument(array(
	'fileName' => 'test document', 
	'fileData' => $BYTEDATA_TO_FILE
	));
$documentBeanByData;
try {
	$documentBeanByData = $viewApi->upload($viewDocumentByData);
	if ($documentBeanByData->error === 0) {
		echo sprintf("Uploaded successfully, docId:%s", $documentBeanByData->docId),'<br/>';
	} else {
		echo sprintf("Uploaded failed, error:%s, msg:%s", $documentBeanByData->error, $documentBeanByData->msg),'<br/>';
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
		echo sprintf("Deleted failed, error:%s, msg:%s", $deleteBean->error, $deleteBean->msg),'<br/>';
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
		echo sprintf("View successfully, docId:%s, sessionId:%s\n", $documentBeanByURL->docId, $sessionBean->sessionId),'<br/>';
	} else {
		echo sprintf("View failed, error:%s, msg:%s", $sessionBean->error, $sessionBean->msg),'<br/>';
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
