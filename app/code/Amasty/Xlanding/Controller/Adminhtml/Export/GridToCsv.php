<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */


namespace Amasty\Xlanding\Controller\Adminhtml\Export;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Amasty\Xlanding\Model\Export\ConvertToCsv;
use Magento\Framework\App\Response\Http\FileFactory;

class GridToCsv extends Action
{
    protected $_converter;

    protected $_fileFactory;

    public function __construct(
        Context $context,
        ConvertToCsv $converter,
        FileFactory $fileFactory
    ) {
        parent::__construct($context);
        $this->_converter = $converter;
        $this->_fileFactory = $fileFactory;
    }

    public function execute()
    {
        return $this->_fileFactory->create('export.csv', $this->_converter->getCsvFile(), 'var');
    }
}
