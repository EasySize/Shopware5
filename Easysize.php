<?php

namespace Easysize;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Easysize extends Plugin
{

    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->setParameter($this->getContainerPrefix() . '.view_dir', $this->getPath() . '/Resources/views');
    }

    /**
     * @param InstallContext $context
     */
    public function install(InstallContext $context)
    {
        $service = $this->container->get('shopware_attribute.crud_service');
        $service->update('s_order_basket_attributes', '_easysizeID', 'string');
        $service->update('s_order_details_attributes', '_easysizeID', 'string', [
            'label' => 'EasysizeID',
            'displayInBackend' => true,
            'custom' => true,
            'position' => 0,
        ]);
        $this->container->get('models')->generateAttributeModels(['s_order_basket_attributes', 's_order_details_attributes']);
    }

    /**
     * @param UninstallContext $context
     */
    public function uninstall(UninstallContext $context)
    {
        if (!$context->keepUserData()) {
            $this->removeAttribute('s_order_basket_attributes', '_easysizeID');
            $this->removeAttribute('s_order_details_attributes', '_easysizeID');

            $this->container->get('models')->generateAttributeModels(['s_order_basket_attributes', 's_order_details_attributes']);
        }
    }

    /**
     * @param string $table
     * @param string $attribute
     */
    private function removeAttribute(string $table, string $attribute)
    {
        $service = $this->container->get('shopware_attribute.crud_service');
        $attributeExists = $service->get($table, $attribute);

        if ($attributeExists === NULL) {
            return;
        }

        $service->delete($table, $attribute);
    }
}
