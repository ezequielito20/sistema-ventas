<?php

namespace Tests\Unit;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CompanySlugTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: setting a reserved slug on Company model throws ValidationException.
     */
    public function test_reserved_slug_validation(): void
    {
        // Test with 'admin' - should throw validation exception
        $this->expectException(ValidationException::class);

        Company::factory()->create([
            'name' => 'Admin Co',
            'slug' => 'admin',
        ]);
    }

    /**
     * Test: setting 'login' as slug throws ValidationException.
     */
    public function test_reserved_slug_login_throws_exception(): void
    {
        $this->expectException(ValidationException::class);

        Company::factory()->create([
            'name' => 'Login Co',
            'slug' => 'login',
        ]);
    }

    /**
     * Test: setting a non-reserved slug works fine.
     */
    public function test_non_reserved_slug_works(): void
    {
        $company = Company::factory()->create([
            'name' => 'Valid Co',
            'slug' => 'my-valid-company',
        ]);

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'slug' => 'my-valid-company',
        ]);
    }

    /**
     * Test: auto-generated slug from name when slug is empty.
     */
    public function test_slug_auto_generates_from_name(): void
    {
        $company = Company::factory()->create([
            'name' => 'Mi Empresa Test',
            'slug' => null,
        ]);

        $this->assertNotNull($company->slug);
        $this->assertEquals('mi-empresa-test', $company->slug);
    }
}
