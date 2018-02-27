<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://landofcoder.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_Formbuilder
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\Formbuilder\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $setup->getConnection()->dropTable($setup->getTable('lof_formbuilder_form'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('lof_formbuilder_form')
        )->addColumn(
            'form_id',
            Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Form ID'
        )
            ->addColumn(
                'title',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Title'
            )
            ->addColumn(
                'identifier',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Identifier'
            )
            ->addColumn(
                'email_receive',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Email Receive'
            )
            ->addColumn(
                'thanks_email_template',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Thanks Email Template'
            )
            ->addColumn(
                'email_template',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Email Template'
            )
            ->addColumn(
                'show_captcha',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Show Captcha'
            )
            ->addColumn(
                'show_toplink',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Show Top Link'
            )
            ->addColumn(
                'submit_button_text',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Submit Button Text'
            )
            ->addColumn(
                'success_message',
                Table::TYPE_TEXT,
                '64k',
                ['nullable' => false],
                'Success Message'
            )
            ->addColumn(
                'creation_time',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Form Creation Time'
            )
            ->addColumn(
                'update_time',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Form Modification Time'
            )
            ->addColumn(
                'before_form_content',
                Table::TYPE_TEXT,
                '64k',
                ['nullable' => false],
                'Before Form Content'
            )
            ->addColumn(
                'after_form_content',
                Table::TYPE_TEXT,
                '64k',
                ['nullable' => false],
                'After Form Content'
            )
            ->addColumn(
                'status',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Status'
            )
            ->addColumn(
                'design',
                Table::TYPE_TEXT,
                '64k',
                ['nullable' => false],
                'Design'
            )
            ->addColumn(
                'page_title',
                Table::TYPE_TEXT,
                '64k',
                ['nullable' => false],
                'Page Title'
            )
            ->addColumn(
                'redirect_link',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Redirect Link'
            )
            ->addColumn(
                'page_layout',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Page Layout'
            )
            ->addColumn(
                'page_title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Meta Title'
            )
            ->addColumn(
                'layout_update_xml',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                'Layout Update XML'
            )
            ->addColumn(
                'meta_keywords',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                'Meta Keywords'
            )
            ->addColumn(
                'meta_description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                'Meta Description'
            )
            ->addIndex(
                $setup->getIdxName('lof_formbuilder_form', ['form_id']),
                ['form_id']
            );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'lof_formbuilder_form_customergroup'
         */
        $setup->getConnection()->dropTable($setup->getTable('lof_formbuilder_form_customergroup'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('lof_formbuilder_form_customergroup')
        )->addColumn(
            'form_id',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'primary' => true],
            'Form ID'
        )->addColumn(
            'customer_group_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Customer Group ID'
        )->addIndex(
            $installer->getIdxName('lof_formbuilder_form_customergroup', ['customer_group_id']),
            ['customer_group_id']
        )->addForeignKey(
            $installer->getFkName('lof_formbuilder_form_customergroup', 'form_id', 'lof_formbuilder_form', 'form_id'),
            'form_id',
            $installer->getTable('lof_formbuilder_form'),
            'form_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('lof_formbuilder_form_customergroup', 'customer_group_id', 'customer_group', 'customer_group_id'),
            'customer_group_id',
            $installer->getTable('customer_group'),
            'customer_group_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Form Custom Group'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'lof_formbuilder_form_store'
         */
        $setup->getConnection()->dropTable($setup->getTable('lof_formbuilder_form_store'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('lof_formbuilder_form_store')
        )->addColumn(
            'form_id',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'primary' => true],
            'Form ID'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Store ID'
        )->addIndex(
            $installer->getIdxName('lof_formbuilder_form_store', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('lof_formbuilder_form_store', 'form_id', 'lof_formbuilder_form', 'form_id'),
            'form_id',
            $installer->getTable('lof_formbuilder_form'),
            'form_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('lof_formbuilder_form_store', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Form Store'
        );
        $installer->getConnection()->createTable($table);


        $setup->getConnection()->dropTable($setup->getTable('lof_formbuilder_message'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('lof_formbuilder_message')
        )->addColumn(
            'message_id',
            Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Message ID'
        )->addColumn(
            'form_id',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'primary' => true],
            'Form ID'
        )
            ->addColumn(
                'product_id',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'primary' => true],
                'Product ID'
            )
            ->addColumn(
                'customer_id',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'primary' => true],
                'Customer Id'
            )
            ->addColumn(
                'subject',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Subject'
            )
            ->addColumn(
                'email_from',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Email From'
            )
            ->addColumn(
                'creation_time',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Message Creation Time'
            )
            ->addColumn(
                'message',
                Table::TYPE_TEXT,
                '64k',
                ['nullable' => false],
                'Message'
            )
            ->addColumn(
                'ip_address',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'IP Adrress'
            )
            ->addColumn(
                'params',
                Table::TYPE_TEXT,
                '64k',
                ['nullable' => false],
                'Params'
            );
        $installer->getConnection()->createTable($table);


        $setup->getConnection()->dropTable($setup->getTable('lof_formbuilder_model_category'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('lof_formbuilder_model_category')
        )->addColumn(
            'category_id',
            Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Category ID'
        )->addColumn(
            'parent_id',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Parent ID'
        )
            ->addColumn(
                'title',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Subject'
            )
            ->addColumn(
                'status',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Status'
            )
            ->addColumn(
                'position',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Position'
            );
        $installer->getConnection()->createTable($table);


        $setup->getConnection()->dropTable($setup->getTable('lof_formbuilder_model'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('lof_formbuilder_model')
        )
            ->addColumn(
                'model_id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Model ID'
            )
            ->addColumn(
                'parent_id',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Parent ID'
            )
            ->addColumn(
                'category_id',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'primary' => true],
                'Category ID'
            )
            ->addColumn(
                'title',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Subject'
            )
            ->addColumn(
                'status',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Status'
            )
            ->addColumn(
                'creation_time',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Form Creation Time'
            )
            ->addColumn(
                'update_time',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Form Modification Time'
            )
            ->addColumn(
                'position',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Position'
            );
        $installer->getConnection()->createTable($table);


        $installer->endSetup();

    }
}