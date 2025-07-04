<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditingTrait;
use App\Models\State;
use App\Models\Municipality;


class City extends Model
{
    protected $fillable = [
        'name',
        'state_id',
        'capital'
    ];

    protected static function rules(): array
    {
        return [
            'name'     => 'required|string|max:191', 
            'state_id' => 'required|integer|exists:states,id',
            'capital'  => 'required|boolean', 
        ];
    } 

    protected static function validationMessages(): array
    {
        return [
            'name.required'     => 'El nombre de la ciudad es obligatorio.',
            'name.string'       => 'Valor incorrecto para el campo "Nombre".',
            'name.max'          => 'El nombre de la ciudad no puede exceder :max caracteres.',
            'state_id.required' => 'El campo estado es obligatorio.',
            'state_id.integer'  => 'El campo estado tiene un valor incorrecto.',
            'state_id.exists'   => 'El campo estado seleccionado no es válido.',
            'capital.required'  => 'El campo "capital" es obligatorio.',
            'capital.boolean'   => 'Valor incorrecto para el campo "capital".',
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

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    public function hasChildRelations(): bool
    {
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
