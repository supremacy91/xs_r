<?php
namespace IntechSoft\RegenProductUrl\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

class RegenerateProductUrlCommand extends Command
{
    /**
     * @var ProductUrlRewriteGenerator
     */
    protected $productUrlRewriteGenerator;

    /**
     * @var UrlPersistInterface
     */
    protected $urlPersist;

    protected $productStatus;

    /**
     * @var ProductRepositoryInterface
     */
    protected $collection;

    public function __construct(
        \Magento\Framework\App\State $state,
        Collection $collection,
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        UrlPersistInterface $urlPersist,
        StoreManagerInterface $storeManager,
        Status $productStatus
    ) {
        $state->setAreaCode('adminhtml');
        $this->collection = $collection;
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
        $this->storeManager = $storeManager;
        $this->productStatus = $productStatus;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('intechsoft:regenurl')
            ->setDescription('Regenerate url for given products')
            ->addArgument(
                'pids',
                InputArgument::IS_ARRAY,
                'Products to regenerate'
            )
            ->addOption(
                'store', 's',
                InputOption::VALUE_REQUIRED,
                'Use the specific Store View',
                Store::DEFAULT_STORE_ID
            )
        ;
        return parent::configure();
    }

    public function execute(InputInterface $inp, OutputInterface $out)
    {
        $pids = $inp->getArgument('pids');
        if(!empty($pids) && in_array('urlkey', [0 => $pid = array_shift($pids)])){

            $this->collection->addStoreFilter(0)->setStoreId(0);
            $this->collection->addAttributeToSelect(['url_path', 'url_key']);
            $this->collection->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()]);
            $list = $this->collection->load();
            $productDefault = [];
            foreach ($list as $prod){
                $productDefault[$prod->getId()] = $prod->getUrlKey();
            }

            $stores = $this->storeManager->getStores();

            foreach ($stores as $store) {
                $store_id = $store->getId();
                $this->collection->addStoreFilter($store_id)->setStoreId($store_id);

                if( !empty($pids) )
                    $this->collection->addIdFilter($pids);

                $this->collection->addAttributeToSelect(['url_path', 'url_key']);
                $this->collection->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()]);
                $list = $this->collection->load();

                if(count($list)) {
                    foreach ($list as $product) {
                        if($urlKey = $productDefault[$product->getId()]){
                            $product->setUrlKey($urlKey)->save();
                        }
                    }
                }
            }
            die('UrlKey Regenerate Done');
        }

        if ($this->storeManager->isSingleStoreMode()) {
            $stores = [$this->storeManager->getStore(0)];
        } else {
            $stores = $this->storeManager->getStores();
        }

        foreach ($stores as $store) {
            // $store_id = $inp->getOption('store');
            $store_id = $store->getId();
            $this->collection->addStoreFilter($store_id)->setStoreId($store_id);

            $pids = $inp->getArgument('pids');
            if( !empty($pids) )
                $this->collection->addIdFilter($pids);

            $this->collection->addAttributeToSelect(['url_path', 'url_key']);
            $this->collection->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()]);
            $list = $this->collection->load();
            if(count($list)) {
                foreach ($list as $product) {
                    $product->setStoreId($store_id);
                    $this->urlPersist->deleteByData([
                        UrlRewrite::ENTITY_ID => $product->getId(),
                        UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                        UrlRewrite::REDIRECT_TYPE => 0,
                        UrlRewrite::STORE_ID => $store_id
                    ]);
                    try {
                        $this->urlPersist->replace(
                            $this->productUrlRewriteGenerator->generate($product)
                        );
                    } catch (\Exception $e) {
                        $out->writeln('<error>Duplicated url for ' . $product->getId() . ' store id ' . $store_id . '</error>');
                    }
                }
            }
        }
    }
}