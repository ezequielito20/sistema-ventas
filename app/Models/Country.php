<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\State;
use App\Models\Currency;
use App\Models\Persons\Etnia;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditingTrait;

class Country extends Model  
{
    protected $fillable = [
        'name',
        'iso_3366_1',
        'temp_country_id_mincyt',
    ];
    
    protected static function rules(): array
    {
        return [
            'name'       => 'required|string|min:2|max:191',
            'iso_3366_1' => 'string|max:10',
            'temp_country_id_mincyt' => 'nullable',
        ];
    }

    protected static function validationMessages(): array
    {
        return [
            'name.required'       => 'El campo Nombre del pais es obligatorio.',
            'name.string'         => 'Valor incorrecto para el campo "Nombre".',
            'name.min'            => 'El campo nombre del pais no puede ser inferior a :min caracteres.',
            'name.max'            => 'El campo nombre del pais no puede exceder :max caracteres.',
            'iso_3366_1.string'   => 'El campo ISO 3366_1 tiene un valor incorrecto.',
            'iso_3366_1.max'      => 'El campo ISO 3366_1 no puede exceder :max caracteres.',
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

    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }

    public function cities(): HasManyThrough
    {
        return $this->hasManyThrough(City::class, State::class);
    }

    public function etnias(): HasMany
    {
        return $this->hasMany(Etnia::class);
    }

    public function currencies(): HasMany
    {
        return $this->hasMany(Currency::class);
    }

    public function hasChildRelations(): bool
    {
        $relations = [
            'states',
            'cities',
            'etnias',
            'currencies'
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
}
