<?php
require_once "./database/Database.php";
require_once './pbs/Router.php';
require_once "./pbs/Pbs.php";

require_once "./mvc/Film.model.php";

Database::connect();
Router::create();
