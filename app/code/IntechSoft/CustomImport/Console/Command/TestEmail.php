<?php

namespace IntechSoft\CustomImport\Console\Command;


use Symfony\Component\Console\Command\Command; // for parent class
use Symfony\Component\Console\Input\InputInterface; // for InputInterface used in execute method
use Symfony\Component\Console\Output\OutputInterface; // for OutputInterface used in execute method
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use IntechSoft\CustomImport\Model\ImportFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Stdlib\DateTime\DateTime;
use IntechSoft\CustomImport\Helper\UrlRegenerate;
use Magento\Framework\Registry;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Area;
use Magento\Store\Model\Store;

class TestEmail extends Command
{
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder
    ) {
        $this->_transportBuilder    = $transportBuilder;
        $this->_scopeConfig         = $scopeConfig;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('test:email')
            ->setDescription('Testing email send');

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $templateId = $this->_scopeConfig->getValue("customImportSection/emailGroup/template_id", ScopeInterface::SCOPE_STORE);
        $senderDataId = $this->_scopeConfig->getValue("customImportSection/emailGroup/sender_data_id", ScopeInterface::SCOPE_STORE);

        $vars = array("failed_origin_name" => "failed_origin_name_VALUE", "failed_full_name" => "failed_full_name_VALUE");
        $vars = new \Magento\Framework\DataObject($vars);
        $tomail = 'TJ@gmail.com';
        $toname = 'TJ';

        if ($templateId && $senderDataId) {
            $transport = $this->_transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions(['area' => Area::AREA_ADMINHTML, 'store' => Store::DEFAULT_STORE_ID])
                ->setTemplateVars($vars->getData())
                ->setFrom($senderDataId)
                ->addTo($tomail, $toname)
                ->getTransport();
            $transport->sendMessage();
        }
    }

}