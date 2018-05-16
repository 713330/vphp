<?php
	#dbname
	define('DB_NAME','read');
    define('DB_TYPE', 'mysql');
	#hostname
    define('DB_MASTER', DB_NAME . '_master');
    define('DB_SLAVE', DB_NAME . '_slave');
    return [
        'database' => [
            DB_MASTER => array(
                'type'     => DB_TYPE,
                'host'     => "127.0.0.1",
                'port'     => 3306,
                'username' => 'root',
                'password' => 'IDDIYUEDU2017',
                'charset'  => 'utf8',
                'database' => DB_NAME,
            ),
            DB_SLAVE => array(
                'type'     => DB_TYPE,
                'host'     => '127.0.0.1',
                'port'     => 3306,
                'username' => 'root',
                'password' => 'IDDIYUEDU2017',
                'charset'  => 'utf8',
                'database' => DB_NAME,
            ),
        ]
    ];
