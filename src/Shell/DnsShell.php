<?php
namespace Lubos\Wedos\Shell;

use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\Utility\Xml;

class DnsShell extends WedosShell
{

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
        $parser->addSubcommand('rowAddBatch', [
            'help' => 'DNS row add batch',
            'parser' => [
                'description' => 'Batch DNS row add via passed json file.',
                'arguments' => [
                    'config' => [
                        'requred' => true
                    ]
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
     * @return \Cake\Network\Http\Response
     */
    public function domainInfo($domain)
    {
        $this->request['request']['command'] = 'dns-domain-info';
        $this->request['request']['data'] = [
            'name' => $domain
        ];
        $request = Xml::fromArray($this->request)->asXml();
        $response = $this->client->post(
            $this->url,
            ['request' => $request],
            ['type' => 'xml']
        );
        if ($response->isOk()) {
            $results = Xml::toArray($response->xml);
            $this->out(pr($results['response']));
        } else {
            debug($response);
        }
        return $response;
    }

    /**
     * Getting domain DNS list
     *
     * @param string $domain Domain to check.
     * @return \Cake\Network\Http\Response
     */
    public function rowsList($domain)
    {
        $this->request['request']['command'] = 'dns-rows-list';
        $this->request['request']['data'] = [
            'domain' => $domain
        ];
        $request = Xml::fromArray($this->request)->asXml();
        $response = $this->client->post(
            $this->url,
            ['request' => $request],
            ['type' => 'xml']
        );
        if ($response->isOk()) {
            $results = Xml::toArray($response->xml);
            $this->out(pr($results['response']));
        } else {
            debug($response);
        }
        return $response;
    }

    /**
     * DNS row add
     *
     * Example:
     * wedos dnsRowAdd domain.cz 1.2.3.4 --name mail # Adds A record mail.domain.cz with IP 1.2.3.4
     * wedos dnsRowAdd domain.cz "10 mail.domain.cz" --type MX # Adds MX record
     *
     * options via parser
     * - name Record name (default empty).
     * - ttl TTL (default 1800).
     * - type Record type (default A).
     *
     * @param string $domain Domain name.
     * @param string $rdata Record data (e.g. IP address).
     * @return \Cake\Network\Http\Response
     */
    public function rowAdd($domain, $rdata)
    {
        $data = [
            'domain' => $domain,
            'rdata' => $rdata,
        ];
        unset($this->params['help']);
        unset($this->params['verbose']);
        unset($this->params['quiet']);
        if (!empty($this->params)) {
            $data = array_merge($data, $this->params);
        }
        if (!isset($data['name'])) {
            $data['name'] = ' ';
        }
        if (!isset($data['ttl'])) {
            $data['ttl'] = '1800';
        }
        if (!isset($data['type'])) {
            $data['type'] = 'A';
        }
        $this->request['request']['command'] = 'dns-row-add';
        $this->request['request']['data'] = $data;
        $request = Xml::fromArray($this->request)->asXml();
        $response = $this->client->post(
            $this->url,
            ['request' => $request],
            ['type' => 'xml']
        );
        if ($response->isOk()) {
            $results = Xml::toArray($response->xml);
            $this->out(pr($results['response']));
        } else {
            debug($response);
        }
        return $response;
    }

    /**
     * Reads json config and add DNS records
     *
     * @param string $config Path to json file.
     * @return bool
     */
    public function rowAddBatch($config)
    {
        $timeStart = microtime(true);
        if (!is_array($config)) {
            $file = new File($config);
            if (!$file->exists()) {
                $this->err('<error>Error</error>: config file does not exist.');
                $this->hr();
                return false;
            }
            $json = $file->read();
            $config = json_decode($json, true);
            if (!$config) {
                $this->err('<error>Error</error>: file is not in valid json format.');
                $this->hr();
                return false;
            }
        }
        $this->out(sprintf('%s DNS records to add', count($config)));
        foreach ($config as $item) {
            $domain = $item['domain'];
            $rdata = $item['rdata'];
            unset(
                $item['domain'],
                $item['rdata']
            );
            $this->params = $item;
            $this->rowAdd($domain, $rdata);
        }
        $this->out(sprintf(
            '<success>rowAddBatch finished in %ss</success>',
            round(microtime(true) - $timeStart, 2)
        ));
        $this->hr();
        return true;
    }

    /**
     * DNS row delete
     *
     * @param string $domain Domain name.
     * @param string $rowID Record ID (dnsRowsList to see IDs).
     * @return \Cake\Network\Http\Response
     */
    public function rowDelete($domain, $rowID)
    {
        $this->request['request']['command'] = 'dns-row-delete';
        $this->request['request']['data'] = [
            'domain' => $domain,
            'row_id' => $rowID
        ];
        $request = Xml::fromArray($this->request)->asXml();
        $response = $this->client->post(
            $this->url,
            ['request' => $request],
            ['type' => 'xml']
        );
        if ($response->isOk()) {
            $results = Xml::toArray($response->xml);
            $this->out(pr($results['response']));
        } else {
            debug($response);
        }
        return $response;
    }

    /**
     * DNS delete all rows
     *
     * @param string $domain Domain name.
     * @return bool
     */
    public function rowDeleteAll($domain)
    {
        $response = $this->rowsList($domain);
        $records = Xml::toArray($response->xml);
        foreach ($records['response']['data']['row'] as $row) {
            $this->rowDelete($domain, $row['ID']);
        }
        return true;
    }
}
