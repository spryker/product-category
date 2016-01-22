<?php
/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\ProductCategory\Dependency\Facade;

use Spryker\Zed\Category\Business\CategoryFacade;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\NodeTransfer;

class ProductCategoryToCategoryBridge implements ProductCategoryToCategoryInterface
{

    /**
     * @var \Spryker\Zed\Category\Business\CategoryFacade
     */
    protected $categoryFacade;

    /**
     * ProductCategoryToCategoryBridge constructor.
     *
     * @param \Spryker\Zed\Category\Business\CategoryFacade $categoryFacade
     */
    public function __construct($categoryFacade)
    {
        $this->categoryFacade = $categoryFacade;
    }

    /**
     * @param string $categoryName
     * @param \Generated\Shared\Transfer\LocaleTransfer $locale
     *
     * @return bool
     */
    public function hasCategoryNode($categoryName, LocaleTransfer $locale)
    {
        return $this->categoryFacade->hasCategoryNode($categoryName, $locale);
    }

    /**
     * @param $categoryKey
     *
     * @return CategoryTransfer
     */
    public function getCategoryByKey($categoryKey)
    {
        return $this->categoryFacade->getCategoryByKey($categoryKey);
    }

    /**
     * @param string $categoryName
     * @param \Generated\Shared\Transfer\LocaleTransfer $locale
     *
     * @return int
     */
    public function getCategoryNodeIdentifier($categoryName, LocaleTransfer $locale)
    {
        return $this->categoryFacade->getCategoryNodeIdentifier($categoryName, $locale);
    }

    /**
     * @param string $categoryName
     * @param \Generated\Shared\Transfer\LocaleTransfer $locale
     *
     * @return int
     */
    public function getCategoryIdentifier($categoryName, LocaleTransfer $locale)
    {
        return $this->categoryFacade->getCategoryIdentifier($categoryName, $locale);
    }

    /**
     * @param int $idCategoryNode
     *
     * @return \Generated\Shared\Transfer\NodeTransfer
     */
    public function getNodeById($idCategoryNode)
    {
        return $this->categoryFacade->getNodeById($idCategoryNode);
    }

    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $category
     * @param \Generated\Shared\Transfer\LocaleTransfer $locale
     *
     * @return int
     */
    public function createCategory(CategoryTransfer $category, LocaleTransfer $locale)
    {
        return $this->categoryFacade->createCategory($category, $locale);
    }

    /**
     * @param \Generated\Shared\Transfer\NodeTransfer $categoryNode
     * @param \Generated\Shared\Transfer\LocaleTransfer $locale
     * @param bool $createUrlPath
     *
     * @return int
     */
    public function createCategoryNode(NodeTransfer $categoryNode, LocaleTransfer $locale, $createUrlPath = true)
    {
        return $this->categoryFacade->createCategoryNode($categoryNode, $locale, $createUrlPath);
    }

    /**
     * @param \Generated\Shared\Transfer\NodeTransfer $categoryNode
     * @param \Generated\Shared\Transfer\LocaleTransfer $locale
     *
     * @return void
     */
    public function updateCategoryNode(NodeTransfer $categoryNode, LocaleTransfer $locale)
    {
        $this->categoryFacade->updateCategoryNode($categoryNode, $locale);
    }

    /**
     * @param int $idNode
     * @param \Generated\Shared\Transfer\LocaleTransfer $locale
     * @param bool $deleteChildren
     *
     * @return int
     */
    public function deleteNode($idNode, LocaleTransfer $locale, $deleteChildren = false)
    {
        return $this->categoryFacade->deleteNode($idNode, $locale, $deleteChildren);
    }

    /**
     * @param int $idCategory
     *
     * @return void
     */
    public function deleteCategory($idCategory)
    {
        $this->categoryFacade->deleteCategory($idCategory);
    }

    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $category
     * @param \Generated\Shared\Transfer\LocaleTransfer $locale
     *
     * @return void
     */
    public function updateCategory(CategoryTransfer $category, LocaleTransfer $locale)
    {
        $this->categoryFacade->updateCategory($category, $locale);
    }

    /**
     * @param int $idCategory
     *
     * @return \Generated\Shared\Transfer\NodeTransfer[]
     */
    public function getNotMainNodesByIdCategory($idCategory)
    {
        return $this->categoryFacade->getNotMainNodesByIdCategory($idCategory);
    }

    /**
     * @param array $pathTokens
     *
     * @return string
     */
    public function generatePath(array $pathTokens)
    {
        return $this->categoryFacade->generatePath($pathTokens);
    }

}
