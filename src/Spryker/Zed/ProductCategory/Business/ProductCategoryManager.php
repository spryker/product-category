<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\ProductCategory\Business;

use Generated\Shared\Transfer\ProductAbstractTransfer;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\NodeTransfer;
use Propel\Runtime\Connection\ConnectionInterface;
use Spryker\Shared\ProductCategory\ProductCategoryConstants;
use Spryker\Zed\Category\Persistence\CategoryQueryContainerInterface;
use Spryker\Zed\ProductCategory\Business\Exception\ProductCategoryMappingExistsException;
use Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToCmsInterface;
use Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToCategoryInterface;
use Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToProductInterface;
use Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToTouchInterface;
use Spryker\Zed\ProductCategory\Persistence\ProductCategoryQueryContainerInterface;
use Orm\Zed\ProductCategory\Persistence\SpyProductCategory;

class ProductCategoryManager implements ProductCategoryManagerInterface
{

    /**
     * @var \Spryker\Zed\Category\Persistence\CategoryQueryContainerInterface
     */
    protected $categoryQueryContainer;

    /**
     * @var \Spryker\Zed\ProductCategory\Persistence\ProductCategoryQueryContainerInterface
     */
    protected $productCategoryQueryContainer;

    /**
     * @var \Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToProductInterface
     */
    protected $productFacade;

    /**
     * @var \Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToCategoryInterface
     */
    protected $categoryFacade;

    /**
     * @var \Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToTouchInterface
     */
    protected $touchFacade;

    /**
     * @var \Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToCmsInterface
     */
    protected $cmsFacade;

    /**
     * @var \Propel\Runtime\Connection\ConnectionInterface
     */
    protected $connection;

    /**
     * @param \Spryker\Zed\Category\Persistence\CategoryQueryContainerInterface $categoryQueryContainer
     * @param \Spryker\Zed\ProductCategory\Persistence\ProductCategoryQueryContainerInterface $productCategoryQueryContainer
     * @param \Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToProductInterface $productFacade
     * @param \Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToCategoryInterface $categoryFacade
     * @param \Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToTouchInterface $touchFacade
     * @param \Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToCmsInterface $cmsFacade
     * @param \Propel\Runtime\Connection\ConnectionInterface $connection
     */
    public function __construct(
        CategoryQueryContainerInterface $categoryQueryContainer,
        ProductCategoryQueryContainerInterface $productCategoryQueryContainer,
        ProductCategoryToProductInterface $productFacade,
        ProductCategoryToCategoryInterface $categoryFacade,
        ProductCategoryToTouchInterface $touchFacade,
        ProductCategoryToCmsInterface $cmsFacade,
        ConnectionInterface $connection
    ) {
        $this->categoryQueryContainer = $categoryQueryContainer;
        $this->productCategoryQueryContainer = $productCategoryQueryContainer;
        $this->productFacade = $productFacade;
        $this->categoryFacade = $categoryFacade;
        $this->touchFacade = $touchFacade;
        $this->cmsFacade = $cmsFacade;
        $this->connection = $connection;
    }

    /**
     * @param string $sku
     * @param string $categoryName
     * @param \Generated\Shared\Transfer\LocaleTransfer $locale
     *
     * @return bool
     */
    public function hasProductCategoryMapping($sku, $categoryName, LocaleTransfer $locale)
    {
        $mappingQuery = $this->productCategoryQueryContainer
            ->queryLocalizedProductCategoryMappingBySkuAndCategoryName($sku, $categoryName, $locale);

        return $mappingQuery->count() > 0;
    }

    /**
     * @param string $sku
     * @param string $categoryName
     * @param \Generated\Shared\Transfer\LocaleTransfer $locale
     *
     * @throws \Spryker\Zed\ProductCategory\Business\Exception\ProductCategoryMappingExistsException
     * @throws \Spryker\Zed\ProductCategory\Business\Exception\MissingProductException
     * @throws \Spryker\Zed\ProductCategory\Business\Exception\MissingCategoryNodeException
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return int
     */
    public function createProductCategoryMapping($sku, $categoryName, LocaleTransfer $locale)
    {
        $this->checkMappingDoesNotExist($sku, $categoryName, $locale);

        $idProductAbstract = $this->productFacade->getProductAbstractIdBySku($sku);
        $idCategory = $this->categoryFacade->getCategoryIdentifier($categoryName, $locale);

        $mappingEntity = new SpyProductCategory();
        $mappingEntity
            ->setFkProductAbstract($idProductAbstract)
            ->setFkCategory($idCategory);

        $mappingEntity->save();

        return $mappingEntity->getPrimaryKey();
    }

