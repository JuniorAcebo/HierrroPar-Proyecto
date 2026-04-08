@extends('admin.layouts.app')

@section('title', 'Categorías')

@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="{{ asset('css/style_general.css') }}">
@endpush

@section('content')
@include('admin.layouts.partials.alert')

<div class="container-fluid px-4 py-4">

    {{-- ── ENCABEZADO ──────────────────────────────────────────────────── --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Categorías</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('panel') }}" class="text-decoration-none text-muted">Inicio</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Categorías</li>
                </ol>
            </nav>
        </div>

        @can('crear-categoria')
            <button type="button" class="btn-create" data-bs-toggle="modal" data-bs-target="#crearCategoriaModal">
                <i class="fas fa-plus"></i> Nueva Categoría
            </button>
        @endcan
    </div>

    {{-- ── CARD PRINCIPAL ──────────────────────────────────────────────── --}}
    <div class="card-clean">

        <div class="card-header-clean">
            <div class="card-header-title">
                <i class="fas fa-tags"></i> Lista de Categorías
            </div>
        </div>

        {{-- Barra de búsqueda --}}
        <div class="search-container">
            <form action="{{ route('productos.indexCategorias') }}" method="GET">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0" style="padding: 0.4rem 0.75rem;">
                                <i class="fas fa-search text-muted small"></i>
                            </span>
                            <input type="text" name="busqueda" id="inputBusqueda" class="form-control form-control-clean border-start-0 ps-0" placeholder="Buscar categoría..." value="{{ $busqueda ?? '' }}">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <label for="per_page" class="me-2 text-muted small">Mostrar:</label>
                            <select name="per_page" id="per_page" class="form-select form-select-sm w-auto" style="border-radius: 6px;">
                                @foreach([5, 10, 15, 20, 25] as $option)
                                <option value="{{ $option }}" {{ ($perPage ?? 10) == $option ? 'selected' : '' }}>
                                    {{ $option }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-5 text-end">
                        <a href="{{ route('productos.indexCategorias') }}" class="btn btn-outline-secondary btn-sm" style="border-radius: 6px;">
                            <i class="fas fa-undo me-1"></i> Mostrar Todo
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- ── TABLA ──────────────────────────────────────────────────────── --}}
        <div class="card-body p-0" id="table-container">
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>
                                <button class="sort-btn {{ $sort == 'nombre' ? 'active ' . $direction : '' }}" data-column="nombre">
                                    Nombre <i class="fas fa-sort sort-icon"></i>
                                </button>
                            </th>
                            <th>
                                <button class="sort-btn {{ $sort == 'descripcion' ? 'active ' . $direction : '' }}" data-column="descripcion">
                                    Descripción <i class="fas fa-sort sort-icon"></i>
                                </button>
                            </th>
                            <th class="text-center">
                                <button class="sort-btn {{ $sort == 'productos_count' ? 'active ' . $direction : '' }}" data-column="productos_count">
                                    Productos <i class="fas fa-sort sort-icon"></i>
                                </button>
                            </th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($categorias as $item)
                        <tr>
                            <td>
                                <div class="product-info">
                                    <div class="product-avatar">
                                        <i class="fas fa-tag small"></i>
                                    </div>
                                    <div class="fw-semibold">{{ $item->nombre }}</div>
                                </div>
                            </td>

                            <td>
                                <span class="text-muted small">{{ $item->descripcion ?? '—' }}</span>
                            </td>

                            <td class="text-center">
                                <span class="badge-pill {{ $item->productos_count > 0 ? 'badge-success' : 'badge-danger' }}">
                                    {{ $item->productos_count }}
                                </span>
                            </td>

                            <td>
                                <div class="btn-action-group">
                                    @can('editar-categoria')
                                        <button class="btn-icon-soft" title="Editar" onclick="abrirEditarModal(
                                                {{ $item->id }},
                                                @js($item->nombre),
                                                @js($item->descripcion ?? '')
                                            )">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                    @endcan

                                    @can('eliminar-categoria')
                                        <button class="btn-icon-soft delete" title="Eliminar" onclick="abrirEliminarModal(
                                                {{ $item->id }},
                                                @js($item->nombre),
                                                {{ $item->productos_count }}
                                            )">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted small">
                                <i class="fas fa-inbox me-2"></i>No se encontraron categorías.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                    <tfoot>
                        <tr class="table-totals">
                            <td colspan="2" class="text-end">
                                <span class="totals-label">RESUMEN GENERAL</span>
                            </td>
                            <td class="text-center">
                                <span class="totals-value">{{ $totalCategorias }}</span>
                                <span class="totals-subtext">Categorías</span>
                            </td>
                            <td class="text-center">
                                <span class="totals-value success">{{ $totalProductos }}</span>
                                <span class="totals-subtext">Productos Registrados</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="p-3 d-flex justify-content-between align-items-center border-top">
                <div class="text-muted extra-small">
                    @if($categorias->total() > 0)
                    Mostrando {{ $categorias->firstItem() }} - {{ $categorias->lastItem() }}
                    de {{ $categorias->total() }} registros
                    @else
                    Sin registros
                    @endif
                </div>
                <div>
                    {{ $categorias->appends([
                        'busqueda'  => $busqueda,
                        'per_page'  => $perPage,
                        'sort'      => $sort,
                        'direction' => $direction,
                    ])->links() }}
                </div>
            </div>
        </div>
    </div>
