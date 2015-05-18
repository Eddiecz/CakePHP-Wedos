<?php
namespace Lubos\Wedos\Shell;

use Cake\Utility\Xml;
use Lubos\Wedos\Shell\WedosShell;

class ContactShell extends WedosShell
{

    /**
     * Gets the option parser instance and configures it.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->addSubcommand('check', [
            'help' => 'NIC contact check',
            'parser' => [
                'description' => 'To check if passed contact id for tld is available',
                'arguments' => [
                    'cname' => [
                        'help' => 'Contact ID.',
                        'required' => true
                    ],
                    'tld' => [
                        'help' => 'TLD'
                    ]
                ]
            ]
        ]);
        $parser->addSubcommand('info', [
            'help' => 'NIC contact info',
            'parser' => [
                'description' => 'Info about passed contact ID',
                'arguments' => [
                    'cname' => [
                        'help' => 'Contact ID.',
                        'required' => true
                    ],
                    'tld' => [
                        'help' => 'TLD'
                    ]
                ]
            ]
        ]);
        $parser->addSubcommand('create', [
            'help' => 'NIC contact create',
            'parser' => [
                'description' => 'Create contact with passed info.',
                'arguments' => [
                    'tld' => [
                        'help' => 'TLD',
                        'required' => true
                    ],
                    'fname' => [
                        'required' => true
                    ],
                    'lname' => [
                        'required' => true
                    ],
                    'addr_street' => [
                        'required' => true
                    ],
                    'addr_city' => [
                        'required' => true
                    ],
                    'addr_zip' => [
                        'required' => true
                    ],
                    'addr_country' => [
                        'required' => true,
                        'help' => '2 digits ISO value'
                    ],
                    'phone' => [
                        'required' => true
                    ],
                    'email' => [
                        'required' => true
                    ],
                ],
                'options' => [
                    'cname' => [
                        'help' => 'Contact ID.',
                    ],
                    'company' => [
                    ],
                    'addr_state' => [
                    ],
                    'ic' => [
                    ],
                    'dic' => [
                    ],
                ]
            ]
        ]);
        return $parser;
    }

    /**
     * Getting NIC contact check
     *
     * @param string $cname Contact ID.
     * @param string $tld TLD.
     * @return \Cake\Network\Http\Response
     */
    public function check($cname, $tld = 'cz')
    {
        $this->request['request']['command'] = 'contact-check';
        $this->request['request']['data'] = [
            'tld' => $tld,
            'cname' => $cname,
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
     * Getting NIC contact info
     *
     * @param string $cname Contact ID.
     * @param string $tld TLD.
     * @return \Cake\Network\Http\Response
     */
    public function info($cname, $tld = 'cz')
    {
        $this->request['request']['command'] = 'contact-info';
        $this->request['request']['data'] = [
            'tld' => $tld,
            'cname' => $cname,
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
     * Create NIC contact
     *
     * @param string $tld TLD.
     * @param string $fname First name.
     * @param string $lname Last name.
     * @param string $addrStreet Address Street.
     * @param string $addrCity Address City.
     * @param string $addrZip Address Postcode.
     * @param string $addrCountry Address Country.
     * @param string $phone Phone.
     * @param string $email Email.
     * @return \Cake\Network\Http\Response
     */
    public function create($tld, $fname, $lname, $addrStreet, $addrCity, $addrZip, $addrCountry, $phone, $email)
    {
        // preparing data
        unset(
            $this->params['help'],
            $this->params['verbose'],
            $this->params['quiet']
        );
        $data = [
            'tld' => $tld,
            'contact' => array_merge(
                compact('fname', 'lname', 'phone', 'email'),
                ['addr_street' => $addrStreet, 'addr_city' => $addrCity, 'addr_zip' => $addrZip, 'addr_country' => $addrCountry],
                $this->params
            )
        ];
        // request
        $this->request['request']['command'] = 'contact-create';
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
}
