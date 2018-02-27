<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\Xlanding\Setup;


use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Amasty\Xlanding\Model\ResourceModel\Page\CollectionFactory;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var CollectionFactory
     */
    private $pageCollectionFactory;

    public function __construct(
        CollectionFactory $pageCollectionFactory
    ) {
        $this->pageCollectionFactory = $pageCollectionFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.5', '<')) {
            $this->updateUrlRewrites();
        }

        $setup->endSetup();
    }

    /**
     * Trigger excess url rewrite removal
     */
    private function updateUrlRewrites()
    {
        $this->pageCollectionFactory->create()->save();
    }

}
