# About
This software is an imageboard. In an imageboard you can upload photos (and videos) and 
discuss with others about this contents.
There exists an old version of this project at (www.4fag.com), i decided to make the new
version via github.

# Installation
1. Copy sql statements in `install/sql/tables.sql` into your database
2. Open the configfile `config/application.ini` and fill in the correct database logindata
3. After that, start the installer in `install/index.php`
4. Maybe you have to change permissions of `uploads/`, `cache` and `thumbs/` to readwrite