<?php

if (!defined('IN_SCRIPT')) { die('Access denied'); }


class Image
{
	/**
	 * Save nfo as png
	 * 
	 * @access public
	 * @param $nfo string NFO
	 * @param $file string Speicherort
	 */
	public static function nfo2png($nfo, $file)
	{
		$lines = explode("\n", $nfo);								   
		$longestLine = 0; 
		$numberLines = 0; 
	
		foreach ($lines as $line_num => $line) 
		{ 
			$line = rtrim($line); 
			$tempLineLength = strlen($line); 
			
			if ($tempLineLength > $longestLine)
			{
				$longestLine = $tempLineLength;
			}
		} 
	
		$numberLines = count($lines); 
	    $fontWidth  = 8;
	    $fontHeight = 12;
		$border = 1;
		$imgWidth = ($fontWidth * $longestLine) + (2 * $border * $fontWidth);
	    $imgHeight = ($fontHeight * $numberLines) + (2 * $border * $fontHeight); 
		$currentX = 0;
	    $currentY = 0;
		$img = imagecreatetruecolor($imgWidth, $imgHeight);
		$charMap = imagecreatefrompng(Settings::get('corePath').'includes/library/Image/charmap-20-255.png');
	
		for ($j = 0; $j < $numberLines + 2 * $border; $j++)
	    {
			$currentLineLength = strlen($line);
	            
			for ($i = 0; $i < $longestLine + 2 * $border; $i++)
	        {
				imagecopy($img, $charMap, $currentX, $currentY, ($fontWidth * 12), ($fontHeight * 0), $fontWidth, $fontHeight);
	            $currentX = $currentX + $fontWidth;
	        }
			
	        $currentX = 0;
	        $currentY = $currentY + $fontHeight;
	    }
	
	    $currentX   = $border * $fontWidth; 
	    $currentY   = $border * $fontHeight;
	
		foreach ($lines as $line_num => $line)
	    {
	    	$currentLineLength = strlen($line);
	            
			for ($i = 0; $i < $currentLineLength; $i++)
	        {
	        	$charYOffSet = 0;
	            $charXOffSet = ord($line[$i]);
	
	            while ($charXOffSet >= 20)
	            {
	            	$charYOffSet++;
	                $charXOffSet = $charXOffSet - 20;
	            }
	            
				imagecopy($img, $charMap, $currentX, $currentY, ($fontWidth * $charXOffSet), ($fontHeight * $charYOffSet), $fontWidth, $fontHeight); 
	            $currentX = $currentX + $fontWidth; 
	        }
			
	        $currentX = ($border * $fontWidth);
	        $currentY = $currentY + $fontHeight; 
		}
	
		imagepng($img, $file);
	    imagedestroy($img);
	} 
}

?>