<?php

use gcatlin\dopester as dopester;

// This require is not necessary if you installed via Composer
require __DIR__ . '/../src/bootstrap.php';

dopester\Toolbar::register();

header('Location: simple.php');
