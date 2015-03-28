<?php
namespace Lubos\Wedos\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Network\Http\Client;
use Cake\Utility\String;
use Cake\Utility\Xml;

class ContactShell extends Shell
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
        $parser->addSubcommand('contactInfo');
        return $parser;
    }

    /**
     * Getting NIC contact info
     *
     * @param string $cname Contact ID.
     * @param string $tld TLD.
     * @return mixed
     */
    public function info($cname, $tld = 'cz')
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
                'command' => 'contact-info',
                'data' => [
                    'tld' => $tld,
                    'cname' => $cname,
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
        return false;
    }
}
