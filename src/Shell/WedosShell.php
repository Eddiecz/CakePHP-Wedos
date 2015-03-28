<?php
namespace Lubos\Wedos\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Network\Http\Client;

class WedosShell extends Shell
{

    /**
     * Initial settings on startup
     *
     * @return void
     */
    public function startup()
    {
        $data = Configure::read('Wedos');
        if (!isset($data['user']) || !isset($data['password'])) {
            $this->error('Please set up Wedos user and password');
        }
        $this->client = new Client();
        $this->request = [
            'request' => [
                'user' => $data['user'],
                'auth' => sha1(implode([
                    $data['user'],
                    sha1($data['password']),
                    date('H', time())
                ])),
            ]
        ];
        $this->url = 'https://api.wedos.com/wapi/xml';
    }

    /**
     * Main function Prints out the list of shells.
     *
     * @return void
     */
    public function main()
    {
        $this->out($this->OptionParser->help());
    }
}
