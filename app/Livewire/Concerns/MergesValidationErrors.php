<?php

namespace App\Livewire\Concerns;

use Illuminate\Validation\ValidationException;

trait MergesValidationErrors
{
    protected function mergeValidationErrors(ValidationException $e): void
    {
        foreach ($e->errors() as $field => $messages) {
            foreach ($messages as $message) {
                $this->addError($field, $message);
            }
        }
    }
}
