<?php
namespace Lubos\Wedos\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Network\Http\Client;
use Cake\Utility\String;
use Cake\Utility\Xml;

class DomainShell extends Shell
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
        $parser->addSubcommand('check');
        $parser->addSubcommand('create');
        return $parser;
    }

    /**
     * Checking if domain is available
     *
     * @param string $domain Domain to check.
     * @return mixed
     */
    public function check($domain)
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
                'command' => 'domain-check',
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
     * Register domain
     *
     * @param string $domain Domain to be registered.
     * @param string $owner Owner contact ID.
     * @param string $admin Admin contact ID.
     * @param int $period Number of years.
     * @return mixed
     */
    public function create($domain, $owner, $admin, $period = 1)
    {
        $client = new Client();
        $contact = $this->contactInfo($owner);
        if (empty($contact['response']['data']['contact'])) {
            $this->out('Wrong contact data');
            return false;
        }
        $request = Xml::fromArray([
            'request' => [
                'user' => Configure::read('Wedos.user'),
                'auth' => sha1(implode([
                    Configure::read('Wedos.user'),
                    sha1(Configure::read('Wedos.password')),
                    date('H', time())
                ])),
                'command' => 'domain-create',
                'data' => [
                    'name' => $domain,
                    'period' => $period,
                    'dns' => ' ',
                    'owner_c' => $owner,
                    'admin_c' => $admin,
                    'rules' => [
                        'fname' => $contact['response']['data']['contact']['fname'],
                        'lname' => $contact['response']['data']['contact']['lname'],
                    ]
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
}
