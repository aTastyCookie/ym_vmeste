<?php

/**
 * Handle file uploads via XMLHttpRequest
 */
class qqUploadedFileXhr {
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {    
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);
        
        if ($realSize != $this->getSize()){            
            return false;
        }
        
        $target = fopen($path, "w");        
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);
        
        return true;
    }
    function getName() {
        return $_GET['qqfile'];
    }
    function getSize() {
        if (isset($_SERVER["CONTENT_LENGTH"])){
            return (int)$_SERVER["CONTENT_LENGTH"];            
        } else {
            throw new Exception('Getting content length is not supported.');
        }      
    }   
}

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class qqUploadedFileForm {  
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {
        if(!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)){
            return false;
        }
        return true;
    }
    function getName() {
        return $_FILES['qqfile']['name'];
    }
    function getSize() {
        return $_FILES['qqfile']['size'];
    }
}

class qqFileUploader {
    private $allowedExtensions = array();
    private $sizeLimit = 10485760;
    private $file;

    function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760){        
        $allowedExtensions = array_map("strtolower", $allowedExtensions);
            
        $this->allowedExtensions = $allowedExtensions;        
        $this->sizeLimit = $sizeLimit;
        
        $this->checkServerSettings();       

        if (isset($_GET['qqfile'])) {
            $this->file = new qqUploadedFileXhr();
        } elseif (isset($_FILES['qqfile'])) {
            $this->file = new qqUploadedFileForm();
        } else {
            $this->file = false; 
        }
    }
    
    private function checkServerSettings(){        
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));        
        
        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit){
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';             
            die("{'error':'increase post_max_size and upload_max_filesize to $size'}");    
        }        
    }
    
    private function toBytes($str){
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;        
        }
        return $val;
    }
    
    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    function handleUpload($uploadDirectory, $replaceOldFile = FALSE){
        if (!is_writable($uploadDirectory)){
            return array('error' => "Server error. Upload directory isn't writable.");
        }
        
        if (!$this->file){
            return array('error' => 'No files were uploaded.');
        }
        
        $size = $this->file->getSize();
        
        if ($size == 0) {
            return array('error' => 'File is empty');
        }
        
        if ($size > $this->sizeLimit) {
            return array('error' => 'File is too large');
        }
        
        $pathinfo = pathinfo($this->file->getName());
        $filename = $pathinfo['filename'];
        //$filename = md5(uniqid());
        $ext = $pathinfo['extension'];

        if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'File has an invalid extension, it should be one of '. $these . '.');
        }
        
        if(!$replaceOldFile){
            /// don't overwrite previous files that were uploaded
            while (file_exists($uploadDirectory . $filename . '.' . $ext)) {
                $filename .= rand(10, 99);
            }
        }
        
        if ($this->file->save($uploadDirectory . $filename . '.' . $ext)){
            // работа с FB
            $t1 = microtime(true);
            $config = Zend_Registry::get('appConfig');
            $facebook = new Facebook(array('appId'  => $config->ymoney->APP_ID, 'secret' => $config->ymoney->APP_SECRET));
            $facebook->setFileUploadSupport(true);
            
            // Выбираем все альбомы пользователя
            $aid = 0;
            $result = $facebook->api('/me/albums');
            if(!empty($result['data'])) {
                foreach($result['data'] as $k=>$v) {
                    if($v['name'] == 'Собирайте деньги'){
                        $aid = $v['id'];
                        break;
                    }
                }
            }
            $t2 = microtime(true);
            // Если альбома нет, то создаем
            if($aid == 0) {
                $album_details = array(
                    'message'=> '',
                    'name'=> 'Собирайте деньги'
                );
                $create_album = $facebook->api('/me/albums', 'post', $album_details);
                $aid = $create_album['id'];
            }
            $photo_details = array(
                'message'=> ''
            );
            $t3 = microtime(true);
            $photo_details['image'] = '@' . $uploadDirectory . $filename . '.' . $ext;
            $upload_photo = $facebook->api('/' . $aid . '/photos', 'post', $photo_details);
            $t4 = microtime(true);
            $arr = json_encode($upload_photo);
            $fql = "SELECT pid, src FROM photo WHERE object_id ='" . $upload_photo['id'] . "'";
            $param = array(
                'method' => 'fql.query',
                'query' => $fql,
                'callback' => ''
            );
                            
            $upload_photo = json_encode($facebook->api($param));
            $t5 = microtime(true);
            
            $debug_info = array('get albums' => round($t2 - $t1, 3),
                                'create album' => round($t3 - $t2, 3),
                                'upload img' => round($t4 - $t3, 3),
                                'get img info' => round($t5 - $t4, 3),
                                'total' => round($t5 - $t1, 3));
            
            return array('success'=>true, 'data'=>$upload_photo, 'info' => $debug_info);
        } else {
            return array('error'=> 'Could not save uploaded file.' .
                'The upload was cancelled, or server error encountered');
        }
        
    }    
}

// list of valid extensions, ex. array("jpeg", "xml", "bmp")
$allowedExtensions = array("jpg", "jpeg", "gif", "png");
// max file size in bytes
$sizeLimit = 2 * 1024 * 1024;

$uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
$result = $uploader->handleUpload(APPLICATION_PATH . '/../files/');
// to pass data through iframe you will need to encode all html tags
echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
