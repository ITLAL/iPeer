<?php
require_once('SystemWebDriver.php');
require_once('SystemWebDriverSession.php');
require_once('PHPWebDriver/WebDriver.php');
require_once('PHPWebDriver/WebDriverBy.php');
require_once('PHPWebDriver/WebDriverWait.php');
require_once('PHPWebDriver/WebDriverKeys.php');
require_once('PHPWebDriver/WebDriverSession.php');
require_once('PageFactory.php');

class SystemBaseTestCase extends CakeTestCase
{
    protected $web_driver;
    protected $session;
    protected $url;
    
    /**
     * helper function to get the testing server url
     **/
    public function getURL() {
        $timezone = ini_get('date.timezone') ? ini_get('date.timezone') : 'UTC';
        date_default_timezone_set($timezone); // set the default time zone
        if (!($server = getenv('SERVER_TEST'))) {
            $server = 'http://localhost:2000/';
        }
        $this->url = $server;
    }
    
    public function waitForLogoutLogin($username)
    {
        $this->session->open($this->url);
        $this->logoutLogin($username);
    }
    
    public function logoutLogin($username)
    {
        $this->session->elementWithWait(PHPWebDriver_WebDriverBy::LINK_TEXT, 'Logout')->click();
        $w = new PHPWebDriver_WebDriverWait($this->session);
        $session = $this->session;
        $w->until(
            function($session) {
                $title = $session->title();
                return ($title == 'iPeer - Guard');
            }
        );
        $login = PageFactory::initElements($this->session, 'Login');
        $home = $login->login($username, 'ipeeripeer');
    }
    
    public function startTest($method) {
        echo 'Starting method ' . $method . "\n";
    }

    public function endTest($method) {
        echo 'Ending method ' . $method . "\n";
    }
}