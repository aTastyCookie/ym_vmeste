<?php
class Application_Model_DbTable_Photo extends Ymoney_Db_Table
{
    protected $_name = 'photo';
    protected $_primary = 'id';
    
    public function getPhotosByAction($action_id)
    {
    	$resArr = array();
        $sql = $this->select()
        ->from($this->getTableName(), array('pid'))
        ->where('action_id = ?', $action_id)
        ->where('pid IS NOT NULL')
        ->where("pid != ''");
//echo $sql->__toString();
        $rows = $this->fetchAll($sql);
        foreach ($rows as $row) {
        	$resArr[] = $row['pid'];
        }

        return $resArr;
    }
    
	public function getFullPhotosByAction($action_id)
    {
    	$resArr = array();
        $sql = $this->select()
        ->from($this->getTableName(), array('pid', 'src'=>'url_small', 'src_big'=>'url'))
        ->where('action_id = ?', $action_id)
        ->where('pid IS NOT NULL')
        ->where("pid != ''");
		//echo $sql->__toString();
        $rows = $this->fetchAll($sql);
        foreach ($rows as $row) {
        	$resArr[] = $row;
        }

        return $resArr;
    }
    
	static public function ImageShowJPG($path1, $width, $height)
    {
    	header('Content-Type: image/jpg');
    	$im = @imagecreatefromjpeg($path1);
    	$im2 = @imagecreatetruecolor($width, $height);
    	if ($im) {
    		$imsize = getimagesize($path1);
    		$w = $imsize[0]; $h = $imsize[1];
    		$w2 = round($w/2);
    		$h2 = round($h/2);
    		$d2 = $width/$height;
    		//$d1 = $w/$h;
    		if($w<$h) {
    			$srcW = $w;
    			$srcH = round($w/$d2);
    			$srcY = $h2 - round($srcH/2);
    			$srcX = 0;
    		} else {
    			$srcW = round($h*$d2);
    			$srcH = $h;
    			$srcX = $w2 - round($srcW/2);
    			$srcY = 0;
    		}
    		//echo "imagecopyresized($im2, $im, 0, 0, $srcX, $srcY, $width, $height, $srcW, $srcH);";
    		imagecopyresized($im2, $im, 0, 0, $srcX, $srcY, $width, $height, $srcW, $srcH);
    		imagejpeg($im2, NULL, 80);
    	} else {
    		return false;
    	}
    }

    static public function ImageResizeJPG($path1, $path2, $width, $height)
    {
    	$im = @imagecreatefromjpeg($path1);
    	if ($im) {
    		$im2 = imagecreatetruecolor ($width, $height);
    		$imsize = getimagesize($path1);
    		$w = $imsize[0]; $h = $imsize[1];
    		$w2 = round($w/2);
    		$h2 = round($h/2);
    		$d2 = $width/$height;
    		//$d1 = $w/$h;
    		if($w<$h) {
    			$srcW = $w;
    			$srcH = round($w/$d2);
    			$srcY = $h2 - round($srcH/2);
    			$srcX = 0;
    		} else {
    			$srcW = round($h*$d2);
    			$srcH = $h;
    			$srcX = $w2 - round($srcW/2);
    			$srcY = 0;
    		}
    		//echo "imagecopyresized($im2, $im, 0, 0, $srcX, $srcY, $width, $height, $srcW, $srcH);";
    		imagecopyresized($im2, $im, 0, 0, $srcX, $srcY, $width, $height, $srcW, $srcH);
    		imagejpeg($im2, $path2, 100);
    	} else {
    		return false;
    	}
    }

	static public function ImageResizePNG($path1, $path2, $width, $height)
    {
    	$im = @imagecreatefrompng($path1);
    	if ($im) {
    		$im2 = imagecreatetruecolor($width, $height);
    		//echo $imsize;
    		$imsize = getimagesize($path1);
    		$w = $imsize[0]; $h = $imsize[1];
    		$w2 = round($w/2);
    		$h2 = round($h/2);
    		$d2 = $width/$height;
    		//$d1 = $w/$h;
    		if($w<$h) {
    			$srcW = $w;
    			$srcH = round($w/$d2);
    			$srcY = $h2 - round($srcH/2);
    			$srcX = 0;
    		} else {
    			$srcW = round($h*$d2);
    			$srcH = $h;
    			$srcX = $w2 - round($srcW/2);
    			$srcY = 0;
    		}
    		//echo "imagecopyresized($im2, $im, 0, 0, $srcX, $srcY, $width, $height, $srcW, $srcH);";
    		imagecopyresized($im2, $im, 0, 0, $srcX, $srcY, $width, $height, $srcW, $srcH);
    		imagepng($im2, $path2);
    	} else {
    		return false;
    	}
    }
    
	static public function ImageResizeGIF($path1, $path2, $width, $height)
    {
    	$im = @imagecreatefromgif($path1);
    	if ($im) {
    		$im2 = imagecreatetruecolor($width, $height);
    		$imsize = getimagesize($path1);
    		$w = $imsize[0]; $h = $imsize[1];
    		$w2 = round($w/2);
    		$h2 = round($h/2);
    		$d2 = $width/$height;
    		//$d1 = $w/$h;
    		if($w<$h) {
    			$srcW = $w;
    			$srcH = round($w/$d2);
    			$srcY = $h2 - round($srcH/2);
    			$srcX = 0;
    		} else {
    			$srcW = round($h*$d2);
    			$srcH = $h;
    			$srcX = $w2 - round($srcW/2);
    			$srcY = 0;
    		}
    		//echo "imagecopyresized($im2, $im, 0, 0, $srcX, $srcY, $width, $height, $srcW, $srcH);";
    		imagecopyresized($im2, $im, 0, 0, $srcX, $srcY, $width, $height, $srcW, $srcH);
    		imagepng($im2, $path2);
    	} else {
    		return false;
    	}
    }
    
	static public function ImageResizeBMP($path1, $path2, $width, $height)
    {
    	$im = @imagecreatefromwbmp($path1);
    	if ($im) {
    		$im2 = imagecreatetruecolor ($width, $height);
    		$imsize = getimagesize($path1);
    		$w = $imsize[0]; $h = $imsize[1];
    		$w2 = round($w/2);
    		$h2 = round($h/2);
    		$d2 = $width/$height;
    		//$d1 = $w/$h;
    		if($w<$h) {
    			$srcW = $w;
    			$srcH = round($w/$d2);
    			$srcY = $h2 - round($srcH/2);
    			$srcX = 0;
    		} else {
    			$srcW = round($h*$d2);
    			$srcH = $h;
    			$srcX = $w2 - round($srcW/2);
    			$srcY = 0;
    		}
    		//echo "imagecopyresized($im2, $im, 0, 0, $srcX, $srcY, $width, $height, $srcW, $srcH);";
    		imagecopyresized($im2, $im, 0, 0, $srcX, $srcY, $width, $height, $srcW, $srcH);
    		imagewbmp($im2, $path2);
    	} else {
    		return false;
    	}
    }
    
    public function deleteByAction($id) 
    {
    	$this->delete("action_id = $id");
        return true;
    }
}