    /**
     * @param string $sku
     * @param string $categoryName
     * @param \Generated\Shared\Transfer\LocaleTransfer $locale
     *
     * @throws \Spryker\Zed\ProductCategory\Business\Exception\ProductCategoryMappingExistsException
     *
     * @return void
     */
    protected function checkMappingDoesNotExist($sku, $categoryName, LocaleTransfer $locale)
    {
        if ($this->hasProductCategoryMapping($sku, $categoryName, $locale)) {
            throw new ProductCategoryMappingExistsException(
                sprintf(
                    'Tried to create a product category mapping that already exists: Product: %s, Category: %s, Locale: %s',
                    $sku,
                    $categoryName,
                    $locale->getLocaleName()
                )
            );
        }
    }

    /**
     * @param int $idCategory
     * @param \Generated\Shared\Transfer\LocaleTransfer $locale
     *
     * @return \Orm\Zed\ProductCategory\Persistence\SpyProductCategory[]
     */
    public function getProductsByCategory($idCategory, LocaleTransfer $locale)
    {
        return $this->productCategoryQueryContainer
            ->queryProductsByCategoryId($idCategory, $locale)
            ->orderByFkProductAbstract()
            ->find();
    }

    /**
     * @param \Generated\Shared\Transfer\ProductAbstractTransfer $productAbstractTransfer
     *
     * @return \Orm\Zed\ProductCategory\Persistence\SpyProductCategory[]
     */
    public function getCategoriesByProductAbstract(ProductAbstractTransfer $productAbstractTransfer)
    {
        return $this->productCategoryQueryContainer
            ->queryLocalizedProductCategoryMappingByIdProduct($productAbstractTransfer->getIdProductAbstract())
            ->find();
    }

    /**
     * @param int $idCategory
     * @param int $idProductAbstract
     *
     * @return \Orm\Zed\ProductCategory\Persistence\SpyProductCategoryQuery
     */
    public function getProductCategoryMappingById($idCategory, $idProductAbstract)
    {
        return $this->productCategoryQueryContainer
            ->queryProductCategoryMappingByIds($idCategory, $idProductAbstract);
    }

    /**
     * @param int $idCategory
     * @param array $productIdsToUnAssign
     *
     * @return void
     */
    public function removeProductCategoryMappings($idCategory, array $productIdsToUnAssign)
    {
        foreach ($productIdsToUnAssign as $idProduct) {
            $mapping = $this->getProductCategoryMappingById($idCategory, $idProduct)
                ->findOne();

            if ($mapping === null) {
                continue;
            }

            $mapping->delete();

            //yes, Active is correct, it should update touch items, not mark them to delete
            //it's just a change to the mappings and not an actual product abstract
            $this->touchProductAbstractActive($idProduct);
        }
    }

    /**
     * @param int $idCategory
     * @param array $productIdsToAssign
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return void
     */
    public function createProductCategoryMappings($idCategory, array $productIdsToAssign)
    {
        foreach ($productIdsToAssign as $idProduct) {
            $mapping = $this->getProductCategoryMappingById($idCategory, $idProduct)
                ->findOneOrCreate();

            if ($mapping === null) {
                continue;
            }

            $mapping->setFkCategory($idCategory);
            $mapping->setFkProductAbstract($idProduct);
            $mapping->save();

            $this->touchProductAbstractActive($idProduct);
        }
    }

    /**
     * @param int $idCategory
     * @param array $productOrderList
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return void
     */
    public function updateProductMappingsOrder($idCategory, array $productOrderList)
    {
        foreach ($productOrderList as $idProduct => $order) {
            $mapping = $this->getProductCategoryMappingById($idCategory, $idProduct)
                ->findOne();

            if ($mapping === null) {
                continue;
            }

            $mapping->setFkCategory($idCategory);
            $mapping->setFkProductAbstract($idProduct);
            $mapping->setProductOrder($order);
            $mapping->save();

            $this->touchProductAbstractActive($idProduct);
        }
    }

