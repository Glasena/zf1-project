<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initViewSetup(): Zend_View
    {
        $this->bootstrap('view');
        /** @var Zend_View $view */
        $view = $this->getResource('view');
        $view->setEncoding('UTF-8');
        $view->doctype('HTML5');
        $view->headMeta()->appendHttpEquiv('Content-Type', 'text/html; charset=UTF-8');

        return $view;
    }

    protected function _initDoctrine(): EntityManager
    {
        $options = $this->getOption('doctrine');
        $isDev   = APPLICATION_ENV === 'development';

        $metadataCache = $isDev
            ? new ArrayAdapter()
            : new FilesystemAdapter('doctrine_meta', 0, APPLICATION_PATH . '/../data/doctrine/cache');

        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: [APPLICATION_PATH . '/entities'],
            isDevMode: $isDev,
            proxyDir: $options['proxies_dir'],
            cache: $metadataCache,
        );

        $config->setProxyNamespace($options['proxies_ns']);
        $config->setAutoGenerateProxyClasses((bool) $options['auto_generate_proxies']);

        $connection = DriverManager::getConnection([
            'driver'   => $options['conn']['driver'],
            'host'     => $options['conn']['host'],
            'port'     => (int) $options['conn']['port'],
            'user'     => $options['conn']['user'],
            'password' => $options['conn']['password'],
            'dbname'   => $options['conn']['dbname'],
            'charset'  => $options['conn']['charset'],
        ], $config);

        $em = new EntityManager($connection, $config);

        Zend_Registry::set('doctrine.em', $em);

        return $em;
    }
}
