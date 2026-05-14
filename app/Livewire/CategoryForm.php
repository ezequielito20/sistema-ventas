<?php

namespace App\Livewire;

use App\Livewire\Concerns\MergesValidationErrors;
use App\Models\Category;
use App\Services\CategoryService;
use App\Services\PlanEntitlementService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class CategoryForm extends Component
{
    use MergesValidationErrors;

    public ?int $categoryId = null;

    public string $name = '';

    public string $description = '';

    public function mount(?int $categoryId = null): void
    {
        $this->categoryId = $categoryId;

        if ($this->categoryId !== null) {
            Gate::authorize('categories.edit');

            $category = Category::query()
                ->where('company_id', Auth::user()->company_id)
                ->where('id', $this->categoryId)
                ->firstOrFail();

            $this->name = $category->name;
            $this->description = (string) ($category->description ?? '');

            return;
        }

        Gate::authorize('categories.create');
    }

    protected function rules(): array
    {
        $service = app(CategoryService::class);
        $companyId = (int) Auth::user()->company_id;

        if ($this->categoryId === null) {
            return $service->rulesForCreate($companyId);
        }

        $category = Category::query()
            ->where('company_id', $companyId)
            ->where('id', $this->categoryId)
            ->firstOrFail();

        return $service->rulesForUpdate($category, $companyId);
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nombre',
            'description' => 'descripción',
        ];
    }

    protected function messages(): array
    {
        return app(CategoryService::class)->validationMessages();
    }

    public function saveAndBack(CategoryService $categoryService)
    {
        return $this->persist($categoryService, false);
    }

    public function saveAndCreateAnother(CategoryService $categoryService)
    {
        return $this->persist($categoryService, true);
    }

    protected function persist(CategoryService $categoryService, bool $createAnother)
    {
        if ($this->categoryId !== null) {
            Gate::authorize('categories.edit');
        } else {
            Gate::authorize('categories.create');
        }

        $validated = $this->validate();
        $companyId = (int) Auth::user()->company_id;

        try {
            if ($this->categoryId === null) {
                app(PlanEntitlementService::class)->assertCanCreate(Auth::user(), 'categories');
                $categoryService->createCategory($companyId, $validated);

                session()->flash(
                    'message',
                    $createAnother
                        ? 'Categoría creada correctamente. Puedes registrar otra desde este formulario.'
                        : 'Categoría creada correctamente'
                );
                session()->flash('icons', 'success');

                if ($createAnother) {
                    $this->reset(['name', 'description']);

                    return $this->redirect(route('admin.categories.create'));
                }
            } else {
                $category = Category::query()
                    ->where('company_id', $companyId)
                    ->where('id', $this->categoryId)
                    ->firstOrFail();

                $categoryService->updateCategory($category, $companyId, $validated);

                session()->flash('message', 'Categoría actualizada correctamente');
                session()->flash('icons', 'success');
            }

            return $this->redirect(route('admin.categories.index'));
        } catch (ValidationException $e) {
            $this->mergeValidationErrors($e);

            return null;
        } catch (\Throwable $e) {
            $this->addError('name', 'Error al guardar la categoría: '.$e->getMessage());

            return null;
        }
    }

    public function render(): View
    {
        $isEdit = $this->categoryId !== null;

        return view('livewire.category-form', [
            'isEdit' => $isEdit,
            'headingTitle' => $isEdit ? 'Editar categoría' : 'Crear categoría',
            'headingSubtitle' => $isEdit
                ? 'Modifica el nombre y la descripción de la categoría.'
                : 'Organiza tus productos con categorías claras y únicas en tu empresa.',
        ]);
    }
}
