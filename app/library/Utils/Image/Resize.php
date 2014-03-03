<?php

namespace Library\Utils\Image;


class Resize extends \Phalcon\Mvc\User\Plugin
{
	private $imageFile;

	protected $image;
	protected $width;
	protected $height;
	protected $type;


	public function __construct($imageFile)
	{
		$this -> imageFile = $imageFile;

		$imageInfo = getimagesize($this -> imageFile);
		$this -> width = $imageInfo[0];
		$this -> height = $imageInfo[1];
		$this -> setExtension($imageInfo[2]);
		$this -> setCanvas();
	}

	public function save($type = 'jpeg', $file = NULL, $compress = 100, $permissions = '')
	{
		switch($this -> type) {
			case 'gif': 
				imagegif($this -> image, $file); break;
			case 'jpeg': 
				imagejpeg($this -> image, $file, $compress); break;
			case 'png': 
				imagepng($this -> image, $file); break;
		}

		if($permissions != '') {
			chmod($file, $permissions);
		}
	}

	public function resize($width, $height) 
	{
		$diffWidth = 0;
		$diffHeight = 0;

		if($this -> width < $eidth && $this -> height < $height) {
			$this -> resample($this -> width, $this -> height);
		} else {
			if($this -> width > $width) {
				$diffWidth = $this -> width - $width;
			}
			if($this -> height > $height) {
				$diffHeight = $this -> height - $height;
			}

			if($diffWidth > $diffHeight) {
			    $this -> resizeWidth($width);
			} elseif($diffWidth < $diffHeight) {
			    $this -> resizeHeight($height);
			} else {
			    $this -> resample($width, $height);
			}
		}
	}

	public function percentReduce($percent) 
	{
		$width = $this -> width * $percent / 100;
		$height = $this -> height * $percent / 100;
		$this -> resample($width, $height);
	}

	protected function resample($width, $height)
	{
		$newImage = imagecreatetruecolor($width, $height);
		imagecopyresampled($newImage, $this -> image, 0, 0, 0, 0, $width, $height, $this -> width, $this -> height);

		$this -> width = $width;
		$this -> height = $height;
		$this -> image = $newImage;
	}

	protected function resizeWidth($width)
	{
		$newHeight = $this -> height * ($width / $this -> width);
		$this -> resize($width, $newHeight);
	}

	protected function resizeHeight($height)
	{
		$newWidth = $this -> width * ($height / $this -> height);
		$this -> resample($newWidth, $height);
	}	

	protected function destroy()
	{
		imagedestroy($this -> image);
	}

	protected function setCanvas()
	{
 		switch($this -> type) {
			case 'gif': 
					$this -> image = imagecreatefromgif($this -> imageFile); 
				break;

			case 'jpeg': 
					$this -> image = imagecreatefromjpeg($this -> imageFile); 
				break;

			case 'png': 
					$this -> image = imagecreatefrompng($this -> imageFile); 
				break;
		}
	}

	protected function setExtension($ext)
	{
		switch($ext) {
			case 1: 
				$this -> type = 'gif'; break;
			case 2: 
				$this -> type = 'jpeg'; break;
			case 3: 
				$this -> type = 'png'; break;
			case 4: 
				$this -> type = 'swf'; break;
			case 5: 
				$this -> type = 'psd'; break;
			case 6: 
				$this -> type = 'bmp'; break;
			case 7: 
				$this -> type = 'tiffi'; break;
			case 8: 
				$this -> type = 'tiffm'; break;
			case 9: 
				$this -> type = 'jpc'; break;
			case 10: 
				$this -> type = 'jp2'; break;
			case 11: 
				$this -> type = 'jpx'; break;
			case 12: 
				$this -> type = 'jb2'; break;
			case 13: 
				$this -> type = 'swc'; break;
			case 14: 
				$this -> type = 'iff'; break;
			case 15: 
				$this -> type = 'wbmp'; break;
			case 16: 
				$this -> type = 'xbm'; break;
			case 17: 
				$this -> type = 'ico'; break;
			default: 
				$this -> type = ''; break;
		}
	}
}