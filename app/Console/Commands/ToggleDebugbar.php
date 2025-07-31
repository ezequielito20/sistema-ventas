<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ToggleDebugbar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debugbar:toggle {--enable : Enable debugbar} {--disable : Disable debugbar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Toggle Laravel Debugbar on/off';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $envFile = base_path('.env');
        
        if (!file_exists($envFile)) {
            $this->error('.env file not found!');
            return 1;
        }

        $envContent = file_get_contents($envFile);
        
        // Remove existing DEBUGBAR_ENABLED lines
        $envContent = preg_replace('/^DEBUGBAR_ENABLED=.*$/m', '', $envContent);
        
        if ($this->option('enable')) {
            $envContent .= "\nDEBUGBAR_ENABLED=true";
            $this->info('Debugbar enabled!');
        } elseif ($this->option('disable')) {
            $envContent .= "\nDEBUGBAR_ENABLED=false";
            $this->info('Debugbar disabled!');
        } else {
            // Toggle based on current state
            if (config('debugbar.enabled')) {
                $envContent .= "\nDEBUGBAR_ENABLED=false";
                $this->info('Debugbar disabled!');
            } else {
                $envContent .= "\nDEBUGBAR_ENABLED=true";
                $this->info('Debugbar enabled!');
            }
        }
        
        file_put_contents($envFile, $envContent);
        
        $this->call('config:clear');
        $this->info('Configuration cache cleared!');
        
        return 0;
    }
} 