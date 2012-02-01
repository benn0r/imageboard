<?php

/**
 * CWPAGINATION v0.1
 *
 * CWPAGINATION for easy content pagination
 * (c) 2008 Christian Weber <christian@ghostgames.net>
 * CW-Internetdienste.de / uwserver.de
 *
 * CWPAGINATION is freely distributable under the MIT Licence
 *
 */ 
 class cwpagination {
	var $currentp = 0;
	var $paginations = array();
 
 	public function __constructor() {
		$this->paginations[0]['current'] = 1;
		$this->paginations[0]['style'] = array();
		$this->paginations[0]['amount'] = 0;
		$this->paginations[0]['link'] = 0;
		$this->paginations[0]['content'] = 0;
		$this->paginations[0]['pages'] = 0;
		$this->paginations[0]['jump'] = false;
		$this->paginations[0]['root'] = false;
		$this->paginations[0]['rootaddress'] = "";
	}
	
	public function setid($id=0) {
		$this->currentp = (int)$id;
	}
	
	public function setcontent($a=0) {
		$this->paginations[$this->currentp]['content'] = (int)$a;
	}
	
	public function newpagination() {
		$this->currentp = (int)count($this->paginations)+1;
		$this->paginations[$this->currentp]['current'] = 1;
		$this->paginations[$this->currentp]['style'] = array();
		$this->paginations[$this->currentp]['amount'] = 0; 
		$this->paginations[$this->currentp]['link'] = 0;
		$this->paginations[$this->currentp]['content'] = 0;
		$this->paginations[$this->currentp]['pages'] = 0;
		$this->paginations[$this->currentp]['pages'] = false;
		$this->paginations[$this->currentp]['root'] = false;
		$this->paginations[$this->currentp]['rootaddress'] = "";
	}
	
	public function setroot($a=true,$d="") {
		$this->paginations[$this->currentp]['root'] = $a;
		if(!empty($d)) {
			$this->paginations[$this->currentp]['rootaddress'] = $d;
		}
	}
	
	public function setjump($a=true) {
		$this->paginations[$this->currentp]['jump'] = $a;
	}
	
	public function setpage($p=1) {
		$this->paginations[$this->currentp]['current'] = (int)$p;
	}
	
	public function setlink($link) {
		if(!empty($link)) {
			if(preg_match("[$$$]",$link)) {
				$this->paginations[$this->currentp]['link'] = $link;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	public function setamount($a=0) {
		$this->paginations[$this->currentp]['amount'] = (int)$a;
	}
	
	public function setstyle($type="class",$value="pagination") {
		if($type == "class") {
			$this->paginations[$this->currentp]['style']['type'] = "class";
			$this->paginations[$this->currentp]['style']['value'] = $value;
			return true;
		} elseif($type == "style") {
			$this->paginations[$this->currentp]['style']['type'] = "style";
			$this->paginations[$this->currentp]['style']['value'] = $value;
			return true;
		} else {
			return false;
		}
	}
	
	private function calculate() {
		$current = $this->paginations[$this->currentp];
		if($current['amount'] && $current['link'] && $current['content'] && $current['style']) {
			$pages = ceil($current['content']/$current['amount']);
			$this->paginations[$this->currentp]['pages'] = $pages;
		} else {
			return false;
		}
	}
	
	public function normaloutput() {
		$c = $this->paginations[$this->currentp];
		if(!$c['style'] || !$c['link'] || !$c['content'] || !$c['amount']) {
			return false;
		} else {
			if(!$c['pages']) {
				$this->calculate();
				unset($c);
				$c = $this->paginations[$this->currentp];
			} 
			if($c['style']['type'] == 'class') {
				echo '<ul class="'.$c["style"]["value"].'">';
			} else {
				echo '<ul style="'.$c["style"]["value"].'">';
			}
			
			if($c['current'] > 1) {
				if($c['jump']) {
					if($c['root']) {
						if($c['rootaddress']) {
							 $link = $c['rootaddress'];
						} else {
							$link = $_SERVER['PHP_SELF'];
						}
						echo '<li class="prev"><a onclick="return loadpage(this);" href="'.str_replace("\$\$\$",1,$c['link']).'">&laquo First Page</a></li>';
					} else {
						echo '<li class="prev"><a onclick="return loadpage(this);" href="'.str_replace("\$\$\$",1,$c['link']).'">&laquo First Page</a></li>';
					}
				}
				echo '<li class="previous"><a onclick="return loadpage(this);" href="'.str_replace("\$\$\$",($c['current']-1),$c['link']).'">&laquo</a></li>';
			} else {
				if($c['jump']) {
					//echo '<li class="previous-off"><span class="off">&laquo First Page</span></li>';
				}
				echo '<li class="prev disabled"><a href="" onclick="return false;">&laquo</a></li>';
			}
			
			if($c['pages'] > 10) {
				if($c['current'] >= 5 && $c['current'] <= ($c['pages']-5)) {
					$x = $c['current']-4;
					$max = $c['current']+6;
				} else {
					if($c['current'] <= 5) {
						$x = 1;
						$max = 11;
					} elseif($c['current']>=($c['pages']-5)) {
						$x = $c['pages']-9;
						$max = $c['pages']+1;
					}
				}
			} else {
				$x = 1;
				$max = ($c['pages']+1);
			}
			
			for($i=$x;$i<$max;$i++) {
				
				if($c['current'] == $i) {
					echo '<li class="active"><span><a onclick="return loadpage(this);" href="'.str_replace("\$\$\$",1,$c['link']).'">'.$i.'</a></span></li>';	
				} else {
					if($c['root'] && $i==1) {
						if($c['rootaddress']) {
							 $link = $c['rootaddress'];
						} else {
							$link = $_SERVER['PHP_SELF'];
						}
						echo '<li><a onclick="return loadpage(this);" href="'.str_replace("\$\$\$",1,$c['link']).'">'.$i.'</a></li>';	
					} else {
						echo '<li><a onclick="return loadpage(this);" href="'.str_replace("\$\$\$",$i,$c['link']).'">'.$i.'</a></li>';	
					}
				}
				
			}
			
			if($c['current'] < $c['pages']) {
				
				echo '<li class="next"><a onclick="return loadpage(this);" href="'.str_replace("\$\$\$",($c['current']+1),$c['link']).'">&raquo;</a></li>';
				if($c['jump']) {
					echo '<li class="next"><a onclick="return loadpage(this);" href="'.str_replace("\$\$\$",$c['pages'],$c['link']).'">&raquo;</a></li>';
				}
			} else {
				
				echo '<li class="next disabled"><a href="" onclick="return false;">&raquo;</a></li>';
				if($c['jump']) {
					//echo '<li class="next-off"><span class="off">Last Page </span></li>';
				}
			}
			echo "</ul>";
			
		}
	}
	
	public function __deconstructor() {
	
	}
 
 
 }
 
 
 ?>