<?php

declare(strict_types=1);

namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Framework\App\State;
use Psr\Log\LoggerInterface;
use Exception;

class AddSimpleproductProduct implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private EavSetupFactory $eavSetupFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductInterfaceFactory $productInterfaceFactory;

    /**
     * @var CategoryLinkManagementInterface
     */
    private CategoryLinkManagementInterface $categoryLinkManagement;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

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
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param ProductInterfaceFactory $productInterfaceFactory
     * @param CategoryLinkManagementInterface $categoryLinkManagement
     * @param LoggerInterface $logger
     * @param State $state
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        ModuleDataSetupInterface        $moduleDataSetup,
        EavSetupFactory                 $eavSetupFactory,
        ProductInterfaceFactory         $productInterfaceFactory,
        CategoryLinkManagementInterface $categoryLinkManagement,
        LoggerInterface                 $logger,
        State                           $state
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->productInterfaceFactory = $productInterfaceFactory;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->logger = $logger;
        $state->setAreaCode('adminhtml');
    }

    /**
     * Run code inside patch
     */
    public function apply()
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
