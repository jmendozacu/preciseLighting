<?php
/**
 * Copyright (c) 2018 MageWorkshop. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MageWorkshop\CustomerPermissions\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\Session\SessionManager;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Model\StoreManagerInterface;

class RulesHelper extends AbstractHelper
{
    const XML_PATH_MAGEWORKSHOP_CUSTOMER_PERMISSIONS_GENERAL_ENABLED = 'mageworkshop_detailedreview/mageworkshop_customerpermissions/enabled';
    const XML_PATH_MAGEWORKSHOP_CUSTOMER_PERMISSIONS_GENERAL_ENABLED_AUTO_APPROVE = 'mageworkshop_detailedreview/mageworkshop_customerpermissions/enabled_auto_approve';

    /** @var Session $customerSession */
    protected $customerSession;

    /** @var SessionManager $sessionManager */
    protected $sessionManager;

    /** @var CustomerRepositoryInterface $customerRepositoryInterface */
    protected $customerRepositoryInterface;

    /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
    protected $searchCriteriaBuilder;

    /** @var CustomerFactory $customerFactory */
    protected $customerFactory;

    /** @var StoreManagerInterface $scopeConfig */
    protected $storeManagerInterface;

    /**
     * RulesHelper constructor.
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param SessionManager $sessionManager
     * @param StoreManagerInterface $storeManagerInterface
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepositoryInterface,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        SessionManager $sessionManager,
        StoreManagerInterface $storeManagerInterface
    ) {
        $this->searchCriteriaBuilder       = $searchCriteriaBuilder;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->customerFactory             = $customerFactory;
        $this->customerSession             = $customerSession;
        $this->sessionManager              = $sessionManager;
        $this->storeManagerInterface       = $storeManagerInterface;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomerEmail()
    {
        if ($this->customerSession->isLoggedIn()) {
            $email = $this->customerSession->getCustomer()->getEmail();
        } else {
            $email = $this->sessionManager->getData('customer_email') ? $this->sessionManager->getData('customer_email'): '';
        }
        return $email;
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomerModel()
    {
        if (!$this->customerHasData() || $this->isCustomerLoggedIn()) {
            $customer = $this->customerSession->getCustomer();
        }  else {
            $websiteId = $this->storeManagerInterface->getWebsite()->getId();
            $customer = $this->customerFactory->create(['data' => ['website_id' => $websiteId]])->loadByEmail($this->sessionManager->getData('customer_email'));
        }
        return $customer;
    }

    /**
     * @return bool
     */
    protected function isCustomerLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * @return bool
     */
    public function customerHasData()
    {
        return ($this->sessionManager->getData('customer_email') || $this->isCustomerLoggedIn());
    }

    /**
     * @return bool
     */
    public function isModuleEnabled()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MAGEWORKSHOP_CUSTOMER_PERMISSIONS_GENERAL_ENABLED);
    }

    public function isAutoApproveEnabled()
    {
        return ($this->isModuleEnabled() && $this->scopeConfig->getValue(self::XML_PATH_MAGEWORKSHOP_CUSTOMER_PERMISSIONS_GENERAL_ENABLED_AUTO_APPROVE));

    }

}
