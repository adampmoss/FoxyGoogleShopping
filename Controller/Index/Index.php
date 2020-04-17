<?php

namespace Magefox\GoogleShopping\Controller\Index;

use Magefox\GoogleShopping\Helper\Data;
use Magefox\GoogleShopping\Model\Xmlfeed;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;

class Index extends Action
{
    /**
     * XmlFeed Model
     *
     * @var Xmlfeed
     */
    protected $xmlFeed;

    /**
     * General Helper
     *
     * @var Data
     */
    private $helper;

    /**
     * Result Forward Factory
     *
     * @var Data
     */
    private $resultForward;

    /**
     * Index constructor.
     * @param Context $context
     * @param Xmlfeed $xmlFeed
     * @param Data $helper
     * @param ForwardFactory $resultForwardFactory
     */
    public function __construct(
        Context $context,
        Xmlfeed $xmlFeed,
        Data $helper,
        ForwardFactory $resultForwardFactory
    )
    {
        $this->xmlFeed = $xmlFeed;
        $this->helper = $helper;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultForward = $this->resultForwardFactory->create();

        if (!empty($this->helper->getConfig('enabled'))) {
            echo $this->xmlFeed->getFeed();
        } else {
            $resultForward->forward('noroute');
        }
    }

}
