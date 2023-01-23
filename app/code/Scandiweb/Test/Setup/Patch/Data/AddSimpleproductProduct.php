<?php

declare(strict_types=1);

namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Framework\App\State;
use Psr\Log\LoggerInterface;
use Exception;

class AddSimpleproductProduct implements DataPatchInterface
{
    /**
     * @var EavSetupFactory
     */
    protected EavSetupFactory $eavSetupFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected ProductInterfaceFactory $productInterfaceFactory;

    /**
     * @var CategoryLinkManagementInterface
     */
    protected CategoryLinkManagementInterface $categoryLinkManagement;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var array
     */
    protected $productProperties = [
        'sku' => 'simple-product',
        'name' => 'Simple Product',
        'status' => 1,
        'weight' => 2,
        'price' => 0,
        'visibility' => 1,
        'type_id' => 'simple',
    ];

    /**
     * @var array
     */
    protected $categoryIds = [3];

    /**
     * @var State
     */
    protected State $state;

    /**
     * @param EavSetupFactory $eavSetupFactory
     * @param ProductInterfaceFactory $productInterfaceFactory
     * @param CategoryLinkManagementInterface $categoryLinkManagement
     * @param LoggerInterface $logger
     * @param State $state
     */
    public function __construct(
        EavSetupFactory                 $eavSetupFactory,
        ProductInterfaceFactory         $productInterfaceFactory,
        CategoryLinkManagementInterface $categoryLinkManagement,
        LoggerInterface                 $logger,
        State                           $state
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->productInterfaceFactory = $productInterfaceFactory;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->logger = $logger;
        $this->state = $state;
    }

    /**
     * Run code inside patch
     */
    public function apply()
    {
        $this->appState->emulateAreaCode('adminhtml', [$this, 'execute']);
    }

    public function execute()
    {
        try {
            $product = $this->productInterfaceFactory->create();

            $product->setSku($this->productProperties['sku'])
                ->setName($this->productProperties['name'])
                ->setStatus($this->productProperties['status'])
                ->setWeight($this->productProperties['weight'])
                ->setPrice($this->productProperties['price'])
                ->setVisibility($this->productProperties['visibility'])
                ->setTypeId($this->productProperties['type_id'])
                ->setStockData(
                    [
                        'use_config_manage_stock' => 0,
                        'manage_stock' => 1,
                        'is_in_stock' => 1,
                        'qty' => 999999999
                    ]
                );

            $product->save();
            $this->categoryLinkManagement->assignProductToCategories($this->productProperties['sku'], $this->categoryIds);
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * Get array of patches that have to be executed prior to this.
     * @return string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }
}
