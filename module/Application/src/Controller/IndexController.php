<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;


use Application\Model\IndexTable;
use Application\Model\Index;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Config\Config;
use Zend\Log\Logger;
use Zend\Log\Writer;

use RingCentral\SDK\SDK;
use RingCentral\SDK\Http\ApiException;

class IndexController extends AbstractActionController
{

    private $config;
    private $logger;
    private $table;

    public function __construct(IndexTable $table){
      $this->table = $table;
      $this->config = new Config(include __DIR__ . '/../../config/config.php');
      $this->logger = new Logger;
      $writer = new Writer\Stream(__DIR__ . '/../../realCentralIntegration.log');
      $this->logger->addWriter($writer);

    }

    public function indexAction()
    {
      return new ViewModel([
          'creds' => $this->table->fetchAll()

      ]);
        // return new ViewModel();
    }

    public function loginAction()
    {

      try {

        session_unset();
        session_start();

        $env = $this->params()->fromQuery('env');
        $clientId = $this->params()->fromQuery('clientId');
        $clientSecret = $this->params()->fromQuery('clientSecret');

        if (!empty($env) && !empty($clientId) && !empty($clientSecret)) {

          if ($env == 'sandbox') {
              $_SESSION['env'] = 'sandbox';
              $this->config = new Config(include __DIR__ . '/../../config/config-sandbox.php');
          }elseif ($env == 'production') {
              $_SESSION['env'] = 'production';
              $this->config = new Config(include __DIR__ . '/../../config/config.php');
          }

          $_SESSION['clientId'] = $this->params()->fromQuery('clientId');
          $_SESSION['clientSecret'] = $this->params()->fromQuery('clientSecret');

          $rcsdk = new SDK(
                            $_SESSION['clientId'],
                            $_SESSION['clientSecret'],
                            $this->config->server
                          );

          $platform = $rcsdk->platform();

          // Using the authUrl to call the platform function
          $url = $platform
              ->authUrl(array(
                  'redirectUri' => isset($this->config->redirectUri) ? $this->config->redirectUri : '',
                  'state' => 'myState',
                  'brandId' => '',
                  'display' => '',
                  'prompt' => ''
              ));
          $this->logger->info('Url : '. $url);
          $this->logger->info('Request authorization code.');
          return new ViewModel([
              'url' => $url
          ]);
          // return $this->redirect()->toUrl($url);
        } else {
          throw new \Exception('Required information missing');
        }

      } catch (\Exception $e) {
        $this->logger->info($e->getMessage());
        return new ViewModel([
            'message' => $e->getMessage()
          ]);
      }


    }

    public function codeAction()
    {

        try {
          session_start();

          if (isset($_SESSION['env'])){
            if ($_SESSION['env'] = 'sandbox') {
                $this->config = new Config(include __DIR__ . '/../../config/config-sandbox.php');
            }elseif ($_SESSION['env'] = 'production') {
                $this->config = new Config(include __DIR__ . '/../../config/config.php');
            }
          }else{
            $url =  $this->url()->fromRoute('home');
            return $this->redirect()->toUrl($url);
          }

          $rcsdk = new SDK(
                            $_SESSION['clientId'],
                            $_SESSION['clientSecret'],
                            $this->config->server
                          );
          $platform = $rcsdk->platform();
          $params = $platform->parseAuthRedirectUrl($_SERVER['QUERY_STRING']);

          $this->logger->info('Handling authorization code server response.');

          if (!isset($params['code'])) {
            parse_str($_SERVER['QUERY_STRING'],$params);
            if(!isset($params['error'])){

              $this->logger->info('Something went wrong while handling response.');
              return new ViewModel([
                  'error' => 'Something went wrong while handling response.'
              ]);

            }

            $this->logger->info('Error: '.$params['error']);
            $this->logger->info('Error Description: '.$params['error_description']);
            return new ViewModel([
                'error' => $params['error'],
                'error_description' =>  $params['error_description']
            ]);

          }
          $params["redirectUri"] = $this->config->redirectUri;

          $this->logger->info('Exchanging code for access token.');

          $apiResponse = $platform->login($params);

          $this->logger->info('Handling token server response.');

          $_SESSION['tokens'] = $platform->auth()->data();
          $index = new Index();
          $index->exchangeArray($_SESSION['tokens']);
          $this->table->saveIndex($index);

          return new ViewModel([
              'message' => 'Login successfull !',
              'data' => $_SESSION['tokens']
          ]);
          // return new JsonModel([
          //   'message' => 'Login successfull !',
          //   ]);

        } catch (ApiException $e) {
          $this->logger->info('Expected HTTP Error: ' . $e->getMessage());
          return new ViewModel([
              'message' => 'Expected HTTP Error: ' . $e->getMessage(),
          ]);
        }
    }
}
