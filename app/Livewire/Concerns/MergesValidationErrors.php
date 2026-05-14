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

        if (isset($e->errors()['plan'][0])) {
            $msg = (string) $e->errors()['plan'][0];
            $this->js('window.dispatchEvent(new CustomEvent("plan-limit-reached", { detail: { message: '.json_encode($msg).' } }))');
        }
    }
}
