<?php

namespace App\Models;

use App\Models\State;
use App\Models\Parish;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Municipality extends Model
{

    protected $fillable = [
        'name',
        'state_id',
        'temp_municipality_id_mincyt',
    ];

    protected static function rules(): array
    {
        return [
            'name'     => 'required|string|min:2|max:191',
            'state_id' => 'required|integer|exists:states,id',
            'temp_municipality_id_mincyt' => 'nullable',
        ];
    }

    protected static function validationMessages(): array
    {
        return [
            'name.required'     => 'El campo nombre del municipio es obligatorio.',
            'name.string'       => 'Valor incorrecto para el campo "Nombre".',
            'name.min'          => 'El campo nombre del municipio no puede ser inferior a :min caracteres.',
            'name.max'          => 'El campo nombre del municipio no puede exceder :max caracteres.',
            'state_id.required' => 'El campo estado es obligatorio.',
            'state_id.integer'  => 'El campo estado tiene un valor incorrecto.',
            'state_id.exists'   => 'El campo estado seleccionado no es válido.',
        ];
    }

    protected static function validateData(array $data): void
    {
        $validator = \Illuminate\Support\Facades\Validator::make($data, self::rules(), self::validationMessages());

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
    }

    protected static function booted()
    {
        static::saving(function ($model) {
            try {
                self::validateData($model->getAttributes());
            } catch (\Illuminate\Validation\ValidationException $e) {
                $errorMessages = implode('<br>', $e->validator->errors()->all());

                \Filament\Notifications\Notification::make()
                    ->title('Error')
                    ->body("Hay errores en el formulario: <br>{$errorMessages}")
                    ->danger()
                    ->send();

                throw $e;
            }
        });
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function parishes(): HasMany
    {
        return $this->hasMany(Parish::class);
    }

    public function hasChildRelations(): bool
    {
        $relations = [
            'parishes'
        ];

        foreach ($relations as $relation) {
            $countField = "{$relation}_count";
            if ($this->$countField !== null) {
                if ($this->$countField > 0) {
                    return true;
                }
                continue;
            }

            if (method_exists($this, $relation) && $this->$relation()->exists()) {
                return true;
            }
        }

        return false;
    }

    public function transformAudit(array $data): array
    {
        $state = $this->state;
        
        if (!isset($data['new_values'])) {
            $data['new_values'] = [];
        }
    
        $data['new_values']['municipality_id'] = $this->id;
        $data['new_values']['municipality'] = $this->name;
        $data['new_values']['state_id'] = $state ? $state->id : 'unknown';
        $data['new_values']['state'] = $state ? $state->name : 'unknown';
        $data['new_values']['country_id'] = $state ? $state->country_id : 'unknown';
        $data['new_values']['country'] = $state ? $state->country->name : 'unknown';
      
        return $data;
    }
}
