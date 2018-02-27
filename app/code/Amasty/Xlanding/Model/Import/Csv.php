<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */

namespace Amasty\Xlanding\Model\Import;

class Csv
{
    protected $_resource;

    protected $_columns;

    const ERROR_CODE_DUPLICATE_ENTRY = 23000;

    protected $_importErrors = [];

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = []
    ){
        $this->_resource = $resource;
    }

    protected function _getColumns()
    {
        if ($this->_columns === null){

            $tableName = $this->_resource->getTableName('amasty_xlanding_page');
            $this->_columns = [];
            $schema = $this->_resource->getConnection()->describeTable($tableName);
            foreach ($schema as $column) {
                $this->_columns[$column['COLUMN_NAME']] = $column['COLUMN_NAME'];
            }
        }

        return $this->_columns;
    }

    protected function _getRow(array $header, array $csvLine)
    {
        $row = [];
        $columns = $this->_getColumns();
        foreach(array_combine($header, $csvLine) as $column => $value){
            if (array_key_exists($column, $columns)){
                $row[$column] = $value;
            }
        }
        return $row;
    }

    public function import(
        \Magento\Framework\Filesystem\File\WriteInterface $file,
        array $stores
    ){
        $header = null;

        $rowNumber  = 1;

        while (($csvLine = $file->readCsv()) !== FALSE) {
            if ($header === null){
                $header = $csvLine;
                continue;
            }

            $row = $this->_getRow($header, $csvLine);
            $this->_insertPage($row, $stores, $rowNumber);

            $rowNumber++;
        }

        if (count($this->_importErrors) > 0) {
            $error = __('Landing Pages has not been imported completely. See the following list of errors:<br /> %1', implode(" \n", $this->_importErrors));

            throw new \Magento\Framework\Exception\LocalizedException($error);
        }
    }

    protected function _insertPage(array $insertData, array $stores, $rowNumber)
    {
        $pageTableName = $this->_resource->getTableName('amasty_xlanding_page');
        $pageStoreTableName = $this->_resource->getTableName('amasty_xlanding_page_store');
        try{
            $this->_resource->getConnection()->insert($pageTableName, $insertData);

            foreach($stores as $storeId) {
                $this->_resource->getConnection()->insert($pageStoreTableName, [
                    'page_id' => $this->_resource->getConnection()->lastInsertId(),
                    'store_id' => $storeId
                ]);
            }
        }catch (\Zend_Db_Statement_Exception $e){
            if ($e->getCode() == self::ERROR_CODE_DUPLICATE_ENTRY) {
                $this->_importErrors[] = __('Record with "%1" identifier exists already. Delete it before update. Row #%2', $insertData['identifier'], $rowNumber);
                // In case if Sample data was already installed we just skip duplicated records installation
            } else {
                throw $e;
            }
        }
    }
}