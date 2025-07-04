<?php

namespace App\Models;

use App\Models\Country;
use App\Models\City;
use App\Models\Municipality;
use App\Models\Systems\Campus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditingTrait;

class State extends Model 
{

    protected $fillable = [
        'country_id',
        'name',
        'iso_3366_2',
        'category',
        'region',
        'zoom',
        'temp_state_id_mincyt',
    ];

    protected static function rules(): array
    {
        return [
            'name'       => 'required|string|min:2|max:191',
            'country_id' => 'required|integer|exists:countries,id',
            'iso_3366_2' => 'required|string|min:3|max:10',
            'temp_state_id_mincyt' => 'nullable',
        ];
    }

    protected static function validationMessages(): array
    {
        return [
            'name.required'       => 'El campo nombre del Estado es obligatorio.',
            'name.string'         => 'Valor incorrecto para el campo "Nombre".',
            'name.min'            => 'El campo nombre del Estado no puede ser inferior a :min caracteres.',
            'name.max'            => 'El campo nombre del Estado no puede exceder :max caracteres.',
            'country_id.required' => 'El campo estado es obligatorio.',
            'country_id.integer'  => 'El campo estado tiene un valor incorrecto.',
            'country_id.exists'   => 'El campo estado seleccionado no es válido.',
            'iso_3366_2.required' => 'El campo "ISO 3366-2" del Estado es obligatorio.',
            'iso_3366_2.string'   => 'El campo "ISO 3366-2" tiene un valor incorrecto.',
            'iso_3366_2.min'      => 'El campo "ISO 3366-2" no puede ser inferior a :min caracteres.',
            'iso_3366_2.max'      => 'El campo "ISO 3366-2" no puede exceder :max caracteres.',
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

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function municipalities(): HasMany
    {
        return $this->hasMany(Municipality::class);
    }

    public function campuses(): HasMany
    {
        return $this->hasMany(Campus::class);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function hasChildRelations(): bool
    {
        $relations = [
            'municipalities',
            'cities',
            'campuses'
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
        $country = $this->country;
        
        if (!isset($data['new_values'])) {
            $data['new_values'] = [];
        }
        $countryName = $country ? $country->name : 'unknown';
    
        $data['new_values']['state_id'] = $this->id;
        $data['new_values']['state'] = $this->name;
        $data['new_values']['country_id'] = $country ? $country->id : 'unknown';
        $data['new_values']['country'] = $countryName;
      
        return $data;
    }
}