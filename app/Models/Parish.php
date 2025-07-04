<?php

namespace App\Models;

use App\Models\Municipality;
use App\Models\Persons\Person;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditingTrait;
class Parish extends Model 
{

    protected $fillable = [
        'name',
        'municipality_id',
        'id_municipio',
        'temp_parish_id_mincyt',
    ];

    protected static function rules(): array
    {
        return [
            'name'            => 'required|string|min:2|max:191',
            'municipality_id' => 'required|integer|exists:municipalities,id',
            'temp_parish_id_mincyt' => 'nullable',
        ];
    }

    protected static function validationMessages(): array
    {
        return [
            'name.required'     => 'El campo nombre de la parroquia es obligatorio.',
            'name.string'       => 'Valor incorrecto para el campo "Nombre".',
            'name.min'          => 'El campo nombre de la parroquia no puede ser inferior a :min caracteres.',
            'name.max'          => 'El campo nombre de la parroquia no puede exceder :max caracteres.',
            'municipality_id.required' => 'El campo municipio es obligatorio.',
            'municipality_id.integer'  => 'El campo municipio tiene un valor incorrecto.',
            'municipality_id.exists'   => 'El campo municipio seleccionado no es válido.',
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

    public function persons_address_parish(): HasMany
    {
        return $this->hasMany(Person::class, 'address_parish_id');
    }

    public function persons_birth_parish(): HasMany
    {
        return $this->hasMany(Person::class, 'birth_parish_id');
    }

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    public function hasChildRelations(): bool
    {
        $relations = [
            'persons_address_parish',
            'persons_birth_parish'
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
        $municipality = $this->municipality;
        
        if (!isset($data['new_values'])) {
            $data['new_values'] = [];
        }
    
        $data['new_values']['parish_id'] = $this->id;
        $data['new_values']['parish'] = $this->name;
        $data['new_values']['municipality_id'] = $municipality ? $municipality->id : 'unknown';
        $data['new_values']['municipality'] = $municipality ? $municipality->name : 'unknown';
        $data['new_values']['state_id'] = $municipality ? $municipality->state->id : 'unknown';
        $data['new_values']['state'] = $municipality ? $municipality->state->name : 'unknown';
        $data['new_values']['country_id'] = $municipality ? $municipality->state->country_id : 'unknown';
        $data['new_values']['country'] = $municipality ? $municipality->state->country->name : 'unknown';
      
        return $data;
    }
}