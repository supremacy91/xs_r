<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */
namespace Amasty\Xlanding\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;


class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.5', '<')) {
            $this->removeUrlKeyUniqueness($setup);
        }

        $setup->endSetup();
    }

    protected function removeUrlKeyUniqueness(SchemaSetupInterface $setup)
    {
        $installer = $setup;
        $connection = $installer->getConnection();
        $connection->dropIndex(
            $setup->getTable('amasty_xlanding_page'),
            $installer->getIdxName($installer->getTable('amasty_xlanding_page'), ['identifier'], \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
        );
    }
}
