<?php

namespace IntechSoft\CustomImport\Model\UrlRewrite\Storage;

use Magento\Framework\App\ResourceConnection;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use Magento\Framework\Api\DataObjectHelper;

class DbStorage extends \Magento\UrlRewrite\Model\Storage\DbStorage
{
    /**
     * Insert multiple
     *
     * @param array $data
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Exception
     */
    protected function insertMultiple($data)
    {
        try {
            $this->connection->insertOnDuplicate($this->resource->getTableName(self::TABLE_NAME), $data, ['target_path']); //changed
        } catch (\Exception $e) {
            if ($e->getCode() === self::ERROR_CODE_DUPLICATE_ENTRY
                && preg_match('#SQLSTATE\[23000\]: [^:]+: 1062[^\d]#', $e->getMessage())
            ) {
                throw new \Magento\Framework\Exception\AlreadyExistsException(
                    __('URL key for specified store already exists.')
                );
            }
            throw $e;
        }
    }
}