<?php

declare(strict_types=1);

namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Framework\App\State;
use Psr\Log\LoggerInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
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
     * @var SourceItemInterfaceFactory
     */
    protected SourceItemInterfaceFactory $sourceItemFactory;

    /**
     * @var SourceItemsSaveInterface
     */
    protected SourceItemsSaveInterface $sourceItemsSaveInterface;


    /**
     * @param EavSetupFactory $eavSetupFactory
     * @param ProductInterfaceFactory $productInterfaceFactory
     * @param CategoryLinkManagementInterface $categoryLinkManagement
     * @param SourceItemInterfaceFactory $sourceItemFactory
     * @param SourceItemsSaveInterface $sourceItemsSaveInterface
     * @param LoggerInterface $logger
     * @param State $state
     */
    public function __construct(
        EavSetupFactory                 $eavSetupFactory,
        ProductInterfaceFactory         $productInterfaceFactory,
        CategoryLinkManagementInterface $categoryLinkManagement,
        SourceItemInterfaceFactory      $sourceItemFactory,
        SourceItemsSaveInterface        $sourceItemsSaveInterface,
        LoggerInterface                 $logger,
        State                           $state
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->productInterfaceFactory = $productInterfaceFactory;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->logger = $logger;
        $this->state = $state;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->sourceItemsSaveInterface = $sourceItemsSaveInterface;
    }

    /**
     * Run code inside patch
     */
    public function apply()
    {
        try {
            $product = $this->productInterfaceFactory->create();

            if ($product->getIdBySku($this->productProperties['sku'])) {
                return;
            }

            $product->setSku($this->productProperties['sku'])
                ->setName($this->productProperties['name'])
                ->setStatus($this->productProperties['status'])
                ->setWeight($this->productProperties['weight'])
                ->setPrice($this->productProperties['price'])
                ->setVisibility($this->productProperties['visibility'])
                ->setTypeId($this->productProperties['type_id'])
                ->setStockData([
                    'use_config_manage_stock' => 1,
                    'is_qty_decimal' => 0,
                    'is_in_stock' => 1
                ]);

            $product->save();
            $this->categoryLinkManagement->assignProductToCategories($this->productProperties['sku'], $this->categoryIds);

            $sourceItem = $this->sourceItemFactory->create();
            $sourceItem->setSourceCode('default');

            $sourceItem->setQuantity(100);
            $sourceItem->setSku($product->getSku());

            $sourceItem->setStatus(SourceItemInterface::STATUS_IN_STOCK);
            $sourceItems[] = $sourceItem;

            $this->sourceItemsSaveInterface->execute($sourceItems);

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
