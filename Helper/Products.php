<?php

namespace Magefox\GoogleShopping\Helper;

class Products extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Eav\ModelAttributeSetRepository
     */
    protected $_attributeSetRepo;

    /**
     * @var \Magefox\GoogleShopping\Helper\Data
     */
    protected $_helper;

    /**
    * @var \Magento\Store\Model\StoreManagerInterface
    */
    public $_storeManager;

    /**
    * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
    */
    public $_productStatus;

    /**
    * @var \Magento\Catalog\Model\Product\Visibility
    */
    public $_productVisibility;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Eav\Model\AttributeSetRepository $attributeSetRepo,
        \Magefox\GoogleShopping\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility
    )
    {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_attributeSetRepo = $attributeSetRepo;
        $this->_helper = $helper;
        $this->_storeManager = $storeManager;
        $this->_productStatus = $productStatus;
        $this->_productVisibility = $productVisibility;
        parent::__construct($context);
    }

    public function getFilteredProducts()
    {
        $collection = $this->_productCollectionFactory->create();
        // $collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $collection->addAttributeToFilter('status', ['in' => $this->_productStatus->getVisibleStatusIds()]);
        $collection->setVisibility($this->_productVisibility->getVisibleInSiteIds());

        return $collection;
    }

    public function getAttributeSet($product)
    {
        $attributeSetId = $product->getAttributeSetId();
        $attributeSet = $this->_attributeSetRepo->get($attributeSetId);

        return $attributeSet->getAttributeSetName();

    }

    public function getProductValue($product, $attributeCode)
    {
        $attributeCodeFromConfig = $this->_helper->getConfig($attributeCode.'_attribute');
        $defaultValue = $this->_helper->getConfig('default_'.$attributeCode);

        if (!empty($attributeCodeFromConfig))
        {
            return $product->getAttributeText($attributeCodeFromConfig);
        }

        if (!empty($defaultValue))
        {
            return $defaultValue;
        }

        return false;
    }

    public function getCurrentCurrencySymbol()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }
}
