<?php
session_start();
define("__BASEDIR__", __DIR__);
define("__APPDIR__", __BASEDIR__.'/_app');
require_once(__DIR__.'/resources/FMAC/_php/autoload.php');
setDebug(true);
autoLoadPHP(__BASEDIR__.'/_data/php/');

use FMAC\MVC\TModel;
TModel::connectDB(__BASEDIR__.'/_data/database/database.ini');

use FMAC\Utils\TTranslator;
$translator = new TTranslator(__BASEDIR__.'/_data/region');
$translator->setLanguage( ifset($_SESSION, 'language', '') );

?>
