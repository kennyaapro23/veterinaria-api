<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Mascota;

class GenerateMissingQRCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qr:generate-missing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera códigos QR para mascotas que no tienen';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mascotas = Mascota::whereNull('qr_code')->get();

        if ($mascotas->isEmpty()) {
            $this->info('✅ Todas las mascotas ya tienen QR');
            return 0;
        }

        $bar = $this->output->createProgressBar($mascotas->count());
        $bar->start();

        foreach ($mascotas as $mascota) {
            $mascota->regenerarQR();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ QR generados para {$mascotas->count()} mascotas");

        return 0;
    }
}
