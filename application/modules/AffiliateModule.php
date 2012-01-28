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
 * BoardModule
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 29102011
 * @version 29102011
 */
class AffiliateModule extends Module
{
	
	public function run(array $args) {
		set_time_limit(300);
		
		$model = new Affiliate_Privatamateure();
		
		if (isset($_GET['thumbnails'])) {
			$thumb = Module::init('Thumb', $this);
			
			$ads = $model->fetchAllRemote();
			while (($promo = $ads->fetch_object()) != null) {
				$media = new Media();
								
				$media->mid = $promo->id;
				$media->promo = true;
				
				$media->username = $promo->username;
				$media->link = $promo->link;
				
				$path = $this->getConfig()->paths->uploads . '/promo/' . $media->mid . '.jpg';
				$media->image = $path;
				
				if (!file_exists($path)) {
					file_put_contents($path, file_get_contents($promo->image));
					$model->update(array('image' => $path), 'id = ' . $promo->id);
				}
				
				$thumb->getThumbnail($media, 63 - 4, 95 - 4);
				$thumb->getThumbnail($media, 63 * 3 - 4, 95 * 3 - 4);
				$thumb->getThumbnail($media, 63 * 2 - 4, 95 - 4);
				$thumb->getThumbnail($media, 63 * 2 - 4, 95 * 2 - 4);
				
				$thumb->getThumbnail($media, 142, 206);
				$thumb->getThumbnail($media, 90, 90);
			}
			
			exit;
		}
		
		if (($handle = fopen("http://www.privatamateure.com/csv_amateur.php?wmid=4963&campaign=0", "r")) !== FALSE) {
		    for ($i = 0; $i < 100; $i++) {
		    	$data = fgetcsv($handle, 1000, ";");
		    	
		    	if ($data[2] == 'w' && $data[3] < 24 && !strstr($data[7], 'keinevorschaubig')) {
		    		print_r($data);
		    		
		    		$model->insert(array(
		    			'id' => $data[0],
		    			'username' => $data[1],
		    			'link' => $data[6],
		    			'image' => $data[7] . '.jpg', // workaround for thumbmodule
		    		));
		    	} else {
		    		$i--;
		    	}
		    }
		    fclose($handle);
		}
	}
	
}