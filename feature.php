<?php

/**
 * This is an IMEdge Node feature
 *
 * @var Feature $this
 */

use IMEdge\Node\Feature;
use IMEdge\TcpFeature\TcpApi;

require __DIR__ . '/vendor/autoload.php';
$this->registerRpcApi(new TcpApi());
