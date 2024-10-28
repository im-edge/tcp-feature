<?php

/**
 * This is an IMEdge Node feature
 *
 * @var Feature $this
 */

use IMEdge\Node\Feature;
use IMEdge\TcpFeature\TcpApi;

$this->registerRpcApi(new TcpApi());
