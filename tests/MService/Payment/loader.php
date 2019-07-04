<?php

const DOCUMENT_ROOT = "/Users/cntt-linhnguyen7/PhpstormProjects/payment/src/";

spl_autoload_register(function($className) {

    //Document Root for Autoloader

    $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
	include_once DOCUMENT_ROOT . $className . ".php";

});
//autoloaddd