    /**
     * @param int $idCategory
     * @param array $productPreConfigList
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return void
     */
    public function updateProductMappingsPreConfig($idCategory, array $productPreConfigList)
    {
        foreach ($productPreConfigList as $idProduct => $idPreconfigProduct) {
            $idPreconfigProduct = (int) $idPreconfigProduct;
            $mapping = $this->getProductCategoryMappingById($idCategory, $idProduct)
                ->findOne();

            if ($mapping === null) {
                continue;
            }

            $idPreconfigProduct = $idPreconfigProduct <= 0 ? null : $idPreconfigProduct;
            $mapping->setFkCategory($idCategory);
            $mapping->setFkProductAbstract($idProduct);
            $mapping->setFkPreconfigProduct($idPreconfigProduct);
            $mapping->save();

            $this->touchProductAbstractActive($idProduct);
        }
    }

    /**
     * @deprecated Use moveCategoryChildren() and CategoryFacade::deleteNode() instead
     *
     * @param \Generated\Shared\Transfer\NodeTransfer $sourceNodeTransfer
     * @param \Generated\Shared\Transfer\NodeTransfer $destinationNodeTransfer
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return void
     */
    public function moveCategoryChildrenAndDeleteNode(NodeTransfer $sourceNodeTransfer, NodeTransfer $destinationNodeTransfer, LocaleTransfer $localeTransfer)
    {
        trigger_error('Deprecated, Use moveCategoryChildren() and CategoryFacade::deleteNode() instead', E_USER_DEPRECATED);

        $this->moveCategoryChildren($sourceNodeTransfer, $destinationNodeTransfer, $localeTransfer);
        $this->categoryFacade->deleteNode($sourceNodeTransfer->getIdCategoryNode(), $localeTransfer, false);
    }

    /**
     * @param \Generated\Shared\Transfer\NodeTransfer $sourceNodeTransfer
     * @param \Generated\Shared\Transfer\NodeTransfer $destinationNodeTransfer
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return void
     */
    public function moveCategoryChildren(NodeTransfer $sourceNodeTransfer, NodeTransfer $destinationNodeTransfer, LocaleTransfer $localeTransfer)
    {
        $this->connection->beginTransaction();

        $this->moveCategoryNodes($sourceNodeTransfer, $destinationNodeTransfer, $localeTransfer);
        $this->removeExtraParents($sourceNodeTransfer->getFkCategory(), $localeTransfer);

        $this->connection->commit();
    }

    /**
     * @param \Generated\Shared\Transfer\NodeTransfer $sourceNodeTransfer
     * @param \Generated\Shared\Transfer\NodeTransfer $destinationNodeTransfer
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return void
     */
    protected function moveCategoryNodes(NodeTransfer $sourceNodeTransfer, NodeTransfer $destinationNodeTransfer, LocaleTransfer $localeTransfer)
    {
        $categoryNodeCollection = $this->categoryQueryContainer
            ->queryFirstLevelChildren($sourceNodeTransfer->getIdCategoryNode())
            ->find();

        foreach ($categoryNodeCollection as $categoryNode) {
            $nodeTransfer = new NodeTransfer();
            $nodeTransfer->fromArray($categoryNode->toArray());
            $nodeTransfer->setFkParentCategoryNode($destinationNodeTransfer->getIdCategoryNode());

            $this->categoryFacade->updateCategoryNode($nodeTransfer, $localeTransfer);
        }
    }

