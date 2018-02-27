<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amasty\Xlanding\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Symfony\Component\Config\Definition\Exception\Exception;

class Page extends \Magento\Cms\Model\Page
{
    const FILE_PATH_UPLOAD = 'amasty/xlanding/page/';
    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'amasty_xlanding_page';

    /**
     * @var string
     */
    protected $_cacheTag = 'amasty_xlanding_page';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'amasty_xlanding_page';

    protected $_rule;

    protected $_filesystem;
    protected $_fileUploaderFactory;
    protected $_storeManager;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_storeManager = $storeManager;

        return parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    protected function _construct()
    {
        $this->_init('Amasty\Xlanding\Model\ResourceModel\Page');
    }

    public function getRule()
    {
        if (!$this->_rule)
        {
            $this->_rule = \Magento\Framework\App\ObjectManager::getInstance()
                ->create('Amasty\Xlanding\Model\Rule')->load($this->getId());
        }
        return $this->_rule;
    }

    public function applyAttributesFilter($select){
        $conditions = $this->getRule()->getConditions();

        if ($conditions instanceof \Amasty\Xlanding\Model\Rule\Condition\Combine){

            $conditions->collectValidatedAttributes($select);

            $condition = $conditions->collectConditionSql();

            if (!empty($condition)) {
                $select->where($condition);
            }
        }
    }

    public function beforeSave()
    {
        $value = $this->getLayoutFile();

        // if no image was set - nothing to do
        $hasFile = false;

        try{
            $uploader = $this->_fileUploaderFactory->create(['fileId' => 'layout_file']);
            $hasFile = true;

        } catch (\Exception $e) {
            if ($e->getCode() != \Magento\MediaStorage\Model\File\Uploader::TMP_NAME_EMPTY) {
                $this->_logger->critical($e);
            }
        }

        if (empty($value) && $hasFile === false) {
            return parent::beforeSave();
        }

        if (!empty($value['delete'])) {
            $this->setData('layout_file', '');

            return parent::beforeSave();
        }


        try {
           $path = $this->_filesystem->getDirectoryRead(
               DirectoryList::MEDIA
           )->getAbsolutePath(
               self::FILE_PATH_UPLOAD
           );

            /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */

            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $result = $uploader->save($path);

            $this->setData('layout_file', $result['file']);
        } catch (\Exception $e) {
            if ($e->getCode() != \Magento\MediaStorage\Model\File\Uploader::TMP_NAME_EMPTY) {
                $this->_logger->critical($e);
            }
        }

        $value = $this->getLayoutFile();

        if (is_array($value)){
            $this->setData('layout_file', $value['value']);
        }

        return parent::beforeSave();
    }

    public function getLayoutUpdateXml()
    {
        $xml = parent::getLayoutUpdateXml();

        $extra = [
            '<body><attribute name="class" value="amasty-xlanding-columns' . $this->getLayoutColumnsCount() . '"/></body>',

        ];

        if (!$this->getLayoutIncludeNavigation()){
            $extra[] = '<body><referenceContainer name="sidebar.main"><referenceBlock  name="catalog.leftnav" remove="true"></referenceBlock></referenceContainer></body>';
        }

        return implode(',', $extra) . $xml;
    }
}
