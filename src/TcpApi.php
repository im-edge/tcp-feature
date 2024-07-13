<?php

namespace IMEdge\TcpFeature;

use Amp\Cancellation;
use Amp\DeferredCancellation;
use Amp\Socket\ConnectContext;
use Exception;
use IMEdge\Protocol\NTP\Util;
use IMEdge\RpcApi\ApiMethod;
use IMEdge\RpcApi\ApiNamespace;

use function Amp\async;
use function Amp\Future\awaitAll;
use function Amp\Socket\connect;
use function implode;
use function ksort;
use function hrtime;

#[ApiNamespace('tcp')]
class TcpApi
{
    protected ConnectContext $connectContext;

    /** @var Cancellation[] */
    protected array $pendingCancellations = [];

    public function __construct()
    {
        $this->connectContext = (new ConnectContext)->withConnectTimeout(5);
    }

    /**
     * @param string $ip    Ip Address
     * @param int    $port  Port (0-65535)
     */
    #[ApiMethod]
    public function check(string $ip, int $port): array
    {
        $cancellation = new DeferredCancellation();
        $this->pendingCancellations[] = $cancellation;
        $result = [
            'reachable' => false,
            'message'   => '',
        ];

        try {
            $ips = Util::getIps($ip);
            if (empty($ips)) {
                throw new \InvalidArgumentException("Failed to resolve $ip");
            }
            $futures = [];
            foreach ($ips as $ip) {
                $futures[$ip] = async(function () use ($ip, $port, $cancellation) {
                    $start = hrtime(true);
                    try {
                        $socket = connect("$ip:$port", $this->connectContext, $cancellation->getCancellation());
                        $duration = hrtime(true) - $start;
                        $socket->close();
                        return [
                            'reachable' => true,
                            'message'   => sprintf(
                                'Successfully connected to %s:%s via TCP in %.2Fms',
                                $ip,
                                $port,
                                $duration * 1_000_000
                            )
                        ];
                    } catch (Exception $e) {
                        $duration = hrtime(true) - $start;
                        return [
                            'reachable' => false,
                            'message' => sprintf(
                                'Failed to connect to %s:%s after %.2Fms', // . $e->getMessage(),
                                $ip,
                                $port,
                                $duration * 1_000_000
                            )
                        ];
                    }
                });
            }
            [$errors, $results] = awaitAll($futures);
            foreach ($errors as $error) {
                throw $error; // There should be none
            }
            $messages = [];
            foreach ($results as $ip => $singleResult) {
                if ($singleResult['reachable']) {
                    $result['reachable'] = true;
                }
            }
            ksort($messages);
            $result['message'] = implode(', ', $messages);
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
        }

        return $result;
    }

    public function stop(): void
    {
        foreach ($this->pendingCancellations as $cancellation) {
            $cancellation->throwIfRequested(); // TODO: verify this
        }
    }
}
