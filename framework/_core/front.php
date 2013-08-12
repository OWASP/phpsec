<?php
namespace phpsec\framework;
use phpsec\HttpRequest;
require_once __DIR__."/autoload.php";
require_once __DIR__."/loader.php";

var_dump(HttpRequest::InternalPath());
var_dump(HttpRequest::QueryString());
var_dump($_GET);
