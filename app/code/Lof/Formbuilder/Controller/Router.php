<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://landofcoder.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_Formbuilder
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\Formbuilder\Controller;

class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * Event manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Page factory
     *
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_formFactory;

    /**
     * Config primary
     *
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * Url
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * Response
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @var bool
     */
    protected $dispatched;

    /**
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_helper;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Framework\App\ActionFactory       $actionFactory
     * @param \Magento\Framework\Event\ManagerInterface  $eventManager
     * @param \Magento\Framework\UrlInterface            $url
     * @param \Magento\Cms\Model\PageFactory             $pageFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResponseInterface   $response
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $url,
        \Lof\Formbuilder\Model\FormFactory $formFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResponseInterface $response,
        \Lof\Formbuilder\Helper\Data $data,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry
    )
    {
        $this->actionFactory = $actionFactory;
        $this->_eventManager = $eventManager;
        $this->_url = $url;
        $this->_formFactory = $formFactory;
        $this->_storeManager = $storeManager;
        $this->_response = $response;
        $this->_helper = $data;
        $this->_coreRegistry = $registry;
        $this->_customerSession = $customerSession;
    }

    /**
     * Validate and Match Cms Page and modify request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->dispatched) {
            $identifier = trim($request->getPathInfo(), '/');
            $origUrlKey = $identifier;

            $condition = new \Magento\Framework\DataObject(['identifier' => $identifier, 'continue' => true]);
            $this->_eventManager->dispatch(
                'formbuilder_controller_router_match_before',
                ['router' => $this, 'condition' => $condition]
            );
            $identifier = $condition->getIdentifier();

            if ($condition->getRedirectUrl()) {
                $this->response->setRedirect($condition->getRedirectUrl());
                $request->setDispatched(true);
                return $this->actionFactory->create(
                    'Magento\Framework\App\Action\Redirect',
                    ['request' => $request]
                );
            }

            if (!$condition->getContinue()) {
                return null;
            }
            $enable = $this->_helper->getConfig('general_settings/enable');
            $route = $this->_helper->getConfig('general_settings/route');

            $identifiers = explode('/', $identifier);
            if (count($identifiers) == 2) $identifier = $identifiers[0];
            if ($route != '' && $route != $identifier) {
                $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
                $this->dispatched = true;
                return $this->actionFactory->create(
                    'Magento\Framework\App\Action\Forward',
                    ['request' => $request]
                );
            }
            if (count($identifiers) == 2) $identifier = $identifiers[1];
            $form = $this->_formFactory->create();
            $formId = $form->checkIdentifier($identifier, $this->_storeManager->getStore()->getId());


            if (!$formId) {
                return null;
            }
            $form->load($formId);
            $customergroups = $form->getData('customergroups');
            $customerGroupId = $this->_customerSession->getCustomerId();

            if (!in_array(0, $customergroups)) {
                if (!in_array($customerGroupId, $customergroups)) return null;
            }

            if (!$enable || !$form->getStatus()) return false;
            $this->_coreRegistry->register("current_form", $form);
            $request->setModuleName('lofformbuilder')
                ->setControllerName('form')
                ->setActionName('view')
                ->setParam('form_id', $formId);
            $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
            $this->dispatched = true;
            return $this->actionFactory->create(
                'Magento\Framework\App\Action\Forward',
                ['request' => $request]
            );
        }
    }
}
