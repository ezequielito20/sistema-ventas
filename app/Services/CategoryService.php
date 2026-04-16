<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;

class CategoryService
{
    /**
     * @return array{total:int, weekly:int, with_products:int, empty:int}
     */
    public function statistics(int $companyId): array
    {
        $base = Category::query()->where('company_id', $companyId);

        $total = (clone $base)->count();
        $weekly = (clone $base)->where('created_at', '>=', now()->subDays(7))->count();
        $withProducts = (clone $base)->whereHas('products')->count();
        $empty = (clone $base)->whereDoesntHave('products')->count();

        return [
            'total' => $total,
            'weekly' => $weekly,
            'with_products' => $withProducts,
            'empty' => $empty,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function rulesForCreate(int $companyId): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->where(fn ($query) => $query->where('company_id', $companyId)),
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s-]+$/',
            ],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function rulesForUpdate(Category $category, int $companyId): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($category->id)->where(fn ($query) => $query->where('company_id', $companyId)),
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s-]+$/',
            ],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationMessages(): array
    {
        return [
            'name.required' => 'El nombre de la categoría es obligatorio.',
            'name.max' => 'El nombre no puede exceder los 255 caracteres.',
            'name.unique' => 'Ya existe una categoría con este nombre en tu empresa.',
            'name.regex' => 'El nombre solo puede contener letras, números, espacios y guiones.',
            'description.max' => 'La descripción no puede exceder los 255 caracteres.',
        ];
    }

    public function createCategory(int $companyId, array $validated): Category
    {
        return Category::create([
            'name' => trim((string) $validated['name']),
            'description' => isset($validated['description']) && $validated['description'] !== ''
                ? trim((string) $validated['description'])
                : null,
            'company_id' => $companyId,
        ]);
    }

    public function updateCategory(Category $category, int $companyId, array $validated): void
    {
        if ((int) $category->company_id !== $companyId) {
            throw new \RuntimeException('No tiene permisos para editar esta categoría.');
        }

        $category->update([
            'name' => trim((string) $validated['name']),
            'description' => isset($validated['description']) && $validated['description'] !== ''
                ? trim((string) $validated['description'])
                : null,
        ]);
    }

    /**
     * @return array{can_delete: bool, reason: ?string}
     */
    public function deletionGuard(Category $category, int $companyId): array
    {
        if ((int) $category->company_id !== $companyId) {
            return [
                'can_delete' => false,
                'reason' => 'La categoría no pertenece a tu empresa',
            ];
        }

        $productsCount = $category->products()->count();
        if ($productsCount > 0) {
            return [
                'can_delete' => false,
                'reason' => 'Tiene '.$productsCount.' producto(s) asociado(s)',
            ];
        }

        return [
            'can_delete' => true,
            'reason' => null,
        ];
    }

    /**
     * @return array{id:int,name:string,deleted:bool,reason:?string}
     */
    public function deleteCategoryWithResult(Category $category, int $companyId): array
    {
        $guard = $this->deletionGuard($category, $companyId);

        if (! $guard['can_delete']) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'deleted' => false,
                'reason' => $guard['reason'],
            ];
        }

        $category->delete();

        return [
            'id' => $category->id,
            'name' => $category->name,
            'deleted' => true,
            'reason' => null,
        ];
    }

    /**
     * @param  array<int>  $categoryIds
     * @return array<int, array{id:int,name:string,deleted:bool,reason:?string}>
     */
    public function bulkDeleteCategories(int $companyId, array $categoryIds): array
    {
        /** @var Collection<int, Category> $categories */
        $categories = Category::query()
            ->where('company_id', $companyId)
            ->whereIn('id', array_map('intval', $categoryIds))
            ->withCount('products')
            ->orderBy('name')
            ->get();

        $results = [];

        /** @var Category $category */
        foreach ($categories as $category) {
            $results[] = $this->deleteCategoryWithResult($category, $companyId);
        }

        return $results;
    }
}
