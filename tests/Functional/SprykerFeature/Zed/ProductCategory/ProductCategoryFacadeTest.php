<?php


namespace Functional\SprykerFeature\Zed\ProductCategory;

use Codeception\TestCase\Test;
use Generated\Zed\Ide\AutoCompletion;
use SprykerFeature\Zed\Category\Business\CategoryFacade;
use SprykerFeature\Zed\Category\Persistence\Propel\SpyCategoryAttributeQuery;
use SprykerFeature\Zed\Category\Persistence\Propel\SpyCategoryClosureTableQuery;
use SprykerFeature\Zed\Category\Persistence\Propel\SpyCategoryNodeQuery;
use SprykerFeature\Zed\Category\Persistence\Propel\SpyCategoryQuery;
use SprykerEngine\Zed\Kernel\Business\Factory;
use SprykerEngine\Zed\Kernel\Persistence\Factory as PersistenceFactory;
use SprykerEngine\Zed\Kernel\Locator;
use SprykerFeature\Zed\Product\Business\ProductFacade;
use SprykerFeature\Zed\Product\Persistence\ProductQueryContainer;
use SprykerFeature\Zed\ProductCategory\Business\ProductCategoryFacade;
use SprykerFeature\Zed\ProductCategory\Persistence\ProductCategoryQueryContainerInterface;
use Propel\Runtime\Propel;
use SprykerEngine\Zed\Locale\Business\LocaleFacade;
use SprykerFeature\Zed\Url\Persistence\Propel\SpyUrlQuery;

class ProductCategoryFacadeTest extends Test
{
    /**
     * @var ProductCategoryFacade
     */
    protected $productCategoryFacade;

    /**
     * @var ProductCategoryQueryContainerInterface
     */
    protected $productCategoryQueryContainer;

    /**
     * @var LocaleFacade
     */
    protected $localeFacade;

    /**
     * @var ProductFacade
     */
    protected $productFacade;

    /**
     * @var CategoryFacade
     */
    protected $categoryFacade;

    /**
     * @var AutoCompletion
     */
    protected $locator;

    protected function setUp()
    {
        parent::setUp();
        $this->locator = Locator::getInstance();

        $this->localeFacade = new LocaleFacade(new Factory('Locale'), $this->locator);
        $this->productFacade = new ProductFacade(new Factory('Product'), $this->locator);
        $this->categoryFacade = new CategoryFacade(new Factory('Category'), $this->locator);
        $this->productCategoryFacade = new ProductCategoryFacade(new Factory('ProductCategory'), $this->locator);
        $this->productCategoryQueryContainer = new ProductQueryContainer(
            new PersistenceFactory('ProductCategory'),
            $this->locator
        );
    }

    /**
     * @group ProductCategory
     */
    public function testCreateAttributeTypeCreatesAndReturnsId()
    {
        $this->eraseUrlsAndCategories();
        $abstractSku = 'AnAbstractTestProduct';
        $concreteSku = 'ATestProduct';
        $categoryName = 'ATestCategory';
        $localeName = 'ABCDE';

        $locale = $this->localeFacade->createLocale($localeName);
        $idAbstractProduct = $this->productFacade->createAbstractProduct($abstractSku);
        $this->productFacade->createConcreteProduct($concreteSku, $idAbstractProduct);

        $categoryTransfer = new \Generated\Shared\Transfer\CategoryCategoryTransfer();
        $categoryTransfer->setName($categoryName);
        $idCategory = $this->categoryFacade->createCategory($categoryTransfer, $locale);

        $categoryNodeTransfer = new \Generated\Shared\Transfer\CategoryCategoryNodeTransfer();
        $categoryNodeTransfer->setFkCategory($idCategory);
        $categoryNodeTransfer->setIsRoot(true);
        $this->categoryFacade->createCategoryNode($categoryNodeTransfer, $locale);
        $this->productCategoryFacade->createProductCategoryMapping($concreteSku, $categoryName, $locale);

        $this->assertTrue(
            $this->productCategoryFacade->hasProductCategoryMapping(
                $concreteSku,
                $categoryName,
                $locale
            )
        );
    }

    protected function eraseUrlsAndCategories()
    {
        Propel::getConnection()->query('SET foreign_key_checks = 0;');
        SpyUrlQuery::create()->deleteAll();
        SpyCategoryClosureTableQuery::create()->deleteAll();
        SpyCategoryAttributeQuery::create()->deleteAll();
        SpyCategoryNodeQuery::create()->deleteAll();
        SpyCategoryQuery::create()->deleteAll();
        Propel::getConnection()->query('SET foreign_key_checks = 1;');
    }
}
