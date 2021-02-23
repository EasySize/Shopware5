<?php

namespace Easysize;

use Shopware\Components\Plugin;


class Easysize extends Plugin
{
    public function install($context)
    {
        $service = $this->container->get('shopware_attribute.crud_service');
        $service->update('s_order_basket_attributes', '_easysizeID', 'string');
        $service->update('s_order_details_attributes', '_easysizeID', 'string', [
            'label' => 'EasysizeID',
            'displayInBackend' => true,
            'custom' => true,
            'position' => 0,
        ]);
    }

    public function uninstall($context)
    {
        $this->removeAttribute($context, 's_order_basket_attributes', '_easysizeID');
        $this->removeAttribute($context, 's_order_details_attributes', '_easysizeID');
    }

    private function removeAttribute($context, $table, $attribute) {
        if ($context->keepUserData()) {
            return;
        }

        $service = $this->container->get('shopware_attribute.crud_service');
        $attributeExists = $service->get($table, $attribute);

        if ($attributeExists === NULL) {
            return;
        }

        $service->delete($table, $attribute);
    }
}
