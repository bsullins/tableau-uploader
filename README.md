tableau-uploader
================

Allows users of Tableau Server to upload .csv data w/o the use of Tableau Desktop

Setup
================
* update $tabcmd and $tabLogin in upload.php
* update mail() to your alert distro in upload.php
* create 'uploads' folder
* update tableau server url on index.php
* add some security checks at the beginning of index.php
* add ajax spinner image in the index.php
* update source/dest directories in extract-upload.py
