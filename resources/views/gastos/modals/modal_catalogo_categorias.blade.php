<!-- Modal Administrar Categorías y Subcategorías -->
<div class="modal fade" id="modalCatalogoCategorias" data-bs-focus="false" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-gradient-dark py-3 text-white">
                <h5 class="modal-title text-white mb-0">
                    <i class="fa fa-tags me-2"></i> Administrar Categorías y Subcategorías
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-3">
                <ul class="nav nav-tabs mb-3" id="tabCatalogos" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold" id="cat-tab" data-bs-toggle="tab" data-bs-target="#tab-categorias" type="button" role="tab">
                            📁 Categorías
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" id="subcat-tab" data-bs-toggle="tab" data-bs-target="#tab-subcategorias" type="button" role="tab">
                            🏷️ Subcategorías (Conceptos)
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="tabCatalogosContent">
                    <!-- Tab Categorías -->
                    <div class="tab-pane fade show active" id="tab-categorias" role="tabpanel">
                        <div class="card p-3 mb-3 bg-light border shadow-none">
                            <h6 class="mb-2 text-primary fw-bold">Nueva Categoría</h6>
                            <form id="formNuevaCategoria" class="row g-2 align-items-end">
                                @csrf
                                <div class="col-md-9">
                                    <label class="form-label text-xs fw-bold">Nombre de la Categoría *</label>
                                    <input type="text" id="txtNuevaCategoria" class="form-control form-control-sm" placeholder="Ej. Alquileres, Sistemas, Seguros..." required>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-sm btn-primary w-100 mb-0">
                                        <i class="fa fa-plus me-1"></i> Guardar
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="table-responsive" style="max-height: 350px;">
                            <table class="table table-sm table-bordered table-hover align-items-center mb-0" id="tablaCategoriasCatalogo">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 70px;">ID</th>
                                        <th>Categoría</th>
                                        <th style="width: 90px;" class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyCategoriasCatalogo">
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">Cargando categorías...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab Subcategorías -->
                    <div class="tab-pane fade" id="tab-subcategorias" role="tabpanel">
                        <div class="card p-3 mb-3 bg-light border shadow-none">
                            <h6 class="mb-2 text-primary fw-bold">Nueva Subcategoría</h6>
                            <form id="formNuevaSubcategoria" class="row g-2 align-items-end">
                                @csrf
                                <div class="col-md-5">
                                    <label class="form-label text-xs fw-bold">Categoría Padre *</label>
                                    <select id="selectCatPadreSubcat" class="form-select form-select-sm" required>
                                        <option value="">-- Seleccione Categoría --</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-xs fw-bold">Nombre de la Subcategoría *</label>
                                    <input type="text" id="txtNuevaSubcat" class="form-control form-control-sm" placeholder="Ej. Diésel, Mantenimiento Preventivo..." required>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-sm btn-primary w-100 mb-0">
                                        <i class="fa fa-plus me-1"></i> Guardar
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="mb-2 d-flex justify-content-between align-items-center">
                            <label class="text-xs text-muted mb-0">Filtrar por Categoría:</label>
                            <select id="filtroCatEnSubcat" class="form-select form-select-sm w-auto">
                                <option value="">Todas las Categorías</option>
                            </select>
                        </div>

                        <div class="table-responsive" style="max-height: 350px;">
                            <table class="table table-sm table-bordered table-hover align-items-center mb-0" id="tablaSubcategoriasCatalogo">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 60px;">ID</th>
                                        <th>Subcategoría</th>
                                        <th>Categoría Asignada</th>
                                        <th style="width: 90px;" class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodySubcategoriasCatalogo">
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">Cargando subcategorías...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
