<?php

namespace App\Services;

use App\Models\EstadoPedido;
use App\Models\Pedido;

/**
 * Máquina de estados del pedido.
 * Centraliza las transiciones permitidas y qué rol puede ejecutar cada una.
 */
class PedidoWorkflowService
{
    /**
     * Flujo lineal CCOLCAS.
     * null = estado inicial (sin pedido aún, se crea como Pendiente).
     */
    public const TRANSITIONS = [
        'Pendiente'    => ['Confirmado', 'Cancelado'],
        'Confirmado'   => ['En Almacén', 'Cancelado'],
        'En Almacén'   => ['Enviado', 'Cancelado'],
        'Enviado'      => ['Entregado', 'Cancelado'],
        'Entregado'    => [], // terminal
        'Cancelado'    => [], // terminal
    ];

    /**
     * Qué rol puede ejecutar la transición HACIA ese estado destino.
     * 'any_personal' = cualquier rol de personal (no Cliente).
     */
    public const ROLE_FOR_TARGET = [
        'Confirmado' => ['Vendedor', 'Administrador', 'Gerente', 'Subgerente'],
        'En Almacén' => ['Almacenero', 'Administrador', 'Gerente', 'Subgerente'],
        'Enviado'    => ['Almacenero', 'Vendedor', 'Administrador', 'Gerente', 'Subgerente'],
        'Entregado'  => ['Vendedor', 'Administrador', 'Gerente', 'Subgerente', 'Contador'],
        'Cancelado'  => ['Vendedor', 'Administrador', 'Gerente', 'Subgerente'],
    ];

    public static function estados(): array
    {
        return array_keys(self::TRANSITIONS);
    }

    public static function transicionesDesde(string $estadoActual): array
    {
        return self::TRANSITIONS[$estadoActual] ?? [];
    }

    public static function puedeTransitar(Pedido $pedido, string $estadoDestino, string $rolActual): bool
    {
        $actual = $pedido->estado->nombre ?? null;
        if (! $actual) return false;

        $permitidos = self::TRANSITIONS[$actual] ?? null;
        if ($permitidos === null || ! in_array($estadoDestino, $permitidos, true)) return false;

        $rolesPermitidos = self::ROLE_FOR_TARGET[$estadoDestino] ?? [];
        if (empty($rolesPermitidos)) return false;

        return in_array($rolActual, $rolesPermitidos, true);
    }

    public static function estadosPermitidosParaRol(Pedido $pedido, string $rolActual): array
    {
        $siguientes = self::transicionesDesde($pedido->estado->nombre ?? '');
        return array_values(array_filter($siguientes, fn ($dest) =>
            in_array($rolActual, self::ROLE_FOR_TARGET[$dest] ?? [], true)
        ));
    }

    public static function transitar(Pedido $pedido, string $estadoDestino, ?int $usuarioId = null): Pedido
    {
        $estado = EstadoPedido::where('nombre', $estadoDestino)->firstOrFail();
        $data = ['estado_id' => $estado->id];
        // Solo asignar vendedor_id al confirmar por primera vez (trazabilidad del que toma el pedido)
        if ($estadoDestino === 'Confirmado' && $usuarioId && empty($pedido->vendedor_id)) {
            $data['vendedor_id'] = $usuarioId;
        }
        $pedido->update($data);
        return $pedido->fresh(['estado', 'cliente', 'vendedor']);
    }
}
