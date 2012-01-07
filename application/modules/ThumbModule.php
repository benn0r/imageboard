<?php

/**
 * Copyright (c) 2012 benn0r <benjamin@benn0r.ch>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * ThumbModule
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 29102011
 * @version 29102011
 */
class ThumbModule extends Module
{
	
	public function sendHeaders() {
		// Alle möglichen Header mitsenden
		// Aus Performance gründen wollen wir vermeiden das ein Thumbnail pro Client mehrmals geladen werden muss	
		header('Content-Type: image/png');
		header('Expires: Sat, 26 Jul 2020 05:00:00 GMT'); 
		header('Last-Modified: Mon, 26 Jul 1997 05:00:00 GMT'); 
		header('Pragma: cache'); 
		header('Cache-Control: store, cache');
	}
	
	public function loadThumb($id, $w, $h, $type) {
		if (file_exists($path = $this->createThumbFilename($id, $w, $h, $type))) {
			$this->sendHeaders();
			return file_get_contents($path);
		}
		return false;
	}
	
	public function createThumbFilename(Media $media, $w, $h) {
		$type = explode('.', $media->image);
		
		if ($media->temp) {
			if (!is_dir($this->_config->paths->cache . '/' . session_id())) {
				// Existiert noch kein Ordner für dieses Bild legen wir den mal an
				mkdir($this->_config->paths->cache . '/' . session_id());
			}
			
			$filename = $this->_config->paths->cache . '/' . session_id() . '/' . md5($media->image . $w . $h) . '.' . $type[count($type) - 1];
			if (file_exists($filename)) {
				// Temporäre Thumbnails werden jedes mal neu erstellt
				unlink($filename);
			}
			
			// Pfad zurückgeben
			return $filename;
		} else {
			if (!is_dir($this->_config->paths->thumbs . '/' . $media->mid)) {
				// Existiert noch kein Ordner für dieses Bild legen wir den mal an
				mkdir($this->_config->paths->thumbs . '/' . $media->mid);
			}
			
			// Pfad zurückgeben
			return $this->_config->paths->thumbs . '/' . $media->mid . '/' . md5($media->mid . $w . $h) . '.' . $type[count($type) - 1];
		}
	}
	
	public function getThumbnail(Media $media, $w, $h) {
		$filename = $this->createThumbFilename($media, $w, $h);
		
		if (!file_exists($filename)) {
			// Thumbnails gibts noch nicht, also generieren
			
			$this->generateThumbnail($media, $w, $h);
			
			if (!file_exists($filename)) {
				// Gibts immer noch nicht? WTF?!?
				return false;
			}
		}
		
		// Jetzt gibts das Thumbnail aber
		return $filename;
	}
	
	public function generateThumbnail(Media $media, $width, $height) {
		
		$file = $media->image;

		$imagesize = getimagesize($file);
		
		// wir machen das ganze ein bisschen leserlicher
		$imagewidth = $imagesize[0];
		$imageheight = $imagesize[1];
		$imagetype = $imagesize[2];
		
		switch ($imagetype) {
			case 1: $image = imagecreatefromgif($file); break;
			case 2: $image = imagecreatefromjpeg($file); break;
			case 3: $image = imagecreatefrompng($file); break;
			default: $this->error();
		}
		
		// Maximalausmaße
		$maxthumbwidth = $width + 100;
		$maxthumbheight = $height + 100;
		$minthumbwidth = $width;
		$minthumbheight = $height;
		
		// Ausmaße kopieren, wir gehen zuerst davon aus, dass das Bild schon Thumbnailgröße hat
		$thumbwidth = $imagewidth;
		$thumbheight = $imageheight;
		
		// Breite skalieren falls nötig
		if ($thumbwidth > $maxthumbwidth) {
			$factor = $maxthumbwidth / $thumbwidth;
			$thumbwidth *= $factor;
			$thumbheight *= $factor;
		}
		if ($thumbwidth < $minthumbwidth) {
			$thumbwidth = $minthumbwidth;
		}
		// Höhe skalieren, falls nötig
		if ($thumbheight > $maxthumbheight) {
			$factor = $maxthumbheight / $thumbheight;
			$thumbwidth *= $factor;
			$thumbheight *= $factor;
		}
		
		if($thumbheight < $minthumbheight) {
			$thumbheight = $minthumbheight;
		}
		if($thumbwidth < $minthumbwidth) {
			$diff = $minthumbwidth - $thumbwidth;
			$thumbwidth = $minthumbwidth;
			$thumbheight += $diff;
		}
		
		// Thumbnail erstellen
		$thumb = imagecreatetruecolor($thumbwidth, $thumbheight);
		$new = imagecreatetruecolor($width, $height);
		imagecopyresampled(
			$thumb,
			$image,
			0, 0, 0, 0, // Startposition des Ausschnittes
			$thumbwidth,
			$thumbheight,
			$imagewidth,
			$imageheight
		);
		$left = $thumbwidth;
		$top = $thumbheight;
		$left = ceil($left / 2) - ($width / 2);
		$top = ceil($top / 2) - ($height / 2);
		imagecopy($new, $thumb, 0, 0, $left, $top, $width, $height);
		
		/*$result = $this->_db->exec('SELECT * FROM
		board_media WHERE mid = ' . $mid);
		$result = $result->fetch_object();
		if ($result->type > 1) {
			if ($width == 50 || $height == 50) {
				$play = imagecreatefrompng('images/play_small.png');
				imagecopy($new, $play, 3, $height - 20 - 3, 0, 0, 24, 20); 
			} else {
				$play = imagecreatefrompng('images/play.png');
				imagecopy($new, $play, 3, $height - 29 - 3, 0, 0, 35, 29);
			}
		}*/
		
		imagepng($new, $this->createThumbFilename($media, $width, $height));
	}
	
	public function error() {
		trigger_error('404 Page not found', E_USER_ERROR);
	}
	
}