</div>


{{-- ════════════════════════════════════════════════════════════
     MODAL — CREAR
════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="crearCategoriaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-clean">

            <div class="modal-header modal-header-clean">
                <h5 class="modal-title fs-6">
                    <i class="fas fa-plus-circle me-2 text-muted"></i> Nueva Categoría
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('productos.storeCategoria') }}" method="POST">
                @csrf
                <input type="hidden" name="_form" value="crear">

                <div class="modal-body p-4">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">
                            Nombre <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nombre" id="crear_nombre" class="form-control form-control-clean @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}" maxlength="100">
                        @error('nombre')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label small fw-semibold text-muted">
                            Descripción <span class="text-muted fw-normal">(opcional)</span>
                        </label>

                        <textarea name="descripcion" class="form-control form-control-clean @error('descripcion') is-invalid @enderror" rows="3" maxlength="255">{{ old('descripcion') }}</textarea>

                        @error('descripcion')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                </div>

                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-4" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-outline-primary btn-sm px-4">
                        <i class="fas fa-save me-1"></i> Guardar
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>


{{-- ════════════════════════════════════════════════════════════
     MODAL — EDITAR (llenado por JS)
════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="editarCategoriaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-clean">

            <div class="modal-header modal-header-clean">
                <h5 class="modal-title fs-6">
                    <i class="fas fa-pen me-2 text-muted"></i> Editar Categoría
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formEditar" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="_form" value="editar">
                <input type="hidden" name="categoria_id" id="editar_id" value="{{ old('categoria_id') }}">

                <div class="modal-body p-4">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">
                            Nombre <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            id="editar_nombre"
                            name="nombre"
                            class="form-control form-control-clean @error('nombre') is-invalid @enderror"
                            value="{{ old('nombre') }}"
                            maxlength="100"
                        >

                        @error('nombre')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label small fw-semibold text-muted">
                            Descripción <span class="text-muted fw-normal">(opcional)</span>
                        </label>

                        <textarea
                            id="editar_descripcion"
                            name="descripcion"
                            class="form-control form-control-clean @error('descripcion') is-invalid @enderror"
                            rows="3"
                            maxlength="255"
                        >{{ old('descripcion') }}</textarea>

                        @error('descripcion')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                </div>

                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-4" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-outline-primary btn-sm px-4">
                        <i class="fas fa-save me-1"></i> Actualizar
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


{{-- ════════════════════════════════════════════════════════════
     MODAL — ELIMINAR (llenado por JS)
════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="eliminarCategoriaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-clean">

            <div class="modal-header modal-header-clean">
                <h5 class="modal-title fs-6">Confirmar eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 text-center">

                <div style="width:52px;height:52px;border-radius:50%;background:#f8d7da;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                    <i class="fas fa-trash text-danger fs-5"></i>
                </div>

                <h6 class="fw-semibold mb-1">¿Eliminar categoría?</h6>
                <p class="text-muted small mb-3">
                    Está a punto de eliminar <strong id="eliminar_nombre_display"></strong>.
                </p>

                <div id="eliminar_alerta_productos" class="alert alert-warning border-0 small py-2 mb-3" style="border-radius:8px; display:none;">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Esta categoría tiene <strong id="eliminar_productos_count"></strong> producto(s)
                    asociado(s) y <strong>no puede ser eliminada</strong>.
                </div>

                <div class="modal-footer border-0 p-4 pt-0 justify-content-center">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-4" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <form id="formEliminar" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" id="btn_confirmar_eliminar" class="btn btn-outline-danger btn-sm px-4">
                            <i class="fas fa-trash me-1"></i> Eliminar
                        </button>
                    </form>
                </div>


            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
    // ── Reabrir modal crear si hubo errores de validación ──────────────
    @if($errors -> any() && old('_form') === 'crear')
    document.addEventListener('DOMContentLoaded', function() {
        new bootstrap.Modal(document.getElementById('crearCategoriaModal')).show();
    });
    @endif

    @if($errors->any() && old('_form') === 'editar')
    document.addEventListener('DOMContentLoaded', function() {

        abrirEditarModal(
            "{{ old('categoria_id') }}",
            "{{ old('nombre') }}",
            "{{ old('descripcion') }}"
        );

    });
    @endif

    // ── Abrir modal EDITAR ─────────────────────────────────────────────
    function abrirEditarModal(id, nombre, descripcion) {

    document.getElementById('editar_id').value = id; // 👈 guardar el ID

    document.getElementById('formEditar').action =
        '{{ route("productos.updateCategoria", ":id") }}'.replace(':id', id);

    document.getElementById('editar_nombre').value = nombre;
    document.getElementById('editar_descripcion').value = descripcion;

    new bootstrap.Modal(document.getElementById('editarCategoriaModal')).show();
}

    // ── Abrir modal ELIMINAR ───────────────────────────────────────────
    function abrirEliminarModal(id, nombre, productosCount) {
        document.getElementById('eliminar_nombre_display').textContent = nombre;
        document.getElementById('formEliminar').action =
            '{{ route("productos.destroyCategoria", ":id") }}'.replace(':id', id);

        const alerta  = document.getElementById('eliminar_alerta_productos');
        const btnElim = document.getElementById('btn_confirmar_eliminar');

        if (productosCount > 0) {
            document.getElementById('eliminar_productos_count').textContent = productosCount;
            alerta.style.display = 'block';
            btnElim.disabled = true;   // 👈 agregar esto
        } else {
            alerta.style.display = 'none';
            btnElim.disabled = false;
        }

        new bootstrap.Modal(document.getElementById('eliminarCategoriaModal')).show();
    }
    // ── Búsqueda con debounce y ordenamiento dinámico ──────────────────
    let debounceTimer;

    function initializeEvents() {
        const searchInput = document.getElementById('inputBusqueda');
        const perPageSelect = document.getElementById('per_page');
        const sortButtons = document.querySelectorAll('.sort-btn');

        if (searchInput) {
            searchInput.focus();
            const len = searchInput.value.length;
            searchInput.setSelectionRange(len, len);
            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => fetchCategorias(), 300);
            });
        }

        if (perPageSelect) {
            perPageSelect.addEventListener('change', () => fetchCategorias());
        }

        sortButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const column = this.dataset.column;
                const currentUrl = new URL(window.location.href);
                let direction = 'asc';

                if (currentUrl.searchParams.get('sort') === column) {
                    direction = currentUrl.searchParams.get('direction') === 'asc' ? 'desc' : 'asc';
                }

                const params = new URLSearchParams(window.location.search);
                params.set('sort', column);
                params.set('direction', direction);
                fetchCategorias(`{{ route('productos.indexCategorias') }}?${params.toString()}`);
            });
        });
    }

    function fetchCategorias(url = null) {
        const searchInput = document.getElementById('inputBusqueda');
        const perPageSelect = document.getElementById('per_page');
        const container = document.getElementById('table-container');

        let fetchUrl = url;
        if (!fetchUrl) {
            const params = new URLSearchParams(window.location.search);
            if (searchInput) params.set('busqueda', searchInput.value);
            if (perPageSelect) params.set('per_page', perPageSelect.value);
            fetchUrl = `{{ route('productos.indexCategorias') }}?${params.toString()}`;
        }

        container.style.opacity = '0.6';

        fetch(fetchUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.text())
            .then(html => {
                const parser = new DOMParser();
                const newDoc = parser.parseFromString(html, 'text/html');
                const newCont = newDoc.getElementById('table-container');

                if (newCont) {
                    container.innerHTML = newCont.innerHTML;
                    container.style.opacity = '1';
                }

                window.history.pushState({}, '', fetchUrl);
                initializeEvents();
            })
            .catch(err => {
                console.error('Error:', err);
                container.style.opacity = '1';
            });
    }

    document.addEventListener('DOMContentLoaded', initializeEvents);
</script>
@endpush