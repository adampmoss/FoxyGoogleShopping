<?php

namespace Magefox\GoogleShopping\Model;

use Magefox\GoogleShopping\Helper\Data;
use Magefox\GoogleShopping\Helper\Products;
use Magento\Store\Model\StoreManagerInterface;

class Xmlfeed
{
    /**
     * General Helper
     *
     * @var Data
     */
    private $_helper;

    /**
     * Product Helper
     *
     * @var Products
     */
    private $_productFeedHelper;

    /**
     * Store Manager
     *
     * @var Products
     */
    private $_storeManager;

    /**
     * Xmlfeed constructor.
     * @param Data $helper
     * @param Products $productFeedHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Data $helper,
        Products $productFeedHelper,
        StoreManagerInterface $storeManager

    )
    {
        $this->_helper = $helper;
        $this->_productFeedHelper = $productFeedHelper;
        $this->_storeManager = $storeManager;
    }

    /**
     * @return string
     */
    public function getFeed()
    {
        $xml = $this->getXmlHeader();
        $xml .= $this->getProductsXml();
        $xml .= $this->getXmlFooter();

        return $xml;
    }

    /**
     * @return string
     */
    public function getXmlHeader()
    {
        header("Content-Type: application/xml; charset=utf-8");

        $xml = '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">';
        $xml .= '<channel>';
        $xml .= '<title>' . $this->_helper->getConfig('google_default_title') . '</title>';
        $xml .= '<link>' . $this->_helper->getConfig('google_default_url') . '</link>';
        $xml .= '<description>' . $this->_helper->getConfig('google_default_description') . '</description>';

        return $xml;

    }

    /**
     * @return string
     */
    public function getXmlFooter()
    {
        return '</channel></rss>';
    }

    /**
     * @return string
     */
    public function getProductsXml()
    {
        $productCollection = $this->_productFeedHelper->getFilteredProducts();
        $xml = "";

        foreach ($productCollection as $product) {
            $xml .= "<item>" . $this->buildProductXml($product) . "</item>";
        }

        return $xml;
    }

    /**
     * @param $product
     * @return bool|string
     */
    public function buildProductXml($product)
    {
        $_description = $this->fixDescription($product->getDescription());
        $xml = $this->createNode("title", $product->getName(), true);
        $xml .= $this->createNode("link", $product->getProductUrl());
        $xml .= $this->createNode("description", $_description, true);
        $xml .= $this->createNode("g:product_type", $this->_productFeedHelper->getAttributeSet($product), true);
        $xml .= $this->createNode("g:image_link", $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA, true) . 'catalog/product' . $product->getImage());
        $xml .= $this->createNode('g:google_product_category',
            $this->_productFeedHelper->getProductValue($product, 'google_product_category'), true);
        $xml .= $this->createNode("g:availability", 'in stock');
        $xml .= $this->createNode('g:price', number_format($product->getFinalPrice(), 2, '.', '') . ' ' . $this->_productFeedHelper->getCurrentCurrencySymbol());
        if ($product->getSpecialPrice() != $product->getFinalPrice())
            $xml .= $this->createNode('g:sale_price', number_format($product->getSpecialPrice(), 2, '.', '') . ' ' . $this->_productFeedHelper->getCurrentCurrencySymbol());
        $_condition = $this->getAttributeText($product, 'condition');
        if (is_array($_condition))
            $xml .= $this->createNode("g:condition", $_condition[0]);
        else
            $xml .= $this->createNode("g:condition", $_condition);
        $xml .= $this->createNode("g:gtin", $this->getAttributeText($product, 'gr_ean'));
        $xml .= $this->createNode("g:id", $product->getId());
        $xml .= $this->createNode("g:brand", $this->getAttributeText($product, 'manufacturer'));
        $xml .= $this->createNode("g:mpn", $product->getSku());

        return $xml;
    }

    /**
     * @param $product
     * @param $attributeCode
     * @return bool
     */
    protected function getAttributeText($product, $attributeCode)
    {
        if ($attribute = $product->getResource()->getAttribute($attributeCode)) {
            return $attribute->getSource()->getOptionText($product->getData($attributeCode));
        } else {
            return false;
        }
    }

    /**
     * @param $data
     * @return string
     */
    public function fixDescription($data)
    {
        $description = $data;
        $encode = mb_detect_encoding($data);
        $description = mb_convert_encoding($description, 'UTF-8', $encode);

        return $description;
    }

    /**
     * @param $nodeName
     * @param $value
     * @param bool $cData
     * @return bool|string
     */
    public function createNode($nodeName, $value, $cData = false)
    {
        if (empty($value) || empty ($nodeName)) {
            return false;
        }

        $cDataStart = "";
        $cDataEnd = "";

        if ($cData === true) {
            $cDataStart = "<![CDATA[";
            $cDataEnd = "]]>";
        }

        $node = "<" . $nodeName . ">" . $cDataStart . $value . $cDataEnd . "</" . $nodeName . ">";

        return $node;
    }

}
