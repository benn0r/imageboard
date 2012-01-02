# Imageboard
This project is still in development!

### About
This software is an imageboard. (Who who, what a sentence). In an imageboard you can upload photos (and videos) and discuss with others about this contents.
There exists an old version of this project at (www.4fag.com), i decided to make the new
version via github.

### Planed features
* anonymous and registered users
* upload function for images (local) and links,images,videos(remote, e.g. youtube, vimeo)
* comments
* ratings
* search enginge
* profile with stats
* profile comments
* very much things configurable via .ini-files

### Installation
1. Copy sql statements in `install/sql/tables.sql` into your database
2. Open the configfile `config/application.ini` and fill in the correct database logindata
3. After that, start the installer in `install/index.php`
4. Maybe you have to change permissions of `uploads/`, `cache` and `thumbs/` to readwrite