<?php

namespace Easysize\Subscribers;

use Enlight\Event\SubscriberInterface;
use Shopware\Components\Plugin\ConfigReader;
use \Symfony\Component\DependencyInjection\ContainerInterface;

class EasysizeSubscriber implements SubscriberInterface
{

    /** @var ConfigReader */
    private $configReader;

    /** @var string */
    private $pluginName;

    /** @var string */
    private $viewDir;

    /**
     * EasysizeSubscriber constructor.
     * @param ConfigReader $configReader
     * @param string $pluginName
     * @param string $viewDir
     */
    public function __construct(ContainerInterface $container, ConfigReader $configReader, string $pluginName, string $viewDir)
    {
        $this->container = $container;
        $this->configReader = $configReader;
        $this->pluginName = $pluginName;
        $this->viewDir = $viewDir;
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents()
    {
        return [
            'Theme_Inheritance_Template_Directories_Collected' => 'onTemplateDirectoriesCollect',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Detail' => 'onPostDispatchFrontendDetail',
            'Shopware_Modules_Basket_AddArticle_Added' => 'onAddArticle'
        ];
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onTemplateDirectoriesCollect(\Enlight_Event_EventArgs $args)
    {
        $dirs = $args->getReturn();

        $dirs[] = $this->viewDir;

        $args->setReturn($dirs);
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onPostDispatchFrontendDetail(\Enlight_Event_EventArgs $args)
    {
        $shop = false;
        if ($this->container->initialized('shop')) {
            $shop = $this->container->get('shop');
        }

        if (!$shop) {
            $shop = $this->container->get('models')->getRepository(\Shopware\Models\Shop\Shop::class)->getActiveDefault();
        }

        $controller = $args->getSubject();

        $config = $this->configReader->getByPluginName($this->pluginName, $shop);
        $controller->View()->assign('es_config', $config);
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     * @throws \Zend_Db_Adapter_Exception
     */
    public function onAddArticle(\Enlight_Event_EventArgs $args)
    {
        $request = Shopware()->Front()->Request();

        $easysize_id = $request->getParam('_esid');
        $basketID = $args->getId();

        $sql = 'UPDATE `s_order_basket_attributes` SET `_easysizeid` = ? WHERE `basketID` = ?';
        Shopware()->Db()->query($sql, [$easysize_id, $basketID]);
    }
}