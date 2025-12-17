document.addEventListener('DOMContentLoaded', function () {
    // Si Laravel pasa la variable 'prestamos', debe estar disponible en window
    if (typeof window.prestamos === 'undefined') {
        console.error('No se encontró la variable prestamos.');
        return;
    }

    const prestamos = window.prestamos;

    function mapeoRows(prestamos) {
        return prestamos.map((p) => ({
            id: p.id,
            operador: p.operador?.nombre ?? 'Sin operador',
            banco: p.banco?.nombre_banco ?? 'Sin banco',
            cantidad: parseFloat(p.cantidad ?? 0).toFixed(2),

            pagos: parseFloat(p.total_pagado ?? 0).toFixed(2),

            saldo_actual: parseFloat(p.saldo_actual ?? 0).toFixed(2),
            fecha: p.created_at ? new Date(p.created_at).toLocaleDateString() : 'N/A',
        }));
    }

    // Adaptamos los datos para el grid
    const rowData = mapeoRows(prestamos);

    function formatNumber(params) {
        const value = Number(params.value ?? 0);
        return value.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    const columnDefs = [
        { headerName: 'Operador', field: 'operador', flex: 1, sortable: true, filter: true },
        { headerName: 'Banco', field: 'banco', flex: 1, sortable: true, filter: true },

        {
            headerName: 'Cantidad',
            field: 'cantidad',
            width: 130,
            cellStyle: { textAlign: 'right' },
            valueFormatter: formatNumber,
        },

        {
            headerName: 'Pagos',
            field: 'pagos',
            width: 130,
            cellStyle: { textAlign: 'right' },
            valueFormatter: formatNumber,
        },

        {
            headerName: 'Saldo Actual',
            field: 'saldo_actual',
            width: 140,
            cellStyle: { textAlign: 'right', fontWeight: 'bold' },
            valueFormatter: formatNumber,
        },

        { headerName: 'Fecha', field: 'fecha', width: 150 },

        {
            headerName: 'Acciones',
            field: 'acciones',
            width: 180,
            cellRenderer: (params) => `
            <button class="btn btn-sm btn-success btn-abonar" data-id="${params.data.id}">
                💰 Abonar
            </button>
            <button class="btn btn-sm btn-primary btn-ver-historial" data-id="${params.data.id}">
                📄 Historial
            </button>
        `,
        },
    ];
    const gridOptions = {
        columnDefs,
        rowData,
        pagination: true,
        paginationPageSize: 10,
        animateRows: true,
        defaultColDef: {
            resizable: true,
            sortable: true,
            filter: true,
        },
        getRowId: (params) => params.data.id,
        onGridReady: (params) => {
            params.api.sizeColumnsToFit();
        },
    };

    const gridDiv = document.querySelector('#gridPrestamosActivos');
    const gridApi = agGrid.createGrid(gridDiv, gridOptions);

    // Botón para recargar datos
    const btnRecargar = document.getElementById('btnRecargarGrid');
    if (btnRecargar) {
        btnRecargar.addEventListener('click', () => {
            getlistprestamos();
        });
    }

    gridDiv.addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-abonar')) {
            const idPrestamo = e.target.getAttribute('data-id');
            const rowNode = gridApi.getRowNode(idPrestamo);

            if (!rowNode) {
                console.error('No se encontró la fila en la tabla');
                return;
            }

            const saldoActual = parseFloat(rowNode.data.saldo_actual);

            // VALIDACIÓN: no permitir abonar si saldo es 0
            if (saldoActual <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Saldo completado',
                    text: 'Este préstamo ya no tiene saldo pendiente.',
                });
                return; // NO ABRIR MODAL
            }
            abrirModalAbono(idPrestamo);
        } else if (e.target.classList.contains('btn-ver-historial')) {
            const idPrestamo = e.target.getAttribute('data-id');
            abrirDetallePrestamo(idPrestamo);
        }
    });

    function getlistprestamos() {
        fetch('/prestamos/lista')
            .then((response) => response.json())
            .then((data) => {
                const prestamosActualizados = data.prestamos;
                const mappedRows = mapeoRows(prestamosActualizados);
                gridApi.setGridOption('rowData', mappedRows);
            })
            .catch((error) => {
                console.error('Error al recargar los datos:', error);
                errorServidor('Error al recargar los datos. Por favor, intente de nuevo.');
            });
    }

    function abrirModalAbono(idPrestamo) {
        // Guarda el ID en el input hidden
        document.getElementById('id_prestamo_abono').value = idPrestamo;
        // Limpia el monto previo
        document.getElementById('monto_abono').value = '';
        // Muestra el modal
        const modal = new bootstrap.Modal(document.getElementById('modalAbono'));
        modal.show();
    }

    document.getElementById('formAbono').addEventListener('submit', function (e) {
        e.preventDefault();

        const idPrestamo = document.getElementById('id_prestamo_abono').value;
        const monto = parseFloat(document.getElementById('monto_abono').value);
        const idBancoAbono = document.getElementById('id_banco_abono').value;
        const referencia = document.getElementById('referencia').value;
        const _token = document.querySelector('meta[name="csrf-token"]').content;

        if (!idBancoAbono) {
            errorServidor('Seleccione un banco para el abono.');
            return;
        }

        fetch(`/prestamos/${idPrestamo}/abonar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': _token,
            },
            body: JSON.stringify({ monto, id_banco_abono: idBancoAbono, referencia }),
        })
            .then((r) => r.json())
            .then((data) => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Abono registrado',
                        text: 'El abono ha sido registrado correctamente.',
                        confirmButtonColor: '#3085d6',
                    });
                    bootstrap.Modal.getInstance(document.getElementById('modalAbono')).hide();
                    getlistprestamos();
                } else {
                    errorServidor('No se pudo registrar el abono. Intenta nuevamente');
                }
            })
            .catch(() => errorServidor());
    });

    function errorServidor(mssagex = 'No se pudo conectar con el servidor. Intenta nuevamente') {
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: mssagex,
        });
    }

    function abrirDetallePrestamo(idPrestamo) {
        // Limpiar contenido previo
        document.getElementById('totalPrestamo').innerText = '$0.00';
        const tbody = document.querySelector('#movimientosPrestamoTable tbody');
        tbody.innerHTML = '';
        document.getElementById('deudaActual').innerText = '$0.00';

        // Abrimos modal mientras cargamos
        const modal = new bootstrap.Modal(document.getElementById('detallePrestamoModal'));
        modal.show();

        // Fetch al backend
        fetch(`/prestamos/lista-detalle/${idPrestamo}`)
            .then((res) => {
                if (!res.ok) throw new Error('Error al cargar historial');
                return res.json();
            })
            .then((data) => {
                // data = { total: 10000, movimientos: [...] }
                document.getElementById('totalPrestamo').innerText = `$${formatNumber({ value: data.total })}`;

                let deuda = data.total;

                const tbody = document.querySelector('#movimientosPrestamoTable tbody');
                tbody.innerHTML = '';
                document.getElementById('nombreOperador').innerText = data.prestamos.operador.nombre ?? '';

                data.historial.forEach((mov) => {
                    const fecha = mov.fecha_pago
                        ? new Date(mov.fecha_pago).toLocaleDateString()
                        : new Date(mov.created_at).toLocaleDateString();

                    // parseamos monto, si es inválido ponemos 0
                    const monto = parseFloat(mov.monto_pago) || 0;

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
        <td>${mov.tipo_origen ?? ''}</td>
        <td>${fecha}</td>
        <td>$${formatNumber({ value: monto })}</td>
        <td>${mov.referencia ?? ''}</td>
    `;
                    tbody.appendChild(tr);

                    // Calcular deuda actual
                    if (
                        mov.tipo_origen &&
                        (mov.tipo_origen.toLowerCase() === 'directo' || mov.tipo_origen.toLowerCase() === 'liquidacion')
                    ) {
                        deuda -= monto;
                    } else {
                        deuda += monto;
                    }
                });

                document.getElementById('deudaActual').innerText = `$${formatNumber({ value: deuda })}`;
            })
            .catch((err) => {
                console.error(err);
                tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger">No se pudo cargar el historial</td></tr>`;
            });
    }

    function visualizarPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        // Título
        doc.setFontSize(16);
        doc.text('Detalle del Préstamo', 14, 20);

        // Datos ejemplo
        const operador = document.getElementById('nombreOperador').innerText;
        const totalPrestamo = document.getElementById('totalPrestamo').innerText;
        const deudaActual = document.getElementById('deudaActual').innerText;

        doc.setFontSize(12);
        doc.text(`${operador}`, 14, 30);
        doc.text(`Total Préstamo: ${totalPrestamo}`, 14, 38);
        doc.text(`Deuda Actual: ${deudaActual}`, 14, 46);

        // Tabla
        const rows = [];
        document.querySelectorAll('#movimientosPrestamoTable tbody tr').forEach((tr) => {
            const cols = Array.from(tr.children).map((td) => td.innerText);
            rows.push(cols);
        });

        doc.autoTable({
            head: [['Tipo', 'Fecha', 'Monto', 'Referencia']],
            body: rows,
            startY: 55,
            theme: 'grid',
            headStyles: { fillColor: [41, 128, 185], textColor: 255 },
            styles: { fontSize: 10 },
            didDrawPage: function (data) {
                const pageHeight = doc.internal.pageSize.height || doc.internal.pageSize.getHeight();
                doc.setFontSize(10);
                doc.text('SGT - Sistema de Gestión de Transporte', data.settings.margin.left, pageHeight - 10);
            },
        });

        doc.output('dataurlnewwindow');
    }

    document.getElementById('exportarPDF').addEventListener('click', visualizarPDF);
});
