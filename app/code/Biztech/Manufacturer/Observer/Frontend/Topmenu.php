<?php

namespace Biztech\Manufacturer\Observer\Frontend;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Event\ObserverInterface;

class Topmenu implements ObserverInterface {
	protected $_helper;
	protected $_block;
	protected $_request;
	public function __construct(
		\Biztech\Manufacturer\Block\Manufacturer\Index $Blockindex,
		\Magento\Framework\App\Request\Http $request,
		\Biztech\Manufacturer\Helper\Data $data
	){
		$this->_helper = $data;
		$this->_block = $Blockindex;
		$this->_request = $request;
	}
	public function execute(EventObserver $observer)
	{

		if( $this->_helper->isEnabled() && $this->_helper->getConfigValue('manufacturer/general/manufacturer_display_top_menu')  ) :
			/** @var \Magento\Framework\Data\Tree\Node $menu */
		$menu = $observer->getMenu();
		$tree = $menu->getTree();
		$data = [
		'name'      => __('Manufacturer'),
		'id'        => 'manufacturer-main',
		'url'       => $this->_block->getUrl('merken'),
		'is_last'  => 99,
		'is_active' => ($this->_request->getOriginalPathInfo() == '/merken/' || $this->_request->getRouteName() == 'merken' || $this->_request->getOriginalPathInfo() == '/merken' ) ? true : '',
		];

		$node = new Node($data, 'id', $tree, $menu);
		$menu->addChild($node);

		$_manufacturerCollection = $this->_helper->getManufacturerCollection();

		foreach ($_manufacturerCollection as $_manufacturer) {
			$subTree = $node->getTree();
			$data = [
			'name' => __($_manufacturer->getBrandName()),
			'id' => 'manufacturer-name-'.$_manufacturer->getId(),
			'url' => $this->_block->getManufacturerUrl($_manufacturer),
			'is_active' => ($this->_request->getOriginalPathInfo() == '/' . strtolower($_manufacturer->getUrlKey()) . '/' || $this->_request->getOriginalPathInfo() == '/'. strtolower($_manufacturer->getUrlKey())) ? true : ''

			];
			$subNode = new Node($data, 'id', $subTree, $node);
			$node->addChild($subNode);
		}
		endif;
		return $this;
	}

}