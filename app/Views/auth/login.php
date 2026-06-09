<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="card border-0 shadow-lg p-4 rounded-4 bg-white">
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="mb-3 d-inline-block bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                        <i class="fa-solid fa-boxes-stacked fa-3x"></i>
                    </div>
                    <h4 class="fw-bold text-dark m-0">INVENTARIO</h4>
                    <p class="text-muted small fw-semibold text-primary" style="letter-spacing: 1px; margin-top: 2px;">EPS RIOJA</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger border-0 rounded-3 text-center py-2" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success border-0 rounded-3 text-center py-2" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i> <?= $success ?>
                    </div>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>/login" method="POST" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                    <div class="mb-3">
                        <label for="username" class="form-label small fw-semibold text-secondary">Nombre de Usuario</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0 text-muted"><i class="fa-solid fa-user"></i></span>
                            <input type="text" class="form-control bg-light border-0 py-2 fs-6 rounded-end-3" id="username" name="username" placeholder="Ingrese su usuario" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label small fw-semibold text-secondary">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" class="form-control bg-light border-0 py-2 fs-6 rounded-end-3" id="password" name="password" placeholder="Ingrese su contraseña" required>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary py-2 rounded-3 fw-medium">
                            <i class="fa-solid fa-right-to-bracket me-2"></i> Ingresar al Sistema
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
