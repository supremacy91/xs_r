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

namespace Lof\Formbuilder\Controller\Form;

use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class Post extends \Magento\Framework\App\Action\Action
{
    const FILE_TYPES = 'jpg,JPG,jpeg,JPEG,gif,GIF,png,PNG,doc,DOC,DOCX,docx,pdf,PDF,zip,ZIP,tar,TAR,rar,RAR,tgz,TGZ,7zip,7ZIP,gz,GZ';

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var \Lof\Formbuilder\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Lof\Formbuilder\Model\Form
     */
    protected $_form;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @param Context                                              $context
     * @param \Magento\Store\Model\StoreManager                    $storeManager
     * @param \Magento\Framework\View\Result\PageFactory           $resultPageFactory
     * @param \Lof\Formbuilder\Helper\Data                         $helper
     * @param \Magento\Framework\Controller\Result\ForwardFactory  $resultForwardFactory
     * @param \Magento\Framework\Registry                          $registry
     * @param \Magento\Framework\Translate\Inline\StateInterface   $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder    $transportBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface   $scopeConfig
     * @param \Lof\Formbuilder\Model\Form                          $form
     * @param \Magento\Framework\View\LayoutInterface              $layout
     * @param \Magento\Customer\Model\Session                      $customerSession
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     */
    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Lof\Formbuilder\Helper\Data $helper,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Lof\Formbuilder\Model\Form $form,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
    )
    {
        $this->_storeManager = $storeManager;
        $this->resultPageFactory = $resultPageFactory;
        $this->_helper = $helper;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_coreRegistry = $registry;
        $this->inlineTranslation = $inlineTranslation;
        $this->_form = $form;
        $this->_transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->_layout = $layout;
        $this->_customerSession = $customerSession;
        $this->_remoteAddress = $remoteAddress;
        parent::__construct($context);
    }

    /**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $error = true;
        $data = $this->getRequest()->getParams();
        if (!$this->getRequest()->isAjax()) {
            //return $this->_redirect($data['return_url']);
        }

        $rMessage = [];
        $rMessage['status'] = true;

        if (count($data) > 2) {
            $form = $this->_form->load($data['formId']);
            $fields = $form->getFields();
            $successMessage = $this->_helper->filter($form->getData('success_message'));

            // reCaptcha
            if (isset($_POST['g-recaptcha-response']) && ((int)$_POST['g-recaptcha-response']) === 0) {
                $this->messageManager->addError(__('Please check reCaptcha and try again.'));
                $this->_redirect($data['return_url']);
                return;
            }
            if (isset($_POST['g-recaptcha-response'])) {
                $captcha = $_POST['g-recaptcha-response'];
                $secretKey = $this->_helper->getConfig('general_settings/captcha_privatekey');
                $ip = $_SERVER['REMOTE_ADDR'];
                $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $secretKey . "&response=" . $captcha . "&remoteip=" . $ip);
                $responseKeys = json_decode($response, true);
                if (intval($responseKeys["success"]) !== 1) {
                    $this->messageManager->addError(__('Please check reCaptcha and try again.'));
                    $this->_redirect($data['return_url']);
                    return;
                }
            }

            // UPLOAD FILE
            $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
                ->getDirectoryRead(DirectoryList::MEDIA);
            $mediaFolder = 'lof/formbuilder/files';
            $path = $mediaDirectory->getAbsolutePath($mediaFolder);
            foreach ($fields as $key => $field) {
                if (empty($field)) {
                    continue;
                }
                if ($field['field_type'] == 'file_upload' && $_FILES['form_file_' . $field['cid']]['size']) {
                    $cid = $field['cid'];
                    $field_label = isset($field['label']) ? $field['label'] : '';
                    if ($field && isset($field['image_maximum_size']) && $field['image_maximum_size']) {
                        $image_maximum_size = $field['image_maximum_size'];
                    }

                    if (isset($field['image_maximum_size']) && ($image_maximum_size * 1024 * 1024) < $_FILES['form_file_' . $field['cid']]['size']) {
                        $this->messageManager->addError(__($field_label . ' - The file is too big.'));
                        return;
                    }
                    $uploader = $this->_objectManager->create(
                        'Magento\Framework\File\Uploader',
                        array('fileId' => 'form_file_' . $field['cid'])
                    );
                    $fieldTypes = '';
                    if (isset($field['image_type'])) {
                        $fieldTypes = $field['image_type'];
                    }
                    if (!$fieldTypes) {
                        $fieldTypes = self::FILE_TYPES;
                    }
                    if (!is_array($fieldTypes)) {
                        $fieldTypes = explode(',', $fieldTypes);
                    }
                    try {
                        $uploader->setAllowedExtensions($fieldTypes);
                        $uploader->setAllowRenameFiles(true);
                        $uploader->setFilesDispersion(false);
                        try {
                            $file = $uploader->save($path);
                        } catch (\Exception $e) {
                            $this->messageManager->addError(__($field_label) . ' - ' . $e->getMessage());
                            $this->_redirect($data['return_url']);
                            return;
                        }
                        $field_name = $this->_helper->getFieldPrefix() . $cid;

                        $imgExtens = array("gif", "jpeg", "jpg", "png");
                        $temp = explode(".", $file['file']);
                        $extension = end($temp);
                        $data[$field_name] = $field_name;
                        $data[$field_name . '_filename'] = $file['file'];
                        $data[$field_name . '_fileurl'] = $mediaUrl . $mediaFolder . '/' . $file['file'];
                        $data[$field_name . '_filesize'] = $file['size'];
                        if (in_array($extension, $imgExtens)) {
                            $data[$field_name . '_isimage'] = true;
                        }
                    } catch (Exception $e) {
                        $this->messageManager->addError(__($field_label) . ' - ' . $e->getMessage());
                        $this->_redirect($data['return_url']);
                        return;
                    }
                }
            }

            //Build email data object
            $custom_form_data = $form->getCustomFormFields($data);

            $data['message'] = $this->_layout->createBlock('\Magento\Framework\View\Element\Template')
                ->setTemplate("Lof_Formbuilder::email/items.phtml")
                ->setCustomFormData($custom_form_data)
                ->toHtml();

            /*Format form data to save in message params*/
            $form_submit_data = [];
            if ($custom_form_data) {
                foreach ($custom_form_data as $key => $val) {
                    if (isset($form_submit_data[$val['label']])) {
                        $val['label'] .= " " . $key;
                    }
                    $form_submit_data[$val['label']] = $val['value'];
                }
            }

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $uCode = $this->_helper->getConfig('email_settings/sender_email_identity');
            $sender_email = $this->scopeConfig->getValue('trans_email/ident_' . $uCode . '/name', $storeScope);
            //Save message
            $message_data = [];
            $message_data['form_id'] = $form->getFormId();
            $message_data['ip_address'] = $this->_remoteAddress->getRemoteAddress();
            $message_data['ip_address_long'] = $this->_remoteAddress->getRemoteAddress(true);
            $message_data['customer_id'] = $this->_customerSession->getCustomerId();

            $params = [];
            $params['brower'] = $_SERVER['HTTP_USER_AGENT'];
            $params['submit_data'] = $form_submit_data;
            $params['http_host'] = $_SERVER['HTTP_HOST'];
            $message_data['params'] = serialize($params);
            $message_data['message'] = $data['message'];
            $message_data['creation_time'] = date('Y-m-d H:i:s');
            $message_data['email_from'] = $sender_email;

            $message = $this->_objectManager->create('Lof\Formbuilder\Model\Message');
            $message->setData($message_data);
            $message->save();

            // SEND EMAIL
            $this->inlineTranslation->suspend();
            $enable_testmode = $this->_helper->getConfig('email_settings/enable_testmode');
            if (!$enable_testmode && trim($form->getData('email_receive')) != '') {
                $emails = $form->getData('email_receive');
                $emails = explode(',', $emails);
                foreach ($emails as $k => $v) {
                    try {
                        $postObject = new \Magento\Framework\DataObject();
                        $datap['form_id'] = $form->getFormId();
                        $postObject->setData($data);
                        $error = false;
                        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                        $transport = $this->_transportBuilder
                            ->setTemplateIdentifier($form->getData('email_template'))
                            ->setTemplateOptions(
                                [
                                    'area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                                ]
                            )
                            ->setTemplateVars(['data' => $postObject])
                            ->setFrom($this->_helper->getConfig('email_settings/sender_email_identity'))
                            ->addTo($v)
                            ->setReplyTo($v)
                            ->getTransport();
                        try {
                            $transport->sendMessage();
                            $this->inlineTranslation->resume();
                        } catch (\Exception $e) {
                            $error = false;
                            $this->messageManager->addError(
                                __('We can\'t process your request right now. Sorry, that\'s all we know.')
                            );
                        }
                    } catch (\Exception $e) {
                        $this->inlineTranslation->resume();
                        $this->messageManager->addError(
                            __('We can\'t process your request right now. Sorry, that\'s all we know.')
                        );
                        return;
                    }
                }
            }

            if ($error && ($successMessage)) {
                $successMessage = $this->_helper->filter($successMessage);
                $this->messageManager->addSuccess($successMessage);
            }
            if ($form->getData('redirect_link')) {
                $redirect_link = $form->getRedirectLink();
            } else {
                $redirect_link = $data['return_url'];
            }
            $this->_redirect($redirect_link);
            return;
        }
        return $resultRedirect->setRefererOrBaseUrl();
    }

    public function getMessage($message)
    {
        return $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($message)
        );
    }

    /**
     * Set back redirect url to response
     *
     * @param null|string $backUrl
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function _goBack($backUrl = null)
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($backUrl) {
            $resultRedirect->setUrl($backUrl);
        }
        return $resultRedirect;
    }
}