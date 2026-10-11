<?php

namespace App\Observers;

use App\Models\BitacoraCambio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Observer genérico de auditoría pasiva (PA3: ISO 25010 - Trazabilidad).
 * Registra en bitacora_cambios: usuario, tabla, acción, fotos del estado previo/nuevo y timestamp.
 */
class AuditObserver
{
    public function created(Model $model): void
    {
        $this->log($model, 'INSERT', null, $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $prev = [];
        $next = [];
        foreach ($model->getChanges() as $k => $v) {
            $prev[$k] = $model->getOriginal($k);
            $next[$k] = $v;
        }
        // Si no hubo cambios relevantes (timestamps), no auditar ruido
        if (empty($prev)) return;
        $this->log($model, 'UPDATE', $prev, $next);
    }

    public function deleted(Model $model): void
    {
        $this->log($model, 'DELETE', $model->getAttributes(), null);
    }

    private function log(Model $model, string $accion, ?array $prev, ?array $next): void
    {
        try {
            // Evitar auditoría recursiva de la propia bitácora
            if ($model instanceof BitacoraCambio) return;

            BitacoraCambio::create([
                'usuario_id'     => Auth::id() ?? $model->getAttribute('usuario_id') ?? null,
                'tabla_afectada' => $model->getTable(),
                'accion'         => $accion,
                'descripcion'    => class_basename($model) . " #{$model->getKey()} {$accion}",
                'datos_previos'  => $prev ? json_encode($prev, JSON_UNESCAPED_UNICODE) : null,
                'datos_nuevos'   => $next ? json_encode($next, JSON_UNESCAPED_UNICODE) : null,
                'fecha'          => now(),
            ]);
        } catch (\Throwable $e) {
            // Auditoría no debe romper la transacción de negocio
            report($e);
        }
    }
}
