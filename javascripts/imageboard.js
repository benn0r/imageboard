/**
 * Copyright (c) 2010-2012 benn0r <benjamin@benn0r.ch>
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
 * @author benn0r <benjamin@benn0r.ch>
 */
function Imageboard(str_boardid, str_spacerid)
{
	
	////////////////////////////////////////////////////
	//                                                //
	// configuration                                  //
	//                                                //
	////////////////////////////////////////////////////
	this.fwidth         = 63;               // smallest width
	this.fheight        = 95;               // smallest height
	this.minwidth       = 4;                // minimum images count (horizontal)
	this.bufferright    = 200;              // buffer right
	this.unit           = 'px';             // unit for width, height etc., default = px
	this.imagetag       = 'div';            // tag for images in board (notice that ALL elements with this tag
	                                        // in the board will be uses by this script!)	
	this.boardid        = str_boardid;      // id-tag for board
	this.spacerid       = str_spacerid;     // id-tag for spacer-element
	this.rheight        = 0;                // real height for board (will be dynamic edited)
	this.grid           = new Array();      // grid with images (0 = free, 1 = blocked)
		
	////////////////////////////////////////////////////
	//                                                //
	// updates grid, calcs how many images per line   //
	//                                                //
	////////////////////////////////////////////////////
	this.updateGrid = function()
	{
		var board = document.getElementById(this.boardid);
		var gridsize1 = 200; // height
		var gridsize2 = board.offsetWidth / this.fwidth; // width
		this.grid = new Array();
		for(var i = 0; i < gridsize1; i=i+1) {
			this.grid[i] = new Array();
			for(var j = 0; j < gridsize2; j=j+1) {
				// default all elements are free
				this.grid[i][j] = 0;
			}
		}
	}
	
	////////////////////////////////////////////////////
	//                                                //
	// resizes board, normally called by              //
	// window.onresize or onload                      //
	//                                                //
	////////////////////////////////////////////////////
	this.resize = function()
	{
		this.reset();
		var board = document.getElementById(this.boardid);		
		var bwidth = board.offsetWidth - this.bufferright; // boardwith - buffer = width for images
		var diff = bwidth % this.fwidth;
		if(bwidth - diff >= this.minwidth * this.fwidth) {
			// set width for the board
			board.style.width = bwidth - diff + this.unit;
		}
		this.updateGrid();
		var images = this.getImages(this.imagetag); // import all images in board
		for(var i = 0; i < images.length; i=i+1) {
			var image = images[i];
			if(!this.isImageValid(image)) {
				continue;
			}
			// get next free position
			var next = this.getNextFree(0, 0, parseInt(image.style.height) / this.fheight, parseInt(image.style.width) / this.fwidth);
			if(next != false) {
				// position image
				image.style.top = next[0] * this.fheight + this.unit;
				image.style.left = next[1] * this.fwidth + this.unit;
				
//				if ($('.header').height() + parseInt(board.offsetTop) + parseInt(image.style.top) + 
//						parseInt(image.style.height) >= $(window).height()) {
//					image.style.display = 'none';
//				} else {
//					image.style.display = '';
					
					var startcoord = next;
					var endcoord = new Array();
					// calculate endcoordinates for block-function
					endcoord[0] = ((parseInt(image.style.top) + parseInt(image.style.height)) / this.fheight) - 1;
					endcoord[1] = ((parseInt(image.style.left) + parseInt(image.style.width)) / this.fwidth) - 1;
					if(this.dispatchAfterImage != null) {
						this.dispatchAfterImage(image, startcoord, endcoord);
					}
					this.block(startcoord, endcoord);
//				}
			}
			rheight = parseInt(image.style.top) + parseInt(image.style.height); // calculate realheight from actual image
			if(rheight > this.rheight) {
				this.rheight = rheight;
			}
		}
		if(this.dispatchAfterImages != null) {
			this.dispatchAfterImages();
		}
	}
	
	////////////////////////////////////////////////////
	//                                                //
	// resets any variables                           //
	//                                                //
	////////////////////////////////////////////////////
	this.reset = function()
	{
		this.rheight = 0;
	}
	
	////////////////////////////////////////////////////
	//                                                //
	// needs startcoordinates and endcoordinates in   //
	// grid to block elements in the grid.            //
	// blocked elements can't be used by new images   //
	//                                                //
	////////////////////////////////////////////////////
	this.block = function(startcoord, endcoord)
	{
		for(var i = startcoord[0]; i <= endcoord[0]; i=i+1) {
			for(var j = startcoord[1]; j <= endcoord[1]; j=j+1) {
				if(this.grid[i] && this.grid[i].length >= j) {
					// block element
					this.grid[i][j] = 1;
				}
			}
		}
	}
	
	////////////////////////////////////////////////////
	//                                                //
	// gets next free elements in grid                //
	//                                                //
	////////////////////////////////////////////////////
	this.getNextFree = function(start_i, start_j, height, width)
	{
		if(!start_i) { start_i = 0; }
		if(!start_j) { start_j = 0; }
		for(var i = start_i; i < this.grid.length; i=i+1) {
			if(this.grid[i]) {
				for(var j = start_j; j < this.grid[i].length; j=j+1) {
					if(this.grid[i] && this.grid[i][j] == 0) {
						var no = false;
						for(k = j; k < j + width; k=k+1) {
							if(this.grid[i].length <= k || this.grid[i][k] == 1) {
								// this position will overflow maximum width
								no = true;
							}
						}
						if(!no) {
							// we have found a good position
							var arr = new Array();
							arr[0] = i;
							arr[1] = j;
							return arr;
						}
					}
				}
			}
		}
		// no free position found
		return false;
	}
	
	this.getImages = function(imagetag) {
		return document.getElementById(this.boardid).getElementsByTagName(imagetag);
	}
	
	this.isImageValide = function(image) {
		return true;
	}
	
	this.dispatchAfterImages = function() {
		return true;
	}
	
	this.dispatchAfterImage = function() {
		return true;
	}
	
}