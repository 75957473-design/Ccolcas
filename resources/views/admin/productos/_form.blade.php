@vite(['resources/js/admin.js'])

<div class="admin-card">
    <div class="admin-card__body">
        <div class="form-grid">
            <div class="form-group">
                <label for="codigo">Código</label>
                <input type="text" id="codigo" name="codigo" value="{{ old('codigo', $producto->codigo ?? '') }}" class="form-control" placeholder="DEMO-XXX-001">
            </div>
            <div class="form-group">
                <label for="nombre">Nombre *</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $producto->nombre ?? '') }}" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="categoria_id">Categoría</label>
                <select id="categoria_id" name="categoria_id" class="form-control">
                    <option value="">Sin categoría</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}" {{ (string) old('categoria_id', $producto->categoria_id ?? '') === (string) $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="unidad_medida_id">Unidad de medida</label>
                <select id="unidad_medida_id" name="unidad_medida_id" class="form-control">
                    <option value="">Sin unidad</option>
                    @foreach($unidades as $u)
                        <option value="{{ $u->id }}" {{ (string) old('unidad_medida_id', $producto->unidad_medida_id ?? '') === (string) $u->id ? 'selected' : '' }}>{{ $u->nombre }} ({{ $u->simbolo }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group form-group--full">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="3" class="form-control">{{ old('descripcion', $producto->descripcion ?? '') }}</textarea>
            </div>

            <div class="form-group">
                <label for="imagen">Imagen</label>
                <input type="file" id="imagen" name="imagen" class="form-control" accept="image/*">
                @if(!empty($producto?->imagen))
                    <span class="form-help">Actual: {{ $producto->imagen }}</span>
                @endif
            </div>
            <div class="form-group">
                <label for="stock_minimo">Stock mínimo *</label>
                <input type="number" id="stock_minimo" name="stock_minimo" value="{{ old('stock_minimo', $producto->stock_minimo ?? 5) }}" class="form-control" min="0" required>
            </div>

            <div class="form-group">
                <label for="costo_compra">Costo de compra (S/) *</label>
                <input type="number" step="0.01" id="costo_compra" name="costo_compra" value="{{ old('costo_compra', $producto->costo_compra ?? 0) }}" class="form-control" min="0" required>
            </div>
            <div class="form-group">
                <label for="costo_flete">Costo de flete (S/) *</label>
                <input type="number" step="0.01" id="costo_flete" name="costo_flete" value="{{ old('costo_flete', $producto->costo_flete ?? 0) }}" class="form-control" min="0" required>
            </div>
            <div class="form-group">
                <label for="margen_ganancia">Margen de ganancia (%) *</label>
                <input type="number" step="0.01" id="margen_ganancia" name="margen_ganancia" value="{{ old('margen_ganancia', $producto->margen_ganancia ?? 0) }}" class="form-control" min="0" required>
                <span class="form-help">Fórmula: precio = (compra + flete) × (1 + margen%)</span>
            </div>
            <div class="form-group">
                <label for="precio_venta">Precio de venta (S/)</label>
                <input type="number" step="0.01" id="precio_venta" name="precio_venta" value="{{ old('precio_venta', $producto->precio_venta ?? '') }}" class="form-control" min="0" placeholder="Se calcula automático">
                <span class="form-help">Si lo dejas vacío se calcula con la fórmula. Puedes editarlo manualmente.</span>
            </div>

            <div class="form-group">
                <label for="activo">Estado</label>
                <select id="activo" name="activo" class="form-control">
                    <option value="1" {{ old('activo', $producto->activo ?? 1) == 1 ? 'selected' : '' }}>Activo</option>
                    <option value="0" {{ old('activo', $producto->activo ?? 1) == 0 ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
        </div>

        <div style="margin-top:20px; display:flex; gap:10px;">
            <button type="submit" class="btn btn--primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
            <a href="{{ route('admin.productos.index') }}" class="btn btn--outline">Cancelar</a>
        </div>
    </div>
</div>
