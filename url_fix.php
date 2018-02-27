
<?php
use Magento\Framework\App\Bootstrap;

require 'app/bootstrap.php';

$params = $_SERVER;
$bootstrap = Bootstrap::create(BP, $params);
$obj = $bootstrap->getObjectManager();
$state = $obj->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');
$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$conf = $objectManager->create('\Magento\Framework\App\Config\ScopeConfigInterface');
$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
$products = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection')->addFieldToFilter('product_type', ['eq' => "configurable"])->load();
$stop = 0;
$sum = count($products);
$i = 1;

    foreach ($products as $product) {

    $productId = $product->getId();
    $productForSave = '';
    $productForSave = $objectManager->get('\Magento\Catalog\Model\Product')->load($productId);
    $productUrl = $productForSave->getUrlKey();

        $productTypeInstance = $objectManager->get('Magento\ConfigurableProduct\Model\Product\Type\Configurable');
        if((bool)$productTypeInstance->getUsedProducts($product) === false){
            echo $productId . '_' . $productUrl . '||' . $i . ' of ' . $sum . PHP_EOL . ' hasnt options';
            $i++;
            continue;
        }
        echo $productId . '_' . $productUrl . '||' . $i . ' of ' . $sum . PHP_EOL;
    $productForSaveOne = '';
    $productForSaveOne = $productForSave;
    if ($productForSaveOne->getProductType() == 'configurable') {
        $productForSaveOne->setData('url_key', $productUrl . 'd');
        $productForSaveOne->save();
        $productForSaveTwo = '';
        $productForSaveTwo = $objectManager->get('\Magento\Catalog\Model\Product')->load($productId);
        $productForSaveTwo->setData('url_key', $productUrl);
        $productForSaveTwo->save();
    }
    $i++;
}




