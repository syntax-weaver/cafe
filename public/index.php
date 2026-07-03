<?php

session_start();

require "../app/helpers/functions.php";
require base_path("app/core/Router.php");
require base_path("app/core/Database.php");

$routes = require base_path("routes/web.php");
$r = new Router($routes);
$r->resolve();

clear_flash();
