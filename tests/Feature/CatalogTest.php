<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: catalog index returns 200 for a public company with a valid slug.
     */
    public function test_catalog_index_returns_200_for_public_company(): void
    {
        $company = Company::factory()->create([
            'name' => 'Test Company',
            'slug' => 'test-company',
            'catalog_is_public' => true,
        ]);

        $response = $this->get('/test-company');

        $response->assertStatus(200);
    }

    /**
     * Test: catalog index returns 404 when catalog_is_public is false.
     */
    public function test_catalog_index_returns_404_for_disabled_catalog(): void
    {
        $company = Company::factory()->create([
            'name' => 'Hidden Co',
            'slug' => 'hidden-co',
            'catalog_is_public' => false,
        ]);

        $response = $this->get('/hidden-co');

        $response->assertStatus(404);
    }

    /**
     * Test: product detail page returns 200 for a valid product with stock.
     */
    public function test_catalog_product_detail_returns_200(): void
    {
        $company = Company::factory()->create([
            'name' => 'Detail Co',
            'slug' => 'detail-co',
            'catalog_is_public' => true,
        ]);

        $category = Category::factory()->create([
            'name' => 'Test Category',
            'company_id' => $company->id,
        ]);

        $product = Product::factory()->create([
            'name' => 'Test Product',
            'stock' => 10,
            'category_id' => $category->id,
            'company_id' => $company->id,
        ]);

        $response = $this->get('/detail-co/producto/'.$product->id);

        $response->assertStatus(200);
    }

    /**
     * Test: product detail returns 404 when accessed via a different company slug.
     */
    public function test_product_detail_returns_404_for_wrong_company(): void
    {
        $companyA = Company::factory()->create([
            'name' => 'Company A',
            'slug' => 'company-a',
            'catalog_is_public' => true,
        ]);

        $companyB = Company::factory()->create([
            'name' => 'Company B',
            'slug' => 'company-b',
            'catalog_is_public' => true,
        ]);

        $category = Category::factory()->create([
            'name' => 'Cat A',
            'company_id' => $companyA->id,
        ]);

        $product = Product::factory()->create([
            'name' => 'Product A',
            'stock' => 10,
            'category_id' => $category->id,
            'company_id' => $companyA->id,
        ]);

        // Try to access product from company A using company B's slug
        $response = $this->get('/company-b/producto/'.$product->id);

        $response->assertStatus(404);
    }

    /**
     * Test: catalog index does not show out-of-stock products.
     */
    public function test_catalog_does_not_show_out_of_stock_products(): void
    {
        $company = Company::factory()->create([
            'name' => 'Stock Co',
            'slug' => 'stock-co',
            'catalog_is_public' => true,
        ]);

        $category = Category::factory()->create([
            'name' => 'Stock Cat',
            'company_id' => $company->id,
        ]);

        $productInStock = Product::factory()->create([
            'name' => 'In Stock Product',
            'stock' => 10,
            'category_id' => $category->id,
            'company_id' => $company->id,
        ]);

        $productOutOfStock = Product::factory()->create([
            'name' => 'Out of Stock Product',
            'stock' => 0,
            'category_id' => $category->id,
            'company_id' => $company->id,
        ]);

        $response = $this->get('/stock-co');

        $response->assertStatus(200);
        $response->assertSee($productInStock->name);
        $response->assertDontSee($productOutOfStock->name);

        $this->get('/stock-co/producto/'.$productOutOfStock->id)->assertStatus(404);
    }

    public function test_catalog_excludes_products_not_flagged_for_catalog(): void
    {
        $company = Company::factory()->create([
            'name' => 'Catalog Flag Co',
            'slug' => 'catalog-flag-co',
            'catalog_is_public' => true,
        ]);

        $category = Category::factory()->create([
            'name' => 'Flag Cat',
            'company_id' => $company->id,
        ]);

        $productShown = Product::factory()->create([
            'name' => 'Shown In Catalog',
            'stock' => 5,
            'include_in_catalog' => true,
            'category_id' => $category->id,
            'company_id' => $company->id,
        ]);

        $productHidden = Product::factory()->create([
            'name' => 'Hidden From Catalog',
            'stock' => 5,
            'include_in_catalog' => false,
            'category_id' => $category->id,
            'company_id' => $company->id,
        ]);

        $response = $this->get('/catalog-flag-co');

        $response->assertStatus(200);
        $response->assertSee($productShown->name);
        $response->assertDontSee($productHidden->name);

        $detail = $this->get('/catalog-flag-co/producto/'.$productHidden->id);
        $detail->assertStatus(404);
    }
}
