<?php
namespace IntechSoft\CustomImport\Controller\Test;

use Magento\Framework\App\Action\Context;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use IntechSoft\CustomImport\Model\ImportFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Stdlib\DateTime\DateTime;
use IntechSoft\CustomImport\Helper\UrlRegenerate;

class Testday extends \Magento\Framework\App\Action\Action
{
	const CUSTOM_IMPORT_FOLDER = 'import/cron/day';
    const SUCCESS_MESSAGE      = 'Import finished successfully from file - ';
    const FAIL_MESSAGE         = 'Import fail from file - ';
	/**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_uploader;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \IntechSoft\CustomImport\Model\Import
     */
    protected $_importModel;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $_directoryList;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \IntechSoft\CustomImport\Helper\UrlRegenerate
     */
    protected $_urlRegenerateHelper;

    /**
     * Import constructor.
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploader
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \IntechSoft\CustomImport\Model\ImportFactory $importModel
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \IntechSoft\CustomImport\Helper\UrlRegenerate $urlRegenerate
     */
	public function __construct(Context $context,
	UploaderFactory $uploader,
	Filesystem $filesystem,
	ImportFactory $importModel,
	DirectoryList $directoryList,
	DateTime $date,
	UrlRegenerate $urlRegenerate) {
		$this->_uploader            = $uploader;
        $this->_filesystem          = $filesystem;
        $this->_importModel         = $importModel;
        $this->_directoryList       = $directoryList;
        $this->_date                = $date;
        $this->_urlRegenerateHelper = $urlRegenerate;

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/testtestday.log');
        $this->_logger = new \Zend\Log\Logger();
        $this->_logger->addWriter($writer);
        parent::__construct($context);
    }
	/**
     * Method executed when cron runs in server
     */
    public function execute()
    {
        ini_set('memory_limit', '2048M');
        ini_set('display_errors', '1');
		error_reporting(E_ALL);

        $this->_logger->info('daily cron started at - ' . $this->_date->gmtDate('Y-m-d H:i:s'));
        $importDir = $this->_directoryList->getPath(DirectoryList::VAR_DIR) . '/' . self::CUSTOM_IMPORT_FOLDER ;
        $this->_logger->info('$importDir - '.$importDir);

        if(!is_dir($importDir)) {
            mkdir($importDir, 0775);
        }

        $fileList = scandir($importDir);

        $i = 0;
        foreach ($fileList as $file) {
            if ($file == '.' || $file == '..'){
                continue;
            }
            $i++;

            $importedFileName = $importDir . '/' . $file;
            $this->_logger->info('$importedFileName - '.$importedFileName);
            $importModel = $this->_importModel->create();
            $importModel->setCsvFile($importedFileName, true)->process();

            if (count($importModel->errors) == 0) {
                $this->_logger->info(self::SUCCESS_MESSAGE . $file);
                unlink($importDir. '/' .$file);
                $this->_urlRegenerateHelper->regenerateUrl();
            } else {
                foreach ($importModel->errors as $error) {
                    if (is_array($error)) {
                        $error = implode(' - ', $error);
                    }
                    $this->_logger->info( $error);
                }
            }

            if($i <= 1) {
                break;
            }
        }
        $this->_logger->info('daily cron finished at - ' . $this->_date->gmtDate('Y-m-d H:i:s'));
    }
}