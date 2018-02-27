<?php
namespace Parcelpro\Shipment\Block\Adminhtml\System\Config\Form\Field;

class CustomRowOne extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $_elementFactory;

    protected $_addAfter = true;

    protected $_addButtonLabel;

    protected $_customerGroupRenderer;

    protected $countryRenderer;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        array $data = []
    )
    {
        $this->_elementFactory = $elementFactory;
        parent::__construct($context, $data);
    }



    protected function getCountryRenderer()
    {
        if (!$this->countryRenderer) {
            $this->countryRenderer = $this->getLayout()->createBlock(
                'Parcelpro\Shipment\Block\Adminhtml\Form\Field\Countries',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->countryRenderer;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_addButtonLabel = __('Add');
    }

    protected function _prepareToRender()
    {
        $this->addColumn('titel', ['label' => __('Titel')]);
        $this->addColumn(
            'country',
            ['label' => __('Country'), 'renderer' => $this->getCountryRenderer()]
        );
        $this->addColumn('carrier', ['label' => __('Carrier')]);
        $this->addColumn('code', ['label' => __('Code')]);
        $this->addColumn('min_weight', ['label' => __('Min Weight')]);
        $this->addColumn('max_weight', ['label' => __('Max Weight')]);
        $this->addColumn('min_total', ['label' => __('Min Total')]);
        $this->addColumn('max_total', ['label' => __('Max Total')]);
        $this->addColumn('price', ['label' => __('Price')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param \Magento\Framework\DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $country = $row->getDataByKey('country');
        $options = [];
        if ($country) {
            $options['option_' . $this->getCountryRenderer()->calcOptionHash($country)]
                = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
        return;
    }
}
