<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MascotaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id ?? null,
            'nombre' => $this->nombre,
            'especie' => $this->especie,
            'raza' => $this->raza,
            'sexo' => $this->sexo,
            'fecha_nacimiento' => $this->fecha_nacimiento ? $this->fecha_nacimiento->toDateString() : null,
            'edad' => $this->edad ?? null,
            'color' => $this->color,
            'chip_id' => $this->chip_id,
            'foto_url' => $this->foto_url,
            'qr_code' => $this->qr_code,
            // Cliente simple inline (no depende de ClienteResource)
            'cliente' => $this->whenLoaded('cliente', function () {
                $c = $this->cliente;
                if (!$c) return null;
                return [
                    'id' => $c->id,
                    'nombre' => $c->nombre ?? null,
                    'email' => $c->email ?? null,
                    'user_id' => $c->user_id ?? null,
                ];
            }),
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
