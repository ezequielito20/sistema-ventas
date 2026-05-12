<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class EnsureSuperAdmin extends Command
{
    protected $signature = 'superadmin:ensure {--user=1 : ID del usuario que será el único super admin}';

    protected $description = 'Asegura que solo el usuario especificado sea super admin. Revoca el flag a todos los demás.';

    public function handle(): int
    {
        $userId = (int) $this->option('user');
        $user = User::find($userId);

        if (!$user) {
            $this->error("No se encontró el usuario con ID {$userId}.");
            return self::FAILURE;
        }

        // Revocar is_super_admin de todos los demás
        $revoked = User::where('id', '!=', $userId)
            ->where('is_super_admin', true)
            ->update(['is_super_admin' => false]);

        // Asignar al usuario elegido
        $user->update(['is_super_admin' => true]);

        $this->info("✓ Usuario #{$userId} ({$user->name}) establecido como único super admin.");
        if ($revoked > 0) {
            $this->warn("  Se revocó el flag super admin de {$revoked} otro(s) usuario(s).");
        }

        return self::SUCCESS;
    }
}
