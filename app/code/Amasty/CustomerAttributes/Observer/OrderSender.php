<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */


namespace Amasty\CustomerAttributes\Observer;

use Magento\Framework\Event\ObserverInterface;

class OrderSender implements ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;

    /**
     * @var \Amasty\CustomerAttributes\Model\Customer\GuestAttributesFactory
     */
    private $guestAttributesFactory;

    /**
     * @var \Magento\Customer\Model\AttributeMetadataDataProvider
     */
    private $attributeMetadataDataProvider;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider,
        \Amasty\CustomerAttributes\Model\Customer\GuestAttributesFactory $guestAttributesFactory
    ) {
        $this->objectManager = $objectManager;
        $this->customerFactory = $customerFactory;
        $this->guestAttributesFactory = $guestAttributesFactory;
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $transport = $observer->getTransport();
        /** @var \Magento\Sales\Model\Order|null $order */
        $order = null;
        if (is_object($transport)) {
            $order = $transport->getOrder();
        } elseif (is_array($transport) && array_key_exists('order', $transport)) {
            $order = $transport['order'];
        }

        if ($order) {
            $order->setCustomer($this->getCustomer($order));
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return \Magento\Customer\Model\Customer
     */
    protected function getCustomer($order)
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $this->customerFactory->create();
        $customer->setStore($order->getStore());
        $customer->setWebsiteId($order->getStore()->getWebsite()->getId());
        if ($order->getCustomerId()) {
            $customer->loadByEmail($order->getCustomerEmail());
        } else {
            /** @var \Amasty\CustomerAttributes\Model\Customer\GuestAttributes $model */
            $model = $this->guestAttributesFactory->create()->loadByOrderId($order->getId());
            if ($model && $model->getId()) {
                foreach ($model->getData() as $key => $value) {
                    if ($key == 'id') {
                        continue;
                    }
                    if ($value) {
                        $customer->setData($key, $value);
                    }
                }
            }
        }

        /*convert option value to option text*/
        $attributes = $this->attributeMetadataDataProvider->loadAttributesCollection(
            'customer',
            'amasty_custom_attribute'
        );
        foreach ($attributes as $attribute) {
            if ($attribute->usesSource()) {
                $code = $attribute->getAttributeCode();
                $value = $customer->getData($code);
                $value = $attribute->getSource()->getOptionText($value);
                $customer->setData($code, $value);
            }
        }

        return $customer;
    }
}
