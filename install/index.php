<?php

/**
 * Imageboard Installer
 */

// Folder for uploads
if (!is_dir('../uploads')) {
	mkdir('../uploads');
}

// Folder for cache (upload needs that)
if (!is_dir('../cache')) {
	mkdir('../cache');
}

// Folder for Avatars
if (!is_dir('../uploads/avatars')) {
	mkdir('../uploads/avatars');
}

// Folder for Profileheaders
if (!is_dir('../uploads/headers')) {
	mkdir('../uploads/headers');
}

// Folder for thumbnails
if (!is_dir('../uploads/thumbs')) {
	mkdir('../uploads/thumbs');
}