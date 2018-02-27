<?php

namespace IntechSoft\CustomImport\Model\Attribute;

use \Magento\Eav\Api;

/**
 * Class DbStorage
 * @package IntechSoft\CustomImport\Model\Attribute\Uninstall
 */
class Uninstall
{

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * Uninstall constructor.
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepositoryInterface
     */
    public function __construct(
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepositoryInterface
    )
    {
        $this->attributeRepository = $attributeRepositoryInterface;
    }

    /**
     * Insert multiple
     *
     * @param array $data
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     *
     * @throws \Exception
     */
    public function uninstallAttribute($attributeId = false)
    {
        if ($attributeId){
            $this->attributeRepository->deleteById($attributeId);
        }
    }
}