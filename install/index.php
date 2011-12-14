<?php

/**
 * Imageboard Installer
 */

// Folder for uploads
if (!is_dir('../uploads')) {
	mkdir('../uploads');
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
if (!is_dir('../thumbs')) {
	mkdir('../thumbs');
}