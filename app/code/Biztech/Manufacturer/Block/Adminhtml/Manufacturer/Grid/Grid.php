<?php
namespace Biztech\Manufacturer\Block\Adminhtml\Manufacturer\Grid;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Store\Model\WebsiteFactory;
use Biztech\Manufacturer\Model\ResourceModel\Manufacturer\Collection;
use Magento\Framework\Module\Manager;
use Biztech\Manufacturer\Model\Status;

class Grid extends Extended
{

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory]
     */
    protected $_setsFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_type;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_status;
    protected $_collectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_visibility;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;
    protected $_manufacturerStatus;

    public function __construct(
        Context $context,
        Data $backendHelper,
        WebsiteFactory $websiteFactory,
        Collection $collectionFactory,
        Manager $moduleManager,
        Status $status,
        array $data = []
    )
    {

        $this->_collectionFactory = $collectionFactory;
        $this->_websiteFactory = $websiteFactory;
        $this->moduleManager = $moduleManager;
        $this->_manufacturerStatus = $status;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('manufacturerGrid');
        $this->setDefaultSort('manufacturer_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
    }

    /**
     * @return Store
     */
    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        try {
            $store = $this->getRequest()->getParam('store', 0);
            $prefix = $this->getTablePrefix();
            $collection = $this->_collectionFactory->load();
            $collection->getSelect()->joinLeft($prefix . 'manufacturer_text', 'main_table.manufacturer_id =' . $prefix . 'manufacturer_text.manufacturer_id AND ' . $prefix . 'manufacturer_text.store_id = ' . $store, ['status', 'description', 'short_description', 'position', 'store_id']);
            $this->setCollection($collection);
            parent::_prepareCollection();
            return $this;
        } catch (Exception $e) {
            echo $e->getMessage();
            die;
        }
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField(
                    'websites', 'catalog_product_website', 'website_id', 'product_id=entity_id', null, 'left'
                );
            }
        }
        return parent::_addColumnFilterToCollection($column);
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'manufacturer_id', [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'manufacturer_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'width' => '100px'
            ]
        );
        $this->addColumn(
            'filename', [
                'header' => __('Thumbnail'),
                'renderer' => 'Biztech\Manufacturer\Block\Adminhtml\Grid\Renderer\Image',
                'filter' => false,
                'type' => 'image',
                'index' => 'filename',
                'class' => 'filename'
            ]
        );
        $this->addColumn(
            'brand_name', [
                'header' => __('Manufacturer Name'),
                'index' => 'brand_name',
                'class' => 'brand_name'
            ]
        );
        $this->addColumn(
            'status', [
                'header' => __('Status'),
                'align' => 'left',
                'width' => '80px',
                'type' => 'options',
                'options' => [
                    1 => 'Enabled',
                    2 => 'Disabled'
                ],
                'index' => 'status',
                'class' => 'status']
        );


        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('manufacturer_id');
        $this->getMassactionBlock()->setFormFieldName('manufacturer');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('manufacturer/*/massDelete'),
                'confirm' => __('Are you sure you want to Delete Selected Brand(s) ?')
            ]
        );

        $status = $this->_manufacturerStatus->toOptionArray();

        $this->getMassactionBlock()->addItem(
            'status', [
            'label' => __('Change Status'),
            'url' => $this->getUrl('manufacturer/*/massStatus', ['_current' => true, 'store' => $this->getRequest()->getParam('store', 0)]),
            'additional' => [
                'visibility' => [
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => __('Status'),
                    'values' => $status
                ]
            ]
        ]);
        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('manufacturer/*/index', ['_current' => true]);
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'manufacturer/*/edit', ['store' => $this->getRequest()->getParam('store'), 'manufacturer_id' => $row->getManufacturerId()]
        );
    }

}
