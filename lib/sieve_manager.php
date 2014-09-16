<?php


define('SIEVE_ERROR_CONNECTION', 1);
define('SIEVE_ERROR_LOGIN', 2);
define('SIEVE_ERROR_NOT_EXISTS', 3);    // script not exists
define('SIEVE_ERROR_INSTALL', 4);       // script installation
define('SIEVE_ERROR_ACTIVATE', 5);      // script activation
define('SIEVE_ERROR_DELETE', 6);        // script deletion
define('SIEVE_ERROR_INTERNAL', 7);      // internal error
define('SIEVE_ERROR_DEACTIVATE', 8);    // script activation
define('SIEVE_ERROR_OTHER', 255);       // other/unknown error


class SieveManager {

    /**
     * Object constructor
     *
     * @param string  Username (for managesieve login)
     * @param string  Password (for managesieve login)
     * @param string  Managesieve server hostname/address
     * @param string  Managesieve server port number
     * @param string  Managesieve authentication method 
     * @param boolean Enable/disable TLS use
     * @param array   Disabled extensions
     * @param boolean Enable/disable debugging
     * @param string  Proxy authentication identifier
     * @param string  Proxy authentication password
     */

    private $error = false;         // error flag
    private $list = array();        // scripts list
    private $rc;
    private $config;

    const PORT = 4190;

    function __construct($config) {

        $this->rc = rcmail::get_instance();

        // Get connection parameters
        $host = $this->rc->config->get('raw_sieve_host', 'localhost');
        $port = $this->rc->config->get('raw_sieve_port');
        $tls  = $this->rc->config->get('raw_sieve_usetls', false);

        $host = rcube_utils::parse_host($host);
        $host = rcube_utils::idn_to_ascii($host);

        // remove tls:// prefix, set TLS flag
        if (($host = preg_replace('|^tls://|i', '', $host, 1, $cnt)) && $cnt) {
            $tls = true;
        }

        if (empty($port)) {
            $port = getservbyname('sieve', 'tcp');
            if (empty($port)) {
                $port = self::PORT;
            }
        }

        $this->config = array(
            'user'      => $_SESSION['username'],
            'password'  => $this->rc->decrypt($_SESSION['password']),
            'host'      => $host,
            'port'      => $port,
            'usetls'    => $tls,
            'auth_type' => $config->get('raw_sieve_auth_type'),
            'disabled'  => $config->get('raw_sieve_disabled_extensions'),
            'debug'     => $config->get('raw_sieve_debug', false),
            'auth_cid'  => $config->get('raw_sieve_auth_cid'),
            'auth_pw'   => $config->get('raw_sieve_auth_pw'),
        );

        $this->sieve = new Net_Sieve();

        // if (!empty($this->config["auth_cid"])) {
        //     $authz    = $username;
        //     $username = $auth_cid;
        //     $password = $auth_pw;
        // }

        if (PEAR::isError($this->sieve->connect($host, $port, null, $usetls))) {
            return $this->_set_error(SIEVE_ERROR_CONNECTION);
        }

        if (
            PEAR::isError(
                $this->sieve->login(
                    $this->config["user"],
                    $this->config["password"],
                    $this->config["auth_type"] ? strtoupper($this->config["auth_type"]) : null,
                    null
                )
            )
        ) {
            return $this->_set_error(SIEVE_ERROR_LOGIN);
        }


        $this->active_script_name = $this->sieve->getActive();
        $this->active_script = $this->sieve->getScript($this->active_script_name);

    }

    public function __destruct() {
        $this->sieve->disconnect();
    }

    public function list_scripts() {
        return $this->sieve->listScripts();
    }

    public function getScript($scriptname) {
        return $this->sieve->getScript($scriptname);
    }

    public function error()
    {
        return $this->error ? $this->error : false;
    }

    public function install_script($scriptname, $script, $makeactive = false)
    {
        return $this->sieve->installScript($scriptname, $script, $makeactive);
    }

    public function remove_script($scriptname) {

        if($scriptname == $this->active_script_name) {
            $this->sieve->setActive('');
        }
        return $this->sieve->removeScript($scriptname);
    }

    public function activate_script($scriptname) {
        $this->sieve->setActive('');
        $this->sieve->setActive($scriptname);
    }

    public function deactivate_script() {
        $this->sieve->setActive('');
    }


}








































?>
