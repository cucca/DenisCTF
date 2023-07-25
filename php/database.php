<?php

$config = [
    'db_engine'   => 'mysql',
    'db_host'     => '192.168.1.45',
    'db_name'     => 'certificates',
    'db_user'     => 'root',
    'db_password' => 'k3oth4mc',
    'ROOT_URL'    => '/cert/',
    'ROOT_DIR'    => '/var/www/html/cert/',
];

$db_config = $config['db_engine'] . ":host=".$config['db_host'] . ";dbname=" . $config['db_name'];

try {
    $pdo = new PDO($db_config, $config['db_user'], $config['db_password'], [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
    ]);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    exit("Impossibile connettersi al database: " . $e->getMessage());
}