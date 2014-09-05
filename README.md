Documentation
-------------
For general API document, please refer to the [WebPDF Cloud API Document](http://api.webpdf365.com/ApiList).


To get started,
Include the required classes:
```php
require_once 'ViewApi.php';
```

Initializing the API class:
```php
$api_key = "YOUR_API_KEY";
$viewApi = new ViewApi($api_key);
```

Creating a document to upload:
```php
$viewDocument = new ViewDocument(array(
	'fileName' => 'test document.pdf', 
	'fileUrl' => 'URL_TO_FILE'
	));
```

Uploading a document to the API:
```php
$documentBean = $viewApi->upload($viewDocument);
if ($documentBean->error === 0) {
	log('info', sprintf("Uploaded successfully, docId:%s", $documentBean->docId));
} else {
	log('error', sprintf("Uploaded failed, error:%s, msg:%s", $documentBean->error, $documentBean->msg));
}
```

You can also upload local files through the API:
```php
$viewDocument = new ViewDocument(array(
	'filePath' => '/filepath/filename.pdf'
	));
$documentBean = $viewApi->upload($viewDocument);
if ($documentBean->error === 0) {
	log('info', sprintf("Uploaded successfully, docId:%s", $documentBean->docId));
} else {
	log('error', sprintf("Uploaded failed, error:%s, msg:%s", $documentBean->error, $documentBean->msg));
}
```

You can also upload memory files through the API:
```php
$viewDocument = new ViewDocument(array(
	'fileName' => 'test document', 
	'fileData' => $BYTEDATA_TO_FILE //Byte data to file
	));
$documentBean = $viewApi->upload($viewDocument);
if ($documentBean->error === 0) {
	log('info', sprintf("Uploaded successfully, docId:%s", $documentBean->docId));
} else {
	log('error', sprintf("Uploaded failed, error:%s, msg:%s", $documentBean->error, $documentBean->msg));
}
```

After some time, the document will be processed and can be viewed:
```php
$sessionBean = $viewApi->view($documentBean->docId);
if ($sessionBean->error === 0) {
	log('info', "View successfully, docId:%s, sessionId:%s\n", $documentBeanByURL->docId, $sessionBean->sessionId));
} else {
	log('error', sprintf("View failed, error:%s, msg:%s", $sessionBean->error, $sessionBean->msg));
}
```

Embed the document in an iframe.
```php
<iframe src="<?= $sessionBean->urls->view ?>"></iframe>
```

Deleting the document:
```php
$deleteBean = $viewApi->delete($documentBean->docId);
if ($deleteBean->error === 0) {
	log('info', sprintf("Deleted successfully, docId:%s", $documentBean->docId));
} else {
	log('error', sprintf("Deleted failed, error:%s, msg:%s", $deleteBean->error, $deleteBean->msg));
}
```

####Handling Exceptions
API calls will throw an instance of `ViewException` when an error is encountered.  
You should wrap your API calls with a `try/catch`.
```php
try
{
  $documentBean = $viewApi->upload($viewDocument);
}
catch(ViewException $e)
{
  log('error', $e->getMessage(), $http_code = $e->getCode());
}
```
