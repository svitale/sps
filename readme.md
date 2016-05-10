=============================
==How to set up SPS locally==
=============================

++++++++
+Apache+
++++++++
* Add the following to httpd.conf, replacing  "C:\xampp\htdocs\sps\www" and " C:\xampp\htdocs\sps\include\lib.php" with your local path:
--
alias /sps/ "C:\myLocalPath\htdocs\sps\www\"
<Directory "C:\myLocalPath\htdocs\sps\www">
    DirectoryIndex index.php
    php_value auto_prepend_file C:\xampp\htdocs\sps\include\lib.php
    #Options -Indexes MultiViews FollowSymLinks
    Options MultiViews FollowSymLinks
    AllowOverride None
    Order allow,deny
    allow from all
</Directory>
--

+++++++
+MySql+
+++++++
* Obtain a backup of tclab and restore it on a local MySql instance
* create the logging procedures defined in $SPS_root/db/procedures/

++++++++++
+PHP+Pear+
++++++++++
* Enable openssl support in php.  Make sure php.ini includes the line:
extension=php_openssl.dll

* Install phpCAS packages with Pear:
pear install  http://downloads.jasig.org/cas-clients/php/current.tgz

* Install TreeMenu with Pear:
pear install HTML_TreeMenu


+++++
+SPS+
+++++
* Create the SPS configuration file "\sps\include\configuration.php". A template is provided at \sps\include\configuration_template.php". Update it so that:
	$config['root_dir'] is the absolute path to the 'sps' directory
	the database credentials are correct


+++++++++++++++++++++++++++++
+APC - Alternative PHP Cache+
+++++++++++++++++++++++++++++
http://php.net/manual/en/book.apc.php
If APC is not enabled on your system, set to false in the config file "\sps\include\configuration.php".
	$config['apc_enabled'] = false;
