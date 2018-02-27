<?php
namespace Biztech\Manufacturer\Block;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Session\SidResolverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\ConfigInterface;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\View\TemplateEnginePool;
use Magento\Framework\App\State;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Element\Template\File\Resolver;
use Magento\Framework\View\Element\Template\File\Validator;
use Biztech\Manufacturer\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\UrlFactory;

class Context extends \Magento\Framework\View\Element\Template\Context
{
    /**
     * @var \Biztech\Manufacturer\Helper\Data
     */
    protected $devToolHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Biztech\Manufacturer\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\UrlFactory
     */
    protected $urlFactory;

    public function __construct(
        RequestInterface $request,
        LayoutInterface $layout,
        ManagerInterface $eventManager,
        UrlInterface $urlBuilder,
        CacheInterface $cache,
        DesignInterface $design,
        SessionManagerInterface $session,
        SidResolverInterface $sidResolver,
        ScopeConfigInterface $scopeConfig,
        Repository $assetRepo,
        ConfigInterface $viewConfig,
        StateInterface $cacheState,
        \Psr\Log\LoggerInterface $logger,
        Escaper $escaper,
        FilterManager $filterManager,
        TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        Filesystem $filesystem,
        \Magento\Framework\View\FileSystem $viewFileSystem,
        TemplateEnginePool $enginePool,
        State $appState,
        StoreManagerInterface $storeManager,
        Config $pageConfig,
        Resolver $resolver,
        Validator $validator,
        Data $devToolHelper,
        Registry $registry,
        \Biztech\Manufacturer\Model\Config $config,
        ObjectManagerInterface $objectManager,
        UrlFactory $urlFactory
    )
    {
        $this->devToolHelper = $devToolHelper;
        $this->registry = $registry;
        $this->config = $config;
        $this->objectManager = $objectManager;
        $this->urlFactory = $urlFactory;
        parent::__construct(
            $request,
            $layout,
            $eventManager,
            $urlBuilder,
            $cache,
            $design,
            $session,
            $sidResolver,
            $scopeConfig,
            $assetRepo,
            $viewConfig,
            $cacheState,
            $logger,
            $escaper,
            $filterManager,
            $localeDate,
            $inlineTranslation,
            $filesystem,
            $viewFileSystem,
            $enginePool,
            $appState,
            $storeManager,
            $pageConfig,
            $resolver, $validator
        );
    }


    /**
     * @return Data
     */
    public function getManufacturerHelper()
    {
        return $this->devToolHelper;
    }


    /**
     * @return Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }


    /**
     * @return \Biztech\Manufacturer\Model\Config
     */
    public function getConfig()
    {
        return $this->config;
    }


    /**
     * @return ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }


    /**
     * @return UrlFactory
     */
    public function getUrlFactory()
    {
        return $this->urlFactory;
    }

}
