<?php

namespace Biztech\Manufacturer\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {

        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'manufacturer_grid'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('manufacturer')
        )
            ->addColumn(
                'manufacturer_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'manufacturer_grid'
            )
            ->addColumn(
                'manufacturer_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', [], 'manufacturer id'
            )
            ->addColumn(
                'brand_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', [], 'brandname'
            )
            ->addColumn(
                'filename', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', [], 'filename'
            )
            ->addColumn(
                'url_key', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', [], 'url_key'
            )
            /* {{CedAddTableColumn}}} */
            ->setComment(
                'Biztech Manufacturer manufacturer_grid'
            );

        $installer->getConnection()->createTable($table);
        /* {{CedAddTable}} */

        $table1 = $installer->getConnection()->newTable(
            $installer->getTable('manufacturer_text')
        )
            ->addColumn('text_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'manufacturer_text_id')
            ->addColumn('manufacturer_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => false], 'manufacturer_id')
            ->addColumn(
                'store_id', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, ['unsigned' => true, 'nullable' => false, 'default' => '0'], 'Store ID'
            )
            ->addColumn(
                'status', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, ['unsigned' => true, 'nullable' => false, 'default' => '0'], 'enabled status'
            )
            ->addColumn(
                'show_in_sidebar', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, ['unsigned' => true, 'nullable' => false, 'default' => '0'], 'show in sidebar'
            )
            ->addColumn(
                'description', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', [], 'description of manufacturer'
            )
            ->addColumn(
                'short_description', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', [], 'short description of manufacturer'
            )
            ->addColumn(
                'meta_title', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', [], 'meta title of manufacturer'
            )
            ->addColumn(
                'meta_keyword', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', [], 'meta keyword of manufacturer'
            )
            ->addColumn(
                'meta_description', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', [], 'meta_description of manufacturer'
            )
            ->addColumn(
                'url_key', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', [], 'url_key manufacturer'
            )
            ->addColumn(
                'position', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, ['default' => 0, 'unsigned' => true], 'position number'
            )
            ->addColumn(
                'is_featured', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, ['default' => 0, 'unsigned' => true], 'is featured'
            )
            ->addIndex(
                $installer->getIdxName(
                    $installer->getTable('manufacturer_text'), ['manufacturer_id'], \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ), ['manufacturer_id'], ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )
            ->addForeignKey(
                $installer->getFkName('manufacturer_text', 'manufacturer_id', 'manufacturer', 'manufacturer_id'), 'manufacturer_id', $installer->getTable('manufacturer'), 'manufacturer_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment(
                'Biztech Manufacturer manufacturer_text');
        $installer->getConnection()->createTable($table1);

        $installer->endSetup();
    }

}