    /**
     * @param int $idCategory
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return void
     */
    protected function removeExtraParents($idCategory, LocaleTransfer $localeTransfer)
    {
        $extraParents = $this->categoryQueryContainer
            ->queryNotMainNodesByCategoryId($idCategory)
            ->find();

        foreach ($extraParents as $parent) {
            $this->categoryFacade->deleteNode($parent->getIdCategoryNode(), $localeTransfer);
        }
    }

    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     * @param \Generated\Shared\Transfer\NodeTransfer $categoryNodeTransfer
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return int
     */
    public function addCategory(CategoryTransfer $categoryTransfer, NodeTransfer $categoryNodeTransfer, LocaleTransfer $localeTransfer)
    {
        $this->connection->beginTransaction();

        $categoryTransfer->setIsActive(true);
        $categoryTransfer->setIsInMenu(true);
        $categoryTransfer->setIsClickable(true);

        $idCategory = $this->categoryFacade->createCategory($categoryTransfer, $localeTransfer);

        $categoryNodeTransfer->setFkCategory($idCategory);
        $categoryNodeTransfer->setIsMain(true);

        $this->categoryFacade->createCategoryNode($categoryNodeTransfer, $localeTransfer);

        $this->connection->commit();

        return $idCategory;
    }

    /**
     * @param int $idCategoryNode
     * @param int $fkParentCategoryNode
     * @param bool $deleteChildren
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return void
     */
    public function deleteCategory($idCategoryNode, $fkParentCategoryNode, $deleteChildren, LocaleTransfer $localeTransfer)
    {
        $this->connection->beginTransaction();

        if ($deleteChildren) {
            $this->deleteCategoryRecursive($idCategoryNode, $localeTransfer);
        } else {
            $sourceEntity = $this->categoryFacade->getNodeById($idCategoryNode);

            $destinationEntity = $this->categoryFacade->getNodeById($fkParentCategoryNode);

            $sourceNodeTransfer = (new NodeTransfer())
                ->fromArray($sourceEntity->toArray());

            $destinationNodeTransfer = (new NodeTransfer())
                ->fromArray($destinationEntity->toArray());

            $this->moveCategoryChildren($sourceNodeTransfer, $destinationNodeTransfer, $localeTransfer);
            $this->deleteCategoryRecursive($idCategoryNode, $localeTransfer);
        }

        $this->connection->commit();
    }

    /**
     * @param int $idCategory
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return void
     */
    public function deleteCategoryRecursive($idCategory, LocaleTransfer $localeTransfer)
    {
        $this->connection->beginTransaction();

        $this->removeMappings($idCategory);

        $categoryNodes = $this->categoryQueryContainer
            ->queryAllNodesByCategoryId($idCategory)
            ->find();

        foreach ($categoryNodes as $node) {
            $this->cmsFacade->updateBlocksAssignedToDeletedCategoryNode($node->getIdCategoryNode()); //TODO: https://spryker.atlassian.net/browse/CD-540

            $children = $this->categoryQueryContainer
                ->queryFirstLevelChildren($node->getIdCategoryNode())
                ->find();

            foreach ($children as $child) {
                $this->deleteCategoryRecursive($child->getFkCategory(), $localeTransfer);
            }

            $nodeExists = $this->categoryQueryContainer
                ->queryNodeById($node->getIdCategoryNode())
                ->count() > 0;

            if ($nodeExists) {
                $this->categoryFacade->deleteNode($node->getIdCategoryNode(), $localeTransfer, true);
            }
        }

        $this->categoryFacade->deleteCategory($idCategory);

        $this->connection->commit();
    }

    /**
     * @param int $idCategory
     *
     * @return void
     */
    protected function removeMappings($idCategory)
    {
        $assignedProducts = $this->productCategoryQueryContainer
            ->queryProductCategoryMappingsByCategoryId($idCategory)
            ->find();

        $productIdsToUnAssign = [];
        foreach ($assignedProducts as $mapping) {
            $productIdsToUnAssign[] = $mapping->getFkProductAbstract();
        }
        $this->removeProductCategoryMappings($idCategory, $productIdsToUnAssign);
    }

    /**
     * @param int $idProductAbstract
     *
     * @return void
     */
    protected function touchProductAbstractActive($idProductAbstract)
    {
        $this->touchFacade->touchActive(ProductCategoryConstants::RESOURCE_TYPE_PRODUCT_ABSTRACT, $idProductAbstract);
    }

    /**
     * @param int $idProductAbstract
     *
     * @return void
     */
    protected function touchProductAbstractDeleted($idProductAbstract)
    {
        $this->touchFacade->touchDeleted(ProductCategoryConstants::RESOURCE_TYPE_PRODUCT_ABSTRACT, $idProductAbstract);
    }

}
