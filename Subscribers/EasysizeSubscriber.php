<?php

namespace Easysize\Subscribers;

use Enlight\Event\SubscriberInterface;


class EasysizeSubscriber implements SubscriberInterface
{
    private $config;

    public function __construct()
    {
        $this->config = Shopware()->Container()->get('shopware.plugin.cached_config_reader')->getByPluginName('Easysize');
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PreDispatch_Frontend_Detail' => 'onPostDispatchFrontendDetail',
            'Shopware_Modules_Basket_AddArticle_Added' => 'onAddArticle',
            'Shopware_Modules_Order_SaveOrder_FilterDetailAttributes' => 'onSaveOrder'
        ];
    }

    public function onPostDispatchFrontendDetail($args)
    {
        $controller = $args->getSubject();

        $controller->View()->addTemplateDir(__DIR__ . '/../Resources/views');
        $controller->View()->assign('es_config', $this->config);
    }

    public function onAddArticle($args)
    {
        $request = Shopware()->Front()->Request();

        $easysize_id = $request->getParam('_esid');
        $basketID = $args->getId();

        $sql = 'UPDATE `s_order_basket_attributes` SET `_easysizeid` = ? WHERE `basketID` = ?';
        Shopware()->Db()->query($sql, [$easysize_id, $basketID]);
    }

    public function onSaveOrder($args)
    {
        $basket_id = $args->get('basketRow');
        $orderdetails_id = $args->get('orderdetailsID');

        $sql = 'SELECT `_easysizeid` FROM `s_order_basket_attributes` WHERE `basketID` = ?';
        $data = Shopware()->Db()->fetchRow($sql, [$basket_id]);

        $sql = 'UPDATE `s_order_details_attributes` SET `_easysizeid` = ? WHERE `detailID` = ?';
        Shopware()->Db()->query($sql, [$data['_easysizeid'], $orderdetails_id]);
    }
}