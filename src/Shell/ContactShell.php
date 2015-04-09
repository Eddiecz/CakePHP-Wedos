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
        $parser->addSubcommand('check');
        $parser->addSubcommand('info');
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
}
