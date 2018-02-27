<?php
namespace IntechSoft\CustomImport\Plugin;

use Magento\Framework\Setup\ModuleDataSetupInterface;

class InsertOptionData{

    private $setup;
    public function __construct(
        ModuleDataSetupInterface $setup
    ) {
        $this->setup = $setup;
       
    }

    public function aroundAddAttributeOption(\Magento\Eav\Setup\EavSetup $subject, $proceed, $option){
        $proceed = function($option) {
            $optionTable = $this->setup->getTable('eav_attribute_option');
            $optionValueTable = $this->setup->getTable('eav_attribute_option_value');

            if (isset($option['value'])) {
                foreach ($option['value'] as $optionId => $values) {
                    //$intOptionId = (int)$optionId;
                    $intOptionId = is_numeric($optionId) ? (int)$optionId : 0;
                    if (!empty($option['delete'][$optionId])) {
                        if ($intOptionId) {
                            $condition = ['option_id =?' => $intOptionId];
                            $this->setup->getConnection()->delete($optionTable, $condition);
                        }
                        continue;
                    }

                    if (!$intOptionId) {
                        $data = [
                            'attribute_id' => $option['attribute_id'],
                            'sort_order' => isset($option['order'][$optionId]) ? $option['order'][$optionId] : 0,
                        ];
                        $this->setup->getConnection()->insert($optionTable, $data);
                        $intOptionId = $this->setup->getConnection()->lastInsertId($optionTable);
                    } else {
                        $data = [
                            'sort_order' => isset($option['order'][$optionId]) ? $option['order'][$optionId] : 0,
                        ];
                        $this->setup->getConnection()->update($optionTable, $data, ['option_id=?' => $intOptionId]);
                    }

                    // Default value
                    if (!isset($values[0])) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('Default option value is not defined')
                        );
                    }
                    $condition = ['option_id =?' => $intOptionId];
                    $this->setup->getConnection()->delete($optionValueTable, $condition);
                    foreach ($values as $storeId => $value) {
                        $data = ['option_id' => $intOptionId, 'store_id' => $storeId, 'value' => $value];
                        $this->setup->getConnection()->insert($optionValueTable, $data);
                    }
                }
            } elseif (isset($option['values'])) {
                foreach ($option['values'] as $sortOrder => $label) {
                    // add option
                    $data = ['attribute_id' => $option['attribute_id'], 'sort_order' => $sortOrder];
                    $this->setup->getConnection()->insert($optionTable, $data);
                    $intOptionId = $this->setup->getConnection()->lastInsertId($optionTable);

                    $data = ['option_id' => $intOptionId, 'store_id' => 0, 'value' => $label];
                    $this->setup->getConnection()->insert($optionValueTable, $data);
                }
            }
        };
        return $proceed($option);
    }


}
