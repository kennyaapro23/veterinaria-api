<?php

namespace App\Console\Commands;

use App\Jobs\EnviarRecordatoriosCitas;
use Illuminate\Console\Command;

class EnviarRecordatoriosCitasCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'citas:enviar-recordatorios';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía recordatorios de citas programadas para las próximas 24 horas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Enviando recordatorios de citas...');

        EnviarRecordatoriosCitas::dispatch();

        $this->info('Job de recordatorios despachado exitosamente.');
        
        return Command::SUCCESS;
    }
}
