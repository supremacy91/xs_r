<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */

namespace Amasty\Xlanding\Model\Source;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Import extends \Magento\Config\Model\Config\Backend\File
{
    protected $_csvImport;
    protected $_request;

    const STORE_PATH = 'amasty_xlanding/import/store';

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface $requestData,
        \Amasty\Xlanding\Model\Import\Csv $csvImport,
        \Magento\Framework\App\Request\Http $request,
        Filesystem $filesystem,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ){
        $this->_csvImport = $csvImport;
        $this->_request = $request;

        return parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $uploaderFactory,
            $requestData,
            $filesystem,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function save()
    {
        $this->beforeSave();

        $fieldConfig = $this->getFieldConfig();

        if ($this->getValue() && array_key_exists('config', $fieldConfig['upload_dir'])) {

            $stores = $this->_request->getParam('groups');
            $stores = $stores['import']['fields']['store']['value'];

            if (!is_array($stores) || count($stores) < 1){
                throw new \Magento\Framework\Exception\LocalizedException(__('Stores should be selected.'));
            }

            $file = $this->_mediaDirectory->openFile($fieldConfig['upload_dir']['value'] . '/' . $this->getValue(), 'r');
            $this->_csvImport->import($file, $stores);
        }
    }

    protected function _getAllowedExtensions()
    {
        return ['csv', 'txt'];
    }
}