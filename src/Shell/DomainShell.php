<?php
namespace Lubos\Wedos\Shell;

use Cake\Utility\Xml;
use Lubos\Wedos\Shell\ContactShell;

class DomainShell extends WedosShell
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
        $parser->addSubcommand('create');
        $parser->addSubcommand('info');
        $parser->addSubcommand('transfer');
        return $parser;
    }

    /**
     * Checking if domain is available
     *
     * @param string $domain Domain to check.
     * @return \Cake\Network\Http\Response
     */
    public function check($domain)
    {
        $this->request['request']['command'] = 'domain-check';
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
     * Register domain
     *
     * @param string $domain Domain to be registered.
     * @param string $owner Owner contact ID.
     * @param string $admin Admin contact ID.
     * @param int $period Number of years.
     * @return \Cake\Network\Http\Response
     */
    public function create($domain, $owner, $admin, $period = 1)
    {
        $contact = new ContactShell();
        $response = $contact->info($owner);
        $data = Xml::toArray($response->xml);
        if (empty($data['response']['data']['contact'])) {
            $this->out('Wrong contact data');
            return false;
        }
        $this->request['request']['command'] = 'domain-create';
        $this->request['request']['data'] = [
            'name' => $domain,
            'period' => $period,
            'dns' => ' ',
            'owner_c' => $owner,
            'admin_c' => $admin,
            'rules' => [
                'fname' => $data['response']['data']['contact']['fname'],
                'lname' => $data['response']['data']['contact']['lname'],
            ]
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
     * Domain info
     *
     * @param string $name Domain name to get info.
     * @return \Cake\Network\Http\Response
     */
    public function info($name)
    {
        $this->request['request']['command'] = 'domain-info';
        $this->request['request']['data'] = [
            'name' => $name,
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
     * Domain transfer
     *
     * @param string $name Domain name to transfer.
     * @param string $authInfo Domain auth code.
     * @param string $fname First name of owner.
     * @param string $lname Last name of owner.
     * @return \Cake\Network\Http\Response
     */
    public function transfer($name, $authInfo, $fname, $lname)
    {
        $this->request['request']['command'] = 'domain-transfer';
        $this->request['request']['data'] = [
            'name' => $name,
            'auth_info' => $authInfo,
            'rules' => [
                'fname' => $fname,
                'lname' => $lname,
            ]
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
