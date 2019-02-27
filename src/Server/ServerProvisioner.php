<?php

namespace Scp\Server;

use Scp\Api\Api;
use Scp\Client\Client;

/**
 * Provision Servers.
 *
 * TODO: Rename to ServerInventory, rename server method to provision
 */
class ServerProvisioner
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * @var ServerRepository
     */
    protected $servers;

    /**
     * @param Api|null $api
     */
    public function __construct(Api $api = null)
    {
        $this->api = $api ?: Api::instance();
        $this->servers = new ServerRepository($this->api);
    }

    /**
     * Provision a Server according to the given filters and return it.
     * Returns null if no server matching the given filters is found.
     *
     * @param  array  $filters
     * @param  array  $set
     * @param  Client $client
     *
     * @return Server|void
     */
    public function server(array $filters, array $set, Client $client)
    {
        if (!$server = $this->getServer($filters)) {
            return;
        }

        $provisionData = [
            'client_id' => $client->id,
            'server_id' => $server->id,
        ] + $set;
        $result = $this->api->post('server/provision', $provisionData);

        debug($result);
        die();

        $data = $result->data();

        if (!$data) {
            return;
        }

        $server = new Server((array) $data->server, $this->api);
        $server->setExists(true);

        return $server;
    }

    /**
     * @param array $filters
     *
     * @return Server|void
     */
    public function getServer(array $filters)
    {
        $filters = $this->addDefaultFilters($filters);

        return $this->servers
            ->query()
            ->where($filters)
            ->first()
            ;
    }



    /**
     * @param array $filters
     *
     * @return array
     */
    private function addDefaultFilters(array $filters)
    {
        return array_merge(
            $filters,
            [
                'available' => true,
                'parts' => [
                    'exact' => true,
                ],
            ]
        );
    }
}
