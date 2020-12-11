<?php
declare(strict_types=1);

# Force timezone and charset
date_default_timezone_set('EST');
ini_set('default_charset', 'UTF-8');

spl_autoload_register(function ($class) {
    include "classes/$class.php";
});
