Global Search in Moodle using Apache Solr PHP extension
=======================================================


Downloading PHP Solr extension
------------------------
* Downloading extension for Apache Solr 4.x
Apache Solr 4.x requires installation of php solr extension version `1.0.3-alpha`. You will be ready to install the PECL extension for Solr by cloning the following repository for `Solr 4.x`. (Please Note: Currently, the official `php-pecl-solr` is not compatible with `Solr 4.x`. The following repository provides a small fix to make it compatible with `Solr 4.x` and will go to the official release).
`git clone https://github.com/lukaszkujawa/php-pecl-solr.git`

* Downloading extension for Apache Solr 3.x
Apache Solr 3.x requires installation of php solr extension version `<=1.0.2`. You can download the official latest versions from http://pecl.php.net/package/solr. Extract the contents into a directory.

Installing the downloaded PHP Solr extension
--------------------------------------------
For using Global Search, users will have to install the PHP Solr PECL extension on server. Users will have the option of configuring Solr version in  Global Search. 
Following is the procedure for installing the downloaded extension in UNIX:

There are two dependencies of the extension:
* CURL extension (libcurl 7.15.0 or later is required)
* LIBXML extension (libxml2 2.6.26 or later is required)

On Debian and derivatives you can simply execute:
	`apt-get install libxml2-dev libcurl4-openssl-dev`

After installing the above dependencies, you will need to restart your apache server by executing `service apache2 restart`.

`cd /your-downloaded-or-cloned-directory/`
*`phpize`
**This a shell script used to prepare the build environment for a php extension to be compiled. If you don't have `phpize`, you can install it by executing `sudo apt-get install php5-dev`
*sudo make
*sudo make install

The above procedure will compile and install it in the `extension_dir` directory in the `php.ini` file. To enable, the installed extension, you could follow any of the following two steps:

1. Navigate to the directory `/etc/php5/conf.d` and create a new `solr.ini` file with the following line:
 extension=solr.so

OR

2. Open your `php.ini` file and include the following line:
 extension=solr.so

You may follow any of the above two steps. You will need to restart your apache server after that by executing `sudo service apache2 restart`

You can now view the '''solr''' extension details by clicking '''PHP info''' from Site administration > Server in browser or `php -m` in Terminal (`Ctrl+Alt+T`)

Download and Installation - OSX using macports
----------------------------------------------
This method provides an easy install of php solr extension without any downloads.(php solr extension version: `<=1.0.2`)
 - sudo port install apache-solr4
 - sudo port install php54-solr

you can choose your relevant available versions @ http://www.macports.org/ports.php?by=name&substr=solr

Solr installation
--------------------

After installing the php-pecl-solr extension, users will have to download the required [http://lucene.apache.org/solr/ Apache Solr] release (version 4.x for solr-php extension `1.0.3-alpha` or 3.x for solr-php extension version `<=1.0.2`), unzip it and keep it in an external directory of Moodle.

Users will have to replace `solconfig.xml` and `schema.xml` inside the downloaded directory `/example/solr/conf` with the ones that Global Search will provide in `/search/solr/conf/` directory. 

Once the files have been copied and replaced, users will have to start the java jetty server `start.jar` located in `/example/` directory by executing `java -jar start.jar`. For the production setup you may prefert to [http://jmuras.com/blog/2012/setup-solr-4-tomcat-ubuntu-server-12-04-lts run solr on tomcat 6 or 7] and Ubuntu server.
