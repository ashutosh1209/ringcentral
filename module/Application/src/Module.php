<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements ConfigProviderInterface
{
    const VERSION = '3.0.3-dev';

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getServiceConfig() {
      return [
         'factories' => [
            Model\IndexTable::class => function ($container) {
               $tableGateway = $container->get( Model\IndexTableGateway::class);
               $table = new Model\IndexTable($tableGateway);
               return $table;
            },
            Model\IndexTableGateway::class => function ($container) {
               $dbAdapter = $container->get(AdapterInterface::class);
               $resultSetPrototype = new ResultSet();
               $resultSetPrototype->setArrayObjectPrototype(new Model\Index());
               return new TableGateway('user_credentials', $dbAdapter, null, $resultSetPrototype);
            },
         ],
      ];
   }
}
