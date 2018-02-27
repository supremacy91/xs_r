<?php
namespace Biztech\Manufacturer\Block\Manufacturer\Layer;

use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\CategoryFactory as CategoryModelFactory;
use Magento\Catalog\Model\Layer;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Framework\Escaper;

class Attribute extends \Magento\Catalog\Model\Layer\Filter\AbstractFilter
{
    /**
     * Active Category Id
     *
     * @var int
     */
    protected $_categoryId;

    /**
     * Applied Category
     *
     * @var \Magento\Catalog\Model\Category
     */
    protected $_appliedCategory;

    /**
     * Core data
     *
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var CategoryDataProvider
     */
    private $dataProvider;

    /**
     * Attribute constructor.
     * @param ItemFactory $filterItemFactory
     * @param StoreManagerInterface $storeManager
     * @param Layer $layer
     * @param DataBuilder $itemDataBuilder
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        DataBuilder $itemDataBuilder,
        Escaper $escaper,
        $data = []
    )
    {
        parent::__construct($filterItemFactory, $storeManager, $layer, $itemDataBuilder, $data);
        $this->_escaper = $escaper;
        $this->_requestVar = 'manufacturer';
    }


    /**
     * Get filter value for reset current filter state
     *
     * @return mixed|null
     */
    public function getResetValue()
    {
        return $this->dataProvider->getResetValue();
    }

    /**
     * Apply category filter to layer
     *
     * @param   \Magento\Framework\App\RequestInterface $request
     * @return  $this
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        /**
         * Filter must be string: $fromPrice-$toPrice
         */
        // $filter = $request->getParam($this->getRequestVar());
        // if (!$filter) {
        //     return $this;
        // }
        // $filter = explode('-', $filter);
        // list($from, $to) = $filter;
        // $collection = $this->getLayer()->getProductCollection();
        // $collection->getSelect()->joinLeft(['rova' => 'rating_option_vote_aggregated'], 'e.entity_id =rova.entity_pk_value', ["percent"])
        //     ->where("rova.percent between " . $from . " and " . $to)
        //     ->group('e.entity_id');
        return $this;
    }

    /**
     * Get filter name
     *
     * @return \Magento\Framework\Phrase
     */
    public function getName()
    {
        return __('Manufacturer');
    }


    /**
     * Get data array for building attribute filter items
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return array
     */
    protected function _getItemsData()
    {

        /*$facets = [
            '0-20' => '1 Start',
            '21-40' => '2 Start',
            '41-60' => '3 Start',
            '61-80' => '4 Start',
            '81-100' => '5 Start'
        ];
        $collection = $this->getLayer()->getProductCollection();
        if (count($facets) > 1)
        {
            $i = 1;
            foreach ($facets as $key => $label)
            {
                $count = $this->prepareData($key, $collection, $i);
                $i++;
                $this->itemDataBuilder->addItemData(
                    $this->_escaper->escapeHtml($label),
                    $key,
                    $count
                );
            }
        }*/

        // return $this->itemDataBuilder->build();
        return [];
    }

    /**
     * @param $filter
     * @param $collection
     * @param $i
     * @return mixed
     */
    private function prepareData($filter, $collection, $i)
    {
        /*$filter = explode('-', $filter);
        list($from, $to) = $filter;
        $collection->getSelect()->joinLeft(['rova' . $i => 'rating_option_vote_aggregated'], 'e.entity_id =rova' . $i . '.entity_pk_value', ["percent"])
            ->where("rova" . $i . ".percent between " . $from . " and " . $to)
            ->group('e.entity_id');*/
        // return $collection->getSize();
        return [];
    }
}