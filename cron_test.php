<?php
use Magento\Framework\App\Bootstrap;
require 'app/bootstrap.php';

$params = $_SERVER;
$bootstrap = Bootstrap::create(BP, $params);
$obj = $bootstrap->getObjectManager();
$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

$cronClass = $objectManager->create('IntechSoft\CustomImport\Cron\Import\Day');

$cronClass->execute();

