<?php
namespace Lubos\Wedos\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Network\Http\Client;
use Cake\Utility\String;
use Cake\Utility\Xml;

class DNSShell extends Shell
{

    /**
     * Main function Prints out the list of shells.
     *
     * @return void
     */
    public function main()
    {
        $this->out($this->OptionParser->help());
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->addSubcommand('rowsList');
        $parser->addSubcommand('domainInfo');
        $parser->addSubcommand('rowAdd', [
            'help' => 'DNS row add',
            'parser' => [
                'description' => implode(PHP_EOL, [
                    'Example:',
                    '',
                    'wedos rowAdd domain.cz 1.2.3.4 --name mail',
                    '# Adds A record mail.domain.cz with IP 1.2.3.4',
                    '',
                    'wedos rowAdd domain.cz "10 mail.domain.cz" --type MX',
                    '# Adds MX record'
                ]),
                'arguments' => [
                    'domain' => [
                        'help' => 'Domain name',
                        'required' => true
                    ],
                    'rdata' => [
                        'help' => 'Record data (e.g. IP address)',
                        'required' => true
                    ]
                ],
                'options' => [
                    'name' => [
                        'help' => 'Record name (default empty)'
                    ],
                    'ttl' => [
                        'help' => 'TTL (default 1800)'
                    ],
                    'type' => [
                        'help' => 'Record type (default A)'
                    ],
                ]
            ]
        ]);
        $parser->addSubcommand('rowDelete', [
            'help' => 'DNS row delete',
            'parser' => [
                'arguments' => [
                    'domain' => [
                        'help' => 'Domain name',
                        'required' => true
                    ],
                    'row_id' => [
                        'help' => 'Record ID',
                        'required' => true
                    ]
                ]
            ]
        ]);
        $parser->addSubcommand('rowDeleteAll', [
            'help' => 'DNS delete all rows',
            'parser' => [
                'arguments' => [
                    'domain' => [
                        'help' => 'Domain name',
                        'required' => true
                    ]
                ]
            ]
        ]);
        return $parser;
    }

    /**
     * Getting domain DNS info
     *
     * @param string $domain Domain to check.
     * @return mixed
     */
    public function domainInfo($domain)
    {
        $client = new Client();
        $request = Xml::fromArray([
            'request' => [
                'user' => Configure::read('Wedos.user'),
                'auth' => sha1(implode([
                    Configure::read('Wedos.user'),
                    sha1(Configure::read('Wedos.password')),
                    date('H', time())
                ])),
                'command' => 'dns-domain-info',
                'data' => [
                    'name' => $domain
                ]
            ]
        ])->asXml();
        $response = $client->post(
            Configure::read('Wedos.url'),
            ['request' => $request],
            ['type' => 'xml']
        );
        if ($response->isOk()) {
            $results = Xml::toArray($response->xml);
            $this->out(print_r($results['response'], true));
            return $results;
        } else {
            debug($response->code);
        }
    }

    /**
     * Getting domain DNS list
     *
     * @param string $domain Domain to check.
     * @return mixed
     */
    public function rowsList($domain)
    {
        $client = new Client();
        $request = Xml::fromArray([
            'request' => [
                'user' => Configure::read('Wedos.user'),
                'auth' => sha1(implode([
                    Configure::read('Wedos.user'),
                    sha1(Configure::read('Wedos.password')),
                    date('H', time())
                ])),
                'command' => 'dns-rows-list',
                'data' => [
                    'domain' => $domain
                ]
            ]
        ])->asXml();
        $response = $client->post(
            Configure::read('Wedos.url'),
            ['request' => $request],
            ['type' => 'xml']
        );
        if ($response->isOk()) {
            $results = Xml::toArray($response->xml);
            $this->out(print_r($results['response'], true));
            return $results;
        } else {
            debug($response->code);
        }
    }

    /**
     * DNS row add
     *
     * Example:
     * wedos dnsRowAdd domain.cz 1.2.3.4 --name mail # Adds A record mail.domain.cz with IP 1.2.3.4
     * wedos dnsRowAdd domain.cz "10 mail.domain.cz" --type MX # Adds MX record
     *
     * options via parser
     * - name Record name (default www).
     * - ttl TTL (default 1800).
     * - type Record type (default A).
     *
     * @param string $domain Domain name.
     * @param string $rdata Record data (e.g. IP address).
     * @return mixed
     */
    public function rowAdd($domain, $rdata)
    {
        extract($this->params);
        if (!isset($name)) {
            $name = ' ';
        }
        if (!isset($ttl)) {
            $ttl = 1800;
        }
        if (!isset($type)) {
            $type = 'A';
        }
        $client = new Client();
        $request = Xml::fromArray([
            'request' => [
                'user' => Configure::read('Wedos.user'),
                'auth' => sha1(implode([
                    Configure::read('Wedos.user'),
                    sha1(Configure::read('Wedos.password')),
                    date('H', time())
                ])),
                'command' => 'dns-row-add',
                'data' => [
                    'domain' => $domain,
                    'name' => $name,
                    'ttl' => $ttl,
                    'type' => $type,
                    'rdata' => $rdata,
                ]
            ]
        ])->asXml();
        $response = $client->post(
            Configure::read('Wedos.url'),
            ['request' => $request],
            ['type' => 'xml']
        );
        if ($response->isOk()) {
            $results = Xml::toArray($response->xml);
            $this->out(print_r($results['response'], true));
            return $results;
        } else {
            debug($response->code);
        }
    }

    /**
     * DNS row delete
     *
     * @param string $domain Domain name.
     * @param string $rowID Record ID (dnsRowsList to see IDs).
     * @return mixed
     */
    public function rowDelete($domain, $rowID)
    {
        $client = new Client();
        $request = Xml::fromArray([
            'request' => [
                'user' => Configure::read('Wedos.user'),
                'auth' => sha1(implode([
                    Configure::read('Wedos.user'),
                    sha1(Configure::read('Wedos.password')),
                    date('H', time())
                ])),
                'command' => 'dns-row-delete',
                'data' => [
                    'domain' => $domain,
                    'row_id' => $rowID
                ]
            ]
        ])->asXml();
        $response = $client->post(
            Configure::read('Wedos.url'),
            ['request' => $request],
            ['type' => 'xml']
        );
        if ($response->isOk()) {
            $results = Xml::toArray($response->xml);
            $this->out(print_r($results['response'], true));
            return $results;
        } else {
            debug($response->code);
        }
    }

    /**
     * DNS delete all rows
     *
     * @param string $domain Domain name.
     * @return bool
     */
    public function rowDeleteAll($domain)
    {
        $records = $this->dnsRowsList($domain);
        foreach ($records['response']['data']['row'] as $row) {
            $this->rowDelete($domain, $row['ID']);
        }
        return true;
    }
}
