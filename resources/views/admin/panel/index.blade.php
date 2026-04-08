@extends('admin.layouts.app')

@section('title', 'Panel de Control — KPIs')

@push('css')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/style_panel.css') }}">
@endpush

@section('content')

    @if (session('success'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: '¡Excelente!',
                    text: "{{ session('success') }}",
                    icon: 'success',
                    confirmButtonText: 'Continuar',
                    confirmButtonColor: '#4f46e5',
                    background: '#fff',
                    iconColor: '#10b981'
                });
            });
        </script>
    @endif

    <div class="dash-wrapper">

        {{-- ENCABEZADO --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 fade-in-up">
            <div>
                <h1 class="h3 fw-bold text-dark mb-1">Panel de Control</h1>
                <p class="text-muted mb-0">
                    <i class="far fa-calendar me-1"></i>
                    {{ \Carbon\Carbon::now()->locale('es')->translatedFormat('l, d F Y') }}
                </p>
            </div>
            <div class="mt-3 mt-md-0 d-flex gap-2">
                <button type="button" class="btn btn-primary-soft px-4 py-2 rounded-3" data-bs-toggle="modal"
                    data-bs-target="#metricasModal">
                    <i class="fas fa-bolt me-2"></i>Métricas del Día
                </button>
            </div>
        </div>

        {{-- MÓDULO: VENTAS — KPI 1, 2, 3 --}}
        <div class="module-section fade-up delay-1">
            <div class="module-label">
                <span class="mod-badge" style="background:#f0fdf4;border-color:#d1fae5;color:#10b981;">
                    <i class="fas fa-chart-bar me-2"></i>Ventas
                </span>
                <div class="line"></div>
            </div>

            @php
                $k1 = $kpi_ventas['kpi1_flujo_caja'];
                $k2 = $kpi_ventas['kpi2_crecimiento_ventas'];
                $k3 = $kpi_ventas['kpi3_tasa_cancelacion'];
            @endphp

            {{-- Fila 1: KPI 2 | KPI 3 --}}
            <div class="grid-2" style="margin-bottom:1rem;">

                {{-- KPI 2: Crecimiento --}}
                <div class="card v" style="padding:0;overflow:hidden;">
                    <div style="height:3px;background:var(--ventas)"></div>
                    <div
                        style="display:flex;justify-content:space-between;align-items:flex-start;padding:1.1rem 1.25rem .9rem;border-bottom:1px solid var(--border);">
                        <div>
                            <div class="kpi-label">Crecimiento mensual</div>
                            <div class="kpi-value"
                                style="color:{{ $k2['meta_cumplida'] ? 'var(--ventas)' : '#f59e0b' }};margin-top:.25rem">
                                {{ $k2['porcentaje'] >= 0 ? '+' : '' }}{{ $k2['porcentaje'] }}%
                            </div>
                            <div style="font-size:.72rem;color:var(--muted);margin-top:.15rem">vs mes anterior</div>
                        </div>
                        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.5rem">
                            <div class="kpi-icon" style="background:rgba(16,185,129,.1);color:var(--ventas)">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            @if ($k2['meta_cumplida'])
                                <span class="badge-ok" style="font-size:.7rem"><i class="fas fa-arrow-up me-1"></i>Meta
                                    OK</span>
                            @else
                                <span class="badge-warn" style="font-size:.7rem"><i class="fas fa-arrow-right me-1"></i>En
                                    curso</span>
                            @endif
                        </div>
                    </div>
                    {{-- Barra progreso --}}
                    <div style="padding:.65rem 1.25rem;border-bottom:1px solid var(--border);">
                        <div class="prog-track">
                            <div class="prog-bar"
                                style="width:{{ $k2['progreso'] }}%;background:{{ $k2['meta_cumplida'] ? 'var(--ventas)' : '#f59e0b' }}">
                            </div>
                        </div>
                        <div
                            style="display:flex;justify-content:space-between;font-size:.7rem;color:var(--muted);margin-top:.35rem">
                            <span style="font-weight:500">Meta: ≥ {{ $k2['meta'] }}% mensual</span>
                        </div>
                    </div>
                    {{-- Stats en grid --}}
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem;padding:1rem 1.25rem;">
                        <div style="text-align:center;padding:.75rem;background:var(--surface2);border-radius:10px;">
                            <div
                                style="font-size:.62rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);margin-bottom:.3rem">
                                Este mes</div>
                            <div style="font-size:1.1rem;font-weight:700;color:var(--ventas)">Bs/
                                {{ number_format($k2['ventas_actual'], 0) }}</div>
                        </div>
                        <div style="text-align:center;padding:.75rem;background:var(--surface2);border-radius:10px;">
                            <div
                                style="font-size:.62rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);margin-bottom:.3rem">
                                Mes anterior</div>
                            <div style="font-size:1.1rem;font-weight:700;color:var(--text)">Bs/
                                {{ number_format($k2['ventas_anterior'], 0) }}</div>
                        </div>
                        <div style="text-align:center;padding:.75rem;background:var(--surface2);border-radius:10px;">
                            <div
                                style="font-size:.62rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);margin-bottom:.3rem">
                                Diferencia</div>
                            @php $diff = $k2['ventas_actual'] - $k2['ventas_anterior']; @endphp
                            <div
                                style="font-size:1.1rem;font-weight:700;color:{{ $diff >= 0 ? 'var(--ventas)' : '#ef4444' }}">
                                {{ $diff >= 0 ? '+' : '' }}Bs/ {{ number_format($diff, 0) }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- KPI 3: Cancelaciones --}}
                <div class="card v" style="padding:0;overflow:hidden;">
                    <div style="height:3px;background:var(--ventas)"></div>
                    <div
                        style="display:flex;justify-content:space-between;align-items:flex-start;padding:1.1rem 1.25rem .9rem;border-bottom:1px solid var(--border);">
                        <div>
                            <div class="kpi-label">Tasa de cancelaciones</div>
                            <div class="kpi-value"
                                style="color:{{ $k3['meta_cumplida'] ? 'var(--ventas)' : '#ef4444' }};margin-top:.25rem">
                                {{ $k3['tasa'] }}%
                            </div>
                            <div style="font-size:.72rem;color:var(--muted);margin-top:.15rem">del total de ventas
                                registradas</div>
                        </div>
                        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.5rem">
                            <div class="kpi-icon"
                                style="background:{{ $k3['meta_cumplida'] ? 'rgba(16,185,129,.1)' : 'rgba(239,68,68,.1)' }};color:{{ $k3['meta_cumplida'] ? 'var(--ventas)' : '#ef4444' }}">
                                <i class="fas fa-ban"></i>
                            </div>
                            @if ($k3['meta_cumplida'])
                                <span class="badge-ok" style="font-size:.7rem"><i
                                        class="fas fa-shield-alt me-1"></i>Controlado</span>
                            @else
                                <span class="badge-bad" style="font-size:.7rem"><i
                                        class="fas fa-exclamation-circle me-1"></i>Revisar</span>
                            @endif
                        </div>
                    </div>
                    {{-- Barra progreso --}}
                    <div style="padding:.65rem 1.25rem;border-bottom:1px solid var(--border);">
                        <div class="prog-track">
                            <div class="prog-bar"
                                style="width:{{ 100 - $k3['progreso'] }}%;background:{{ $k3['meta_cumplida'] ? 'var(--ventas)' : '#ef4444' }}">
                            </div>
                        </div>
                        <div
                            style="display:flex;justify-content:space-between;font-size:.7rem;color:var(--muted);margin-top:.35rem">
                            <span style="font-weight:500">Meta: ≤ {{ $k3['meta'] }}% canceladas</span>
                        </div>
                    </div>
                    {{-- Stats en grid --}}
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem;padding:1rem 1.25rem;">
                        <div style="text-align:center;padding:.75rem;background:var(--surface2);border-radius:10px;">
                            <div
                                style="font-size:.62rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);margin-bottom:.3rem">
                                Total</div>
                            <div style="font-size:1.1rem;font-weight:700;color:var(--text)">{{ $k3['total_ventas'] }}</div>
                        </div>
                        <div style="text-align:center;padding:.75rem;background:#fef2f2;border-radius:10px;">
                            <div
                                style="font-size:.62rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#ef4444;margin-bottom:.3rem">
                                Canceladas</div>
                            <div style="font-size:1.1rem;font-weight:700;color:#ef4444">{{ $k3['canceladas'] }}</div>
                        </div>
                        <div style="text-align:center;padding:.75rem;background:#f0fdf4;border-radius:10px;">
                            <div
                                style="font-size:.62rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--ventas);margin-bottom:.3rem">
                                Exitosas</div>
                            <div style="font-size:1.1rem;font-weight:700;color:var(--ventas)">
                                {{ $k3['total_ventas'] - $k3['canceladas'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Fila 2: KPI 1 + Gráfico (unificado, full width) --}}
            <div class="card v" style="padding:0;overflow:hidden;">
                <div style="height:3px;background:var(--ventas)"></div>
                <div style="display:flex;flex-wrap:wrap;align-items:stretch;">

                    {{-- Lado izquierdo: indicador KPI 1 --}}
                    <div
                        style="flex:0 1 280px;min-width:0;max-width:100%;padding:1.5rem 1.75rem;border-right:1px solid var(--border);border-bottom:1px solid var(--border);display:flex;flex-direction:column;justify-content:space-between;gap:1.25rem;">
                        <div>
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                                <div>
                                    <div class="kpi-label">Flujo de caja</div>
                                    <div class="kpi-value"
                                        style="color:{{ $k1['meta_cumplida'] ? 'var(--ventas)' : '#ef4444' }};margin-top:.25rem;font-size:2rem">
                                        Bs/ {{ number_format($k1['flujo_neto'], 0) }}
                                    </div>
                                    <div style="font-size:.78rem;color:var(--muted);margin-top:.2rem">flujo neto del mes
                                    </div>
                                </div>
                                <div class="kpi-icon"
                                    style="background:rgba(16,185,129,.1);color:var(--ventas);flex-shrink:0">
                                    <i class="fas fa-coins"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div style="margin-bottom:.75rem">
                                @if ($k1['meta_cumplida'])
                                    <span class="badge-ok" style="font-size:.78rem"><i
                                            class="fas fa-check-circle me-1"></i>Meta lograda</span>
                                @else
                                    <span class="badge-bad" style="font-size:.78rem"><i
                                            class="fas fa-times-circle me-1"></i>Pendiente</span>
                                @endif
                            </div>
                            <div class="prog-track">
                                <div class="prog-bar"
                                    style="width:{{ $k1['progreso'] }}%;background:{{ $k1['meta_cumplida'] ? 'var(--ventas)' : '#ef4444' }}">
                                </div>
                            </div>
                            <div
                                style="display:flex;justify-content:space-between;font-size:.72rem;color:var(--muted);margin-top:.4rem">
                                <span style="font-weight:500">Meta: Bs {{ number_format($k1['meta'], 0) }}/mes</span>
                                <span style="font-weight:500">{{ $k1['progreso'] }}%</span>
                            </div>
                            <div style="margin-top:.9rem;display:flex;flex-direction:column;gap:.4rem;">
                                <div style="display:flex;justify-content:space-between;font-size:.75rem;">
                                    <span style="color:var(--muted)">Ingresos</span>
                                    <span style="font-weight:600;color:var(--ventas)">Bs/
                                        {{ number_format($k1['ventas'], 0) }}</span>
                                </div>
                                <div style="display:flex;justify-content:space-between;font-size:.75rem;">
                                    <span style="color:var(--muted)">Egresos</span>
                                    <span style="font-weight:600;color:#ef4444">Bs/
                                        {{ number_format($k1['compras'], 0) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Lado derecho: gráfico --}}
                    <div style="flex:1;min-width:300px;width:100%;display:flex;flex-direction:column;">
                        <div style="padding:.9rem 1.25rem;border-bottom:1px solid var(--border);">
                            <div
                                style="font-size:.65rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--muted)">
                                Tendencia flujo de caja anual <span
                                    style="background:var(--surface2);border:1px solid var(--border);border-radius:6px;padding:.15rem .5rem;font-size:.62rem;margin-left:.4rem;">{{ $currentYear }}</span>
                            </div>
                        </div>
                        <div style="padding:1.25rem;flex:1;">
                            <div style="position:relative;height:220px;">
                                <canvas id="chartFlujoCaja"></canvas>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        {{-- MÓDULO: INVENTARIO — KPI 4, 5, 9 --}}
        <div class="module-section fade-up delay-2">
            <div class="module-label">
                <span class="mod-badge" style="background:#fffbeb;border-color:#fef3c7;color:#f59e0b;">
                    <i class="fas fa-boxes me-2"></i>Productos / Inventario
                </span>
                <div class="line"></div>
            </div>

            @php
                $k4 = $kpi_inventario['kpi4_top5_productos'];
                $k5 = $kpi_inventario['kpi5_stock_minimo'];
                $k9 = $kpi_inventario['kpi9_ajustes_inventario'];
            @endphp

            {{-- Fila 1: KPI 4 | KPI 5 — misma altura, KPI4 se estira --}}
            <div class="grid-2" style="margin-bottom:2rem;align-items:stretch;">

                {{-- KPI 4: Top 5  --}}
                <div class="card i" style="padding:0;overflow:hidden;display:flex;flex-direction:column;">
                    <div style="height:3px;background:var(--inventario)"></div>
                    <div
                        style="display:flex;justify-content:space-between;align-items:flex-start;padding:1.1rem 1.25rem .9rem;border-bottom:1px solid var(--border);">
                        <div>
                            <div class="kpi-label">Top 5 productos</div>
                            <div class="kpi-value" style="color:var(--inventario);margin-top:.25rem">Top 5</div>
                            <div style="font-size:.72rem;color:var(--muted);margin-top:.15rem">Unidades vendidas ·
                                {{ $currentYear }}</div>
                        </div>
                        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.5rem">
                            <div class="kpi-icon" style="background:rgba(245,158,11,.1);color:var(--inventario)">
                                <i class="fas fa-trophy"></i>
                            </div>
                            @if ($k4['meta_cumplida'])
                                <span class="badge-ok" style="font-size:.7rem"><i
                                        class="fas fa-check-circle me-1"></i>Excelente</span>
                            @else
                                <span class="badge-bad" style="font-size:.7rem"><i
                                        class="fas fa-exclamation-triangle me-1"></i>Stock bajo mínimo</span>
                            @endif
                        </div>
                    </div>

                    {{-- Barra de meta --}}

                    @php
                        $pctMeta =
                            $k4['total_con_minimo'] > 0
                                ? round(($k4['productos_ok'] / $k4['total_con_minimo']) * 100)
                                : 100;
                    @endphp
                    <div style="padding:.65rem 1.25rem;border-bottom:1px solid var(--border);">
                        <div class="prog-track">
                            <div class="prog-bar"
                                style="width:{{ $pctMeta }}%;background:{{ $k4['meta_cumplida'] ? 'var(--inventario)' : '#ef4444' }}">
                            </div>
                        </div>
                        <div
                            style="display:flex;justify-content:space-between;font-size:.7rem;color:var(--muted);margin-top:.35rem">
                            <span style="font-weight:500">Meta: Top 5 con stock ≥ mínimo definido</span>
                            <span
                                style="font-weight:600;color:{{ $k4['meta_cumplida'] ? 'var(--inventario)' : '#ef4444' }}">
                                {{ $k4['productos_ok'] }}/{{ $k4['total_con_minimo'] }} productos OK
                            </span>
                        </div>
                    </div>

                    {{-- flex:1 hace que este div llene todo el espacio restante --}}
                    <div
                        style="padding:1.25rem 1.25rem 1.75rem;flex:1;display:flex;flex-direction:column;justify-content:space-evenly;">
                        <div style="display:flex;flex-direction:column;gap:1rem;height:100%">
                            @foreach ($k4['productos'] as $i => $prod)
                                @php
                                    $maxVal = $k4['productos']->max('total_vendido');
                                    $pct = $maxVal > 0 ? ($prod->total_vendido / $maxVal) * 100 : 0;
                                    $opacities = ['1', '0.82', '0.64', '0.46', '0.3'];
                                    $op = $opacities[$i] ?? '0.3';
                                @endphp
                                <div style="display:flex;align-items:center;gap:.75rem;font-size:.78rem;">
                                    <span
                                        style="color:var(--muted);width:130px;flex-shrink:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $prod->nombre }}</span>
                                    <div
                                        style="flex:1;height:22px;background:var(--surface2);border-radius:8px;overflow:hidden">
                                        <div
                                            style="width:{{ round($pct) }}%;height:100%;background:var(--inventario);opacity:{{ $op }};border-radius:8px;transition:width .6s ease">
                                        </div>
                                    </div>
                                    <span
                                        style="font-weight:600;color:var(--inventario);flex-shrink:0;width:48px;text-align:right">{{ number_format($prod->total_vendido, 0) }}u</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- KPI 5: Stock en riesgo --}}
                <div class="card i" style="padding:0;overflow:hidden;">
                    <div style="height:3px;background:var(--inventario)"></div>
                    <div
                        style="display:flex;justify-content:space-between;align-items:flex-start;padding:1.1rem 1.25rem .9rem;border-bottom:1px solid var(--border);">
                        <div>
                            <div class="kpi-label">Stock en riesgo</div>
                            <div class="kpi-value"
                                style="color:{{ $k5['meta_cumplida'] ? 'var(--inventario)' : '#ef4444' }};margin-top:.25rem">
                                {{ $k5['porcentaje'] }}%
                            </div>
                            <div style="font-size:.72rem;color:var(--muted);margin-top:.15rem">
                                {{ $k5['cantidad'] }} de {{ $k5['total_activos'] }} productos
                            </div>
                        </div>
                        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.5rem">
                            <div class="kpi-icon"
                                style="background:{{ $k5['meta_cumplida'] ? 'rgba(245,158,11,.1)' : 'rgba(239,68,68,.1)' }};color:{{ $k5['meta_cumplida'] ? 'var(--inventario)' : '#ef4444' }}">
                                <i class="fas fa-box-open"></i>
                            </div>
                            @if ($k5['meta_cumplida'])
                                <span class="badge-ok" style="font-size:.7rem"><i
                                        class="fas fa-check me-1"></i>Inventario OK</span>
                            @else
                                <span class="badge-bad" style="font-size:.7rem"><i
                                        class="fas fa-exclamation-triangle me-1"></i>Crítico</span>
                            @endif
                        </div>
                    </div>
                    <div style="padding:.75rem 1.25rem;border-bottom:1px solid var(--border)">
                        <div class="prog-track">
                            <div class="prog-bar"
                                style="width:{{ 100 - min(($k5['porcentaje'] / $k5['meta']) * 100, 100) }}%;background:{{ $k5['meta_cumplida'] ? 'var(--inventario)' : '#ef4444' }}">
                            </div>
                        </div>
                        <div
                            style="display:flex;justify-content:space-between;font-size:.7rem;color:var(--muted);margin-top:.35rem">
                            <span style="font-weight:500">Meta: ≤ {{ $k5['meta'] }}% de productos en estado
                                crítico</span>
                            <span style="font-weight:500">{{ 100 - min(round(($k5['porcentaje'] / $k5['meta']) * 100), 100) }}%
                                dentro del límite</span>
                        </div>
                    </div>
                    <div style="padding:.75rem 1.25rem 0">
                        <div
                            style="font-size:.65rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--muted);margin-bottom:.6rem;display:flex;align-items:center;gap:.5rem">
                            Alertas críticas
                            @if (!$k5['productos']->isEmpty())
                                <span
                                    style="background:#fef2f2;border:1px solid #fee2e2;color:#ef4444;padding:.15rem .55rem;border-radius:99px;font-size:.62rem">
                                    {{ $k5['cantidad'] }} items
                                </span>
                            @endif
                        </div>
                    </div>
                    @if ($k5['productos']->isEmpty())
                        <div class="empty" style="padding:1.25rem"><i class="fas fa-check-circle"></i>Inventario
                            saludable</div>
                    @else
                        <div style="overflow-x:auto">
                            <table class="stock-table">
                                <thead>
                                    <tr>
                                        <th style="padding-left:1.25rem">Producto</th>
                                        <th>Stock actual</th>
                                        <th>Mínimo</th>
                                        <th style="padding-right:1.25rem">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($k5['productos'] as $p)
                                        <tr>
                                            <td
                                                style="padding-left:1.25rem;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                                {{ $p->nombre }}</td>
                                            <td style="font-weight:600;color:#ef4444">
                                                {{ number_format($p->stock_actual, 0) }}</td>
                                            <td style="color:var(--muted)">{{ $p->stock_minimo }}</td>
                                            <td style="padding-right:1.25rem">
                                                @if ($p->stock_actual <= $p->stock_minimo / 2)
                                                    <span class="pill pill-red">Crítico</span>
                                                @else
                                                    <span class="pill pill-yellow">Revisar</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Fila 2: KPI 9 full width --}}
            <div class="card i" style="padding:0;overflow:hidden;">
                <div style="height:3px;background:var(--inventario)"></div>
                <div style="display:flex;flex-wrap:wrap;align-items:stretch;">

                    {{-- Lado izquierdo: indicador --}}
                    <div
                        style="flex:0 1 380px;min-width:0;max-width:100%;padding:1.75rem 2rem;border-right:1px solid var(--border);border-bottom:1px solid var(--border);display:flex;flex-direction:column;justify-content:space-between;gap:1.5rem;">
                        <div>
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                                <div>
                                    <div class="kpi-label">Ajustes de inventario</div>
                                    <div class="kpi-value"
                                        style="color:{{ $k9['meta_cumplida'] ? 'var(--inventario)' : '#ef4444' }};margin-top:.25rem;font-size:2.5rem">
                                        {{ $k9['cantidad'] }}
                                    </div>
                                    <div style="font-size:.78rem;color:var(--muted);margin-top:.2rem">ajustes registrados
                                        este mes</div>
                                </div>
                                <div class="kpi-icon"
                                    style="background:rgba(245,158,11,.1);color:var(--inventario);flex-shrink:0">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div style="margin-bottom:.75rem">
                                @if ($k9['meta_cumplida'])
                                    <span class="badge-ok" style="font-size:.78rem"><i
                                            class="fas fa-check-circle me-1"></i>Excelente</span>
                                @else
                                    <span class="badge-warn" style="font-size:.78rem"><i
                                            class="fas fa-exclamation-triangle me-1"></i>Revisar proceso</span>
                                @endif
                            </div>
                            <div class="prog-track">
                                <div class="prog-bar"
                                    style="width:{{ 100 - $k9['progreso'] }}%;background:{{ $k9['meta_cumplida'] ? 'var(--inventario)' : '#ef4444' }}">
                                </div>
                            </div>
                            <div
                                style="display:flex;justify-content:space-between;font-size:.72rem;color:var(--muted);margin-top:.4rem">
                                <span style="font-weight:500">Meta: Límite ajustes ≤ {{ $k9['meta'] }}/ mes</span>
                                <span style="font-weight:500">
                                    {{ $k9['meta'] - $k9['cantidad'] >= 0
                                        ? $k9['meta'] - $k9['cantidad'] . ' restantes'
                                        : abs($k9['meta'] - $k9['cantidad']) . ' sobre límite' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Lado derecho: tabla últimos ajustes --}}
                    <div style="flex:1;min-width:300px;width:100%;display:flex;flex-direction:column">
                        <div style="padding:.9rem 1.25rem;border-bottom:1px solid var(--border);">
                            <div
                                style="font-size:.65rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--muted)">
                                Últimos ajustes registrados
                            </div>
                        </div>
                        @if ($k9['ultimos']->isEmpty())
                            <div class="empty"><i class="fas fa-clipboard-check"></i>Sin ajustes este mes</div>
                        @else
                            <div style="overflow-x:auto;flex:1">
                                <table class="stock-table" style="min-width:500px">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Producto</th>
                                            <th>Usuario</th>
                                            <th>Anterior</th>
                                            <th>Nuevo</th>
                                            <th>Tipo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($k9['ultimos'] as $aj)
                                            <tr>
                                                <td style="color:var(--muted);white-space:nowrap">{{ $aj['fecha'] }}</td>
                                                <td
                                                    style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                                    {{ $aj['producto'] }}</td>
                                                <td style="color:var(--muted);white-space:nowrap">{{ $aj['usuario'] }}
                                                </td>
                                                <td style="color:var(--muted)">{{ number_format($aj['anterior'], 0) }}</td>
                                                <td style="font-weight:600">{{ number_format($aj['nueva'], 0) }}</td>
                                                <td>
                                                    @if ($aj['tipo'] === 'aumento')
                                                        <span class="pill"
                                                            style="background:#f0fdf4;color:#10b981;border:1px solid #d1fae5">
                                                            <i
                                                                class="fas fa-arrow-up me-1"></i>+{{ number_format($aj['diferencia'], 0) }}
                                                        </span>
                                                    @else
                                                        <span class="pill pill-red">
                                                            <i
                                                                class="fas fa-arrow-down me-1"></i>{{ number_format($aj['diferencia'], 0) }}
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                </div>
            </div>

        </div>

        {{-- MÓDULO: TRASLADOS — KPI 8 --}}
        <div class="module-section fade-up delay-3">
            <div class="module-label">
                <span class="mod-badge" style="background:#eff6ff;border-color:#bfdbfe;color:#3b82f6;">
                    <i class="fas fa-truck me-2"></i>Traslados
                </span>
                <div class="line"></div>
            </div>

            @php $k8 = $kpi_traslados['kpi8_traslados_cancelados']; @endphp

            <div class="grid-2">

                {{-- KPI 8 + Detalle del mes (unificado) --}}
                <div class="card t" style="padding:0;overflow:hidden;">
                    <div style="height:3px;background:var(--traslados)"></div>

                    {{-- Header KPI --}}
                    <div
                        style="display:flex;justify-content:space-between;align-items:flex-start;padding:1.1rem 1.25rem .9rem;border-bottom:1px solid var(--border);">
                        <div>
                            <div class="kpi-label">Traslados cancelados</div>
                            <div class="kpi-value"
                                style="color:{{ $k8['meta_cumplida'] ? 'var(--traslados)' : '#ef4444' }};margin-top:.25rem">
                                {{ $k8['tasa'] }}%
                            </div>
                            <div style="font-size:.72rem;color:var(--muted);margin-top:.15rem">
                                {{ $k8['cancelados'] }} cancelados de {{ $k8['total'] }} traslados del mes
                            </div>
                        </div>
                        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.5rem">
                            <div class="kpi-icon" style="background:rgba(59,130,246,.1);color:var(--traslados)">
                                <i class="fas fa-truck"></i>
                            </div>
                            @if ($k8['meta_cumplida'])
                                <span class="badge-ok" style="font-size:.7rem"><i
                                        class="fas fa-check me-1"></i>Eficiente</span>
                            @else
                                <span class="badge-bad" style="font-size:.7rem"><i class="fas fa-times me-1"></i>Alta
                                    cancelación</span>
                            @endif
                        </div>
                    </div>

                    {{-- Progress --}}
                    <div style="padding:.75rem 1.25rem;border-bottom:1px solid var(--border);">
                        <div class="prog-track">
                            <div class="prog-bar"
                                style="width:{{ 100 - $k8['progreso'] }}%;background:{{ $k8['meta_cumplida'] ? 'var(--traslados)' : '#ef4444' }}">
                            </div>
                        </div>
                        <div
                            style="display:flex;justify-content:space-between;font-size:.7rem;color:var(--muted);margin-top:.35rem">
                            <span style="font-weight:500">Meta: ≤ {{ $k8['meta'] }}% traslados cancelados</span>
                            <span style="font-weight:500">{{ 100 - $k8['progreso'] }}% dentro del límite</span>
                        </div>
                    </div>

                    {{-- Detalle del mes —  3 stats en fila --}}

                    <div style="display:grid;grid-template-columns:repeat(3,1fr);padding:1rem 1.25rem;gap:.5rem">
                        <div style="text-align:center;padding:.75rem;background:var(--surface2);border-radius:10px;">
                            <div
                                style="font-size:.65rem;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);margin-bottom:.35rem">
                                Total</div>
                            <div style="font-size:1.5rem;font-weight:700;color:var(--text)">{{ $k8['total'] }}</div>
                        </div>
                        <div style="text-align:center;padding:.75rem;background:#fef2f2;border-radius:10px;">
                            <div
                                style="font-size:.65rem;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:#ef4444;margin-bottom:.35rem">
                                Cancelados</div>
                            <div style="font-size:1.5rem;font-weight:700;color:#ef4444">{{ $k8['cancelados'] }}</div>
                        </div>
                        <div style="text-align:center;padding:.75rem;background:#eff6ff;border-radius:10px;">
                            <div
                                style="font-size:.65rem;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--traslados);margin-bottom:.35rem">
                                Exitosos</div>
                            <div style="font-size:1.5rem;font-weight:700;color:var(--traslados)">
                                {{ $k8['total'] - $k8['cancelados'] }}</div>
                        </div>
                    </div>
                </div>

                {{-- Gráfico dona --}}

                <div class="card t chart-card" style="padding:0;overflow:hidden;">
                    <div style="height:3px;background:var(--traslados)"></div>
                    <div style="padding:1.5rem;">
                        <div class="chart-title">Distribución traslados</div>
                        <div style="position:relative;height:220px"><canvas id="chartTraslados"></canvas></div>
                    </div>
                </div>

            </div>
        </div>

        {{-- MÓDULO: CLIENTES — KPI 6 --}}
        <div class="module-section fade-up delay-4">
            <div class="module-label">
                <span class="mod-badge" style="background:#faf5ff;border-color:#e9d5ff;color:#8b5cf6;">
                    <i class="fas fa-users me-2"></i>Clientes
                </span>
                <div class="line"></div>
            </div>

            @php $k6 = $kpi_clientes['kpi6_clientes_frecuentes']; @endphp

            <div class="card c" style="padding:0;overflow:hidden;">
                <div style="height:3px;background:var(--clientes)"></div>
                <div style="display:flex;flex-wrap:wrap;align-items:stretch;">

                    {{-- Lado izquierdo: indicador KPI --}}
                    <div
                        style="flex:0 1 400px;min-width:0;max-width:100%;padding:1.5rem 1.75rem;border-right:1px solid var(--border);border-bottom:1px solid var(--border);display:flex;flex-direction:column;justify-content:space-between;gap:1.25rem;">
                        <div>
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                                <div>
                                    <div class="kpi-label">Clientes frecuentes</div>
                                    <div class="kpi-value"
                                        style="color:{{ $k6['meta_cumplida'] ? 'var(--clientes)' : '#f59e0b' }};margin-top:.25rem;font-size:2.5rem">
                                        {{ $k6['porcentaje'] }}%
                                    </div>
                                    <div style="font-size:.78rem;color:var(--muted);margin-top:.2rem">
                                        del total de clientes activos
                                    </div>
                                </div>
                                <div class="kpi-icon"
                                    style="background:rgba(236,72,153,.1);color:var(--clientes);flex-shrink:0">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div style="margin-bottom:.75rem">
                                @if ($k6['meta_cumplida'])
                                    <span class="badge-ok" style="font-size:.78rem"><i
                                            class="fas fa-heart me-1"></i>Buena fidelización</span>
                                @else
                                    <span class="badge-warn" style="font-size:.78rem"><i
                                            class="fas fa-exclamation me-1"></i>Mejorar retención</span>
                                @endif
                            </div>
                            <div class="prog-track">
                                <div class="prog-bar" style="width:{{ $k6['progreso'] }}%;background:var(--clientes)">
                                </div>
                            </div>
                            <div
                                style="display:flex;justify-content:space-between;font-size:.72rem;color:var(--muted);margin-top:.4rem">
                                <span style="font-weight:500">Meta: ≥ {{ $k6['meta'] }}%</span>
                                <span style="font-weight:500">{{ $k6['cantidad'] }} de {{ $k6['total_activos'] }}
                                    clientes</span>
                            </div>
                        </div>
                    </div>

                    {{-- Lado derecho: lista de clientes frecuentes --}}
                    <div style="flex:1;min-width:300px;width:100%;display:flex;flex-direction:column;">
                        <div
                            style="padding:.9rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:.75rem;">
                            <div
                                style="font-size:.65rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--muted)">
                                Clientes con más de 5 compras
                            </div>
                            @if ($k6['cantidad'] > 0)
                                <span
                                    style="background:rgba(236,72,153,.1);border:1px solid rgba(236,72,153,.2);color:var(--clientes);padding:.15rem .55rem;border-radius:99px;font-size:.62rem;font-weight:700">
                                    {{ $k6['cantidad'] }} clientes
                                </span>
                            @endif
                        </div>

                        @if ($k6['lista']->isEmpty())
                            <div class="empty">
                                <i class="fas fa-user-clock"></i>
                                Aún no hay clientes con más de 5 compras
                            </div>
                        @else
                            <div style="overflow-x:auto;flex:1">
                                <table class="stock-table" style="min-width:400px">
                                    <thead>
                                        <tr>
                                            <th>Cliente</th>
                                            <th>Grupo</th>
                                            <th>Compras</th>
                                            <th>Monto total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($k6['lista'] as $cliente)
                                            <tr>
                                                <td style="font-weight:500">
                                                    <div style="display:flex;align-items:center;gap:.6rem">
                                                        <div
                                                            style="width:30px;height:30px;border-radius:8px;background:rgba(236,72,153,.1);color:var(--clientes);display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;flex-shrink:0">
                                                            {{ strtoupper(substr($cliente->nombre_completo, 0, 2)) }}
                                                        </div>
                                                        <span
                                                            style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:150px">{{ $cliente->nombre_completo }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span
                                                        style="background:#f8fafc;border:1px solid var(--border);padding:.15rem .55rem;border-radius:99px;font-size:.68rem;color:var(--muted)">
                                                        {{ $cliente->grupo }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span
                                                        style="font-weight:700;color:var(--clientes)">{{ $cliente->total_compras }}</span>
                                                    <span style="font-size:.7rem;color:var(--muted)"> compras</span>
                                                </td>
                                                <td style="font-weight:600;color:var(--text)">
                                                    Bs/ {{ number_format($cliente->monto_total, 0) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>

        {{-- MÓDULO: USUARIOS — KPI 7 --}}
        <div class="module-section fade-up delay-5">
            <div class="module-label">
                <span class="mod-badge" style="background:#eef2ff;border-color:#d5d5d6;color:#000000;">
                    <i class="fas fa-user-cog me-2"></i>Usuarios
                </span>
                <div class="line"></div>
            </div>

            @php $k7 = $kpi_usuarios['kpi7_productividad']; @endphp

            <div class="card u" style="padding:0;overflow:hidden;">
                <div style="height:3px;background:var(--usuarios)"></div>
                <div style="display:flex;flex-wrap:wrap;align-items:stretch;">

                    {{-- Lado izquierdo: indicador KPI --}}
                    <div
                        style="flex:0 1 400px;min-width:0;max-width:100%;padding:1.5rem 1.75rem;border-right:1px solid var(--border);border-bottom:1px solid var(--border);display:flex;flex-direction:column;justify-content:space-between;gap:1.25rem;">
                        <div>
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                                <div>
                                    <div class="kpi-label">Productividad por usuario</div>
                                    <div class="kpi-value"
                                        style="color:var(--usuarios);margin-top:.25rem;font-size:2.5rem">
                                        {{ $k7['porcentaje'] }}%
                                    </div>
                                    <div style="font-size:.78rem;color:var(--muted);margin-top:.2rem">
                                        del equipo cumple ambas metas
                                    </div>
                                </div>
                                <div class="kpi-icon"
                                    style="background:rgba(79,70,229,.1);color:var(--usuarios);flex-shrink:0">
                                    <i class="fas fa-user-check"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div style="margin-bottom:.75rem">
                                @if ($k7['porcentaje'] >= 100)
                                    <span class="badge-ok" style="font-size:.78rem"><i
                                            class="fas fa-check-circle me-1"></i>Equipo al 100%</span>
                                @elseif($k7['porcentaje'] > 0)
                                    <span class="badge-warn" style="font-size:.78rem"><i
                                            class="fas fa-exclamation-triangle me-1"></i>En progreso</span>
                                @else
                                    <span class="badge-bad" style="font-size:.78rem"><i
                                            class="fas fa-times-circle me-1"></i>Sin alcanzar meta</span>
                                @endif
                            </div>
                            <div class="prog-track">
                                <div class="prog-bar" style="width:{{ $k7['porcentaje'] }}%;background:var(--usuarios)">
                                </div>
                            </div>
                            <div
                                style="display:flex;justify-content:space-between;font-size:.72rem;color:var(--muted);margin-top:.4rem">
                                <span style="font-weight:500">Meta: Ventas ≥100 · Bs/6000</span>
                                <span style="font-weight:500">{{ $k7['cumplen_meta'] }} de {{ $k7['total_usuarios'] }}
                                    operadores</span>

                            </div>
                        </div>
                    </div>

                    {{-- Lado derecho: tabla estilo ranking --}}
                    <div style="flex:1;min-width:300px;width:100%;display:flex;flex-direction:column;">
                        <div
                            style="padding:.9rem 1.25rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
                            <div
                                style="font-size:.65rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--muted)">
                                Detalle por operador</div>

                        </div>
                        <div style="overflow-x:auto;flex:1;">
                            <table class="stock-table" style="min-width:520px;">
                                <thead>
                                    <tr>
                                        <th>Operador</th>
                                        <th>Ventas</th>
                                        <th style="width:90px"></th>
                                        <th>Monto</th>
                                        <th style="width:90px"></th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($k7['detalle'] as $i => $user)
                                        <tr>

                                            <td>
                                                <div style="display:flex;align-items:center;gap:.65rem;">
                                                    <div
                                                        style="width:30px;height:30px;border-radius:9px;background:{{ $user['meta_cumplida'] ? 'rgba(79,70,229,.12)' : '#f1f5f9' }};color:{{ $user['meta_cumplida'] ? 'var(--usuarios)' : 'var(--muted)' }};display:flex;align-items:center;justify-content:center;font-size:.68rem;font-weight:700;flex-shrink:0;">
                                                        {{ strtoupper(substr($user['nombre'], 0, 2)) }}
                                                    </div>
                                                    <span
                                                        style="font-size:.84rem;font-weight:600;color:var(--text);white-space:nowrap;">{{ $user['nombre'] }}</span>
                                                </div>
                                            </td>
                                            <td style="white-space:nowrap;">
                                                <span
                                                    style="font-weight:700;color:var(--usuarios);">{{ $user['cantidad_ventas'] }}</span>
                                                <span
                                                    style="font-size:.72rem;color:var(--muted);">/{{ $k7['meta_cantidad'] }}</span>
                                            </td>
                                            <td>
                                                <div
                                                    style="height:5px;background:#f1f5f9;border-radius:99px;overflow:hidden;width:80px;">
                                                    <div
                                                        style="width:{{ $user['progreso_ventas'] }}%;height:100%;background:var(--usuarios);border-radius:99px;">
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="white-space:nowrap;">
                                                <span style="font-weight:600;color:var(--text);">Bs/
                                                    {{ number_format($user['monto_total'], 0) }}</span>
                                                <span
                                                    style="font-size:.72rem;color:var(--muted);">/{{ number_format($k7['meta_monto'], 0) }}</span>
                                            </td>
                                            <td>
                                                <div
                                                    style="height:5px;background:#f1f5f9;border-radius:99px;overflow:hidden;width:80px;">
                                                    <div
                                                        style="width:{{ $user['progreso_monto'] }}%;height:100%;background:#06b6d4;border-radius:99px;">
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if ($user['meta_cumplida'])
                                                    <span class="pill"
                                                        style="background:rgba(16,185,129,.1);color:#10b981;border:1px solid rgba(16,185,129,.2);">
                                                        <i class="fas fa-check me-1"></i>OK
                                                    </span>
                                                @else
                                                    <span class="pill pill-red">
                                                        <i class="fas fa-times me-1"></i>Pendiente
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7">
                                                <div class="empty"><i class="fas fa-user-slash"></i>Sin usuarios
                                                    registrados</div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <div class="modal fade" id="metricasModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-bottom-0 pb-0 ps-4 pt-4">
                    <h5 class="modal-title fw-bold">Resumen del Día</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 rounded-4 bg-primary bg-opacity-10 text-center h-100">
                                <i class="fas fa-money-bill-wave text-primary fs-3 mb-2"></i>
                                <div class="text-muted small fw-bold text-uppercase">Ventas Hoy</div>
                                <div class="h4 fw-bold text-dark mb-0">
                                    Bs/ {{ number_format($ventasHoy, 0) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded-4 bg-success bg-opacity-10 text-center h-100">
                                <i class="fas fa-shopping-cart text-success fs-3 mb-2"></i>
                                <div class="text-muted small fw-bold text-uppercase">Compras Hoy</div>
                                <div class="h4 fw-bold text-dark mb-0">Bs/
                                    {{ number_format($ventasHoy['comprasHoy'] ?? 0, 0) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 text-center">
                        <small class="text-muted">
                            <i class="far fa-clock me-1"></i> Actualizado: {{ now()->format('H:i') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const gridColor = '#f1f5f9';
            const mutedColor = '#94a3b8';

            Chart.defaults.color = mutedColor;
            Chart.defaults.font.family = "'Inter', sans-serif";
            Chart.defaults.font.size = 12;
            Chart.defaults.plugins.legend.labels.boxWidth = 10;
            Chart.defaults.plugins.legend.labels.padding = 16;

            var gOpts = {
                color: gridColor,
                drawBorder: false
            };

            var tStyle = {
                backgroundColor: '#1e293b',
                titleColor: '#f8fafc',
                bodyColor: '#cbd5e1',
                borderColor: '#334155',
                borderWidth: 1,
                padding: 12,
                cornerRadius: 8
            };

            // Flujo de Caja Anual
            new Chart(document.getElementById('chartFlujoCaja'), {
                type: 'line',
                data: {
                    labels: @json($kpi_ventas['grafico_flujo_anual']['labels']),
                    datasets: [{
                            label: 'Ventas',
                            data: @json($kpi_ventas['grafico_flujo_anual']['ventas']),
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16,185,129,0.08)',
                            borderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 6,
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Compras',
                            data: @json($kpi_ventas['grafico_flujo_anual']['compras']),
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239,68,68,0.05)',
                            borderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 6,
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end'
                        },
                        tooltip: {
                            backgroundColor: tStyle.backgroundColor,
                            titleColor: tStyle.titleColor,
                            bodyColor: tStyle.bodyColor,
                            borderColor: tStyle.borderColor,
                            borderWidth: tStyle.borderWidth,
                            padding: tStyle.padding,
                            cornerRadius: tStyle.cornerRadius,
                            callbacks: {
                                label: function(ctx) {
                                    return ctx.dataset.label + ': Bs/ ' + ctx.raw.toLocaleString(
                                        'es-BO');
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: gOpts
                        },
                        y: {
                            grid: gOpts,
                            ticks: {
                                callback: function(v) {
                                    return 'Bs/ ' + v.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // Top 5 Productos
            new Chart(document.getElementById('chartTop5'), {
                type: 'bar',
                data: {
                    labels: @json($kpi_inventario['kpi4_top5_productos']['labels']),
                    datasets: [{
                        label: 'Unidades vendidas',
                        data: @json($kpi_inventario['kpi4_top5_productos']['cantidades']),
                        backgroundColor: [
                            'rgba(245,158,11,0.85)', 'rgba(245,158,11,0.68)',
                            'rgba(245,158,11,0.52)', 'rgba(245,158,11,0.38)',
                            'rgba(245,158,11,0.22)'
                        ],
                        borderRadius: 6,
                        borderSkipped: false
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: tStyle.backgroundColor,
                            titleColor: tStyle.titleColor,
                            bodyColor: tStyle.bodyColor,
                            borderColor: tStyle.borderColor,
                            borderWidth: tStyle.borderWidth,
                            padding: tStyle.padding,
                            cornerRadius: tStyle.cornerRadius
                        }
                    },
                    scales: {
                        x: {
                            grid: gOpts
                        },
                        y: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Traslados Dona
            var k8 = @json($kpi_traslados['kpi8_traslados_cancelados']);
            new Chart(document.getElementById('chartTraslados'), {
                type: 'doughnut',
                data: {
                    labels: ['Exitosos', 'Cancelados'],
                    datasets: [{
                        data: [k8.total - k8.cancelados, k8.cancelados],
                        backgroundColor: ['rgba(59,130,246,0.8)', 'rgba(239,68,68,0.8)'],
                        borderColor: '#ffffff',
                        borderWidth: 3,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            backgroundColor: tStyle.backgroundColor,
                            titleColor: tStyle.titleColor,
                            bodyColor: tStyle.bodyColor,
                            borderColor: tStyle.borderColor,
                            borderWidth: tStyle.borderWidth,
                            padding: tStyle.padding,
                            cornerRadius: tStyle.cornerRadius
                        }
                    }
                }
            });

            // LOGICA DEL MODAL (Accesibilidad)

            const metricasModal = document.getElementById('metricasModal');
            if (metricasModal) {
                metricasModal.addEventListener('show.bs.modal', function() {
                    this.removeAttribute('aria-hidden');
                });
                metricasModal.addEventListener('hide.bs.modal', function() {
                    this.setAttribute('aria-hidden', 'true');
                });
            }

        });
    </script>
@endpush