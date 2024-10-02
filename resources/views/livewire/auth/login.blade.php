<div>
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-body">
                <section class="flexbox-container">
                    <div class="col-12 d-flex align-items-center justify-content-center">
                        <div class="col-md-4 col-10 box-shadow-2 p-0">
                            <div class="card border-grey border-lighten-3 px-1 py-1 m-0">
                                <div class="card-header border-0">
                                    <h6 class="card-subtitle line-on-side text-muted text-center font-small-3 pt-2">
                                        <span>POOL</span>
                                    </h6>
                                </div>
                                <div class="card-content">
                                    <div class="card-body">
                                        <form class="form-horizontal" wire:submit="login">
											<fieldset  class="form-group position-relative has-icon-left">
												<input type="text" class="form-control" x-model="$wire.user" placeholder="Usuarios..." autocomplete="off">
												<div class="form-control-position">
													<i class="ft-user"></i>
												</div>
												@error('user')
													<span style="color:red;">{{ $message }}</span>
												@enderror
                                            </fieldset>
											<fieldset  class="form-group position-relative has-icon-left">
												<input type="password" class="form-control" x-model="$wire.password" placeholder="Contraseña..." >
												<div class="form-control-position">
													<i class="la la-key"></i>
												</div>
												@error('password')
													<span style="color:red;">{{ $message }}</span>
												@enderror
											</fieldset>
                                            <button type="submit" class="btn btn-outline-info btn-block">Iniciar sesión</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

	@script
		<script>
			$wire.on('entrando', i => {
				console.log('OK')
				// toastRight('warning', 'Contraseña incorrecta!');
			});
			$wire.on('login_fail', i => {
				console.log('F')
				toastRight('warning', 'Contraseña incorrecta!');
			});
			$wire.on('no_register', i => {
				console.log('F2')
				toastRight('warning', 'Usuario no encontrado!');
			});
		</script>
	@endscript
</div>
