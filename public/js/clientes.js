document.addEventListener("DOMContentLoaded", function () {
    const tabla = document.querySelector("#datatablesSimple");

    if (tabla) {
        new simpleDatatables.DataTable(tabla, {
            perPage: 10,
            perPageSelect: [10, 15, 20],
            searchable: true,
            labels: {
                placeholder: "Buscar por nombre, grupo, teléfono...",
                perPage: "",
                noRows: "No se encontraron resultados",
                info: "",
            },
        });
    }

    // ── Filtros rápidos ──────────────────────────────────────
    const filtroGrupo = document.getElementById("filtroGrupo");
    const filtroDocumento = document.getElementById("filtroDocumento");
    const filtroTipoPersona = document.getElementById("filtroTipoPersona");
    const filtroEstado = document.getElementById("filtroEstado");
    const limpiarBtn = document.getElementById("limpiarFiltros");

    const COL_GRUPO = 1;
    const COL_DOC = 3;
    const COL_TIPO = 4;
    const COL_ESTADO = 5;

    function aplicarFiltros() {
        const grupo = filtroGrupo.value.toLowerCase();
        const doc = filtroDocumento.value.toLowerCase();
        const tipo = filtroTipoPersona.value.toLowerCase();
        const estado = filtroEstado.value.toLowerCase();

        tabla.querySelectorAll("tbody tr").forEach((fila) => {
            const txGrupo =
                fila.cells[COL_GRUPO]?.textContent.trim().toLowerCase() ?? "";
            const txDoc =
                fila.cells[COL_DOC]?.textContent.trim().toLowerCase() ?? "";
            const txTipo =
                fila.cells[COL_TIPO]?.textContent.trim().toLowerCase() ?? "";
            const txEstado =
                fila.cells[COL_ESTADO]?.textContent.trim().toLowerCase() ?? "";

            const ok =
                (!grupo || txGrupo.includes(grupo)) &&
                (!doc || txDoc.includes(doc)) &&
                (!tipo || txTipo.includes(tipo)) &&
                (!estado || txEstado === estado);

            fila.style.display = ok ? "" : "none";
        });

        // Resaltar filtros activos
        [filtroGrupo, filtroDocumento, filtroTipoPersona, filtroEstado].forEach(
            (sel) => {
                sel.closest(".filter-group").classList.toggle(
                    "active",
                    sel.value !== "",
                );
            },
        );
    }

    filtroGrupo.addEventListener("change", aplicarFiltros);
    filtroDocumento.addEventListener("change", aplicarFiltros);
    filtroTipoPersona.addEventListener("change", aplicarFiltros);
    filtroEstado.addEventListener("change", aplicarFiltros);

    limpiarBtn.addEventListener("click", function () {
        filtroGrupo.value = "";
        filtroDocumento.value = "";
        filtroTipoPersona.value = "";
        filtroEstado.value = "";
        aplicarFiltros();
    });
});
