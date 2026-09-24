document.addEventListener('DOMContentLoaded', () => {

    const botonesMenu = document.querySelectorAll('.menu-boton');
    const vistas = document.querySelectorAll('.vista');
    const cajaMensaje = document.getElementById('mensaje');
    const textoMensaje = document.getElementById('mensaje-texto');
    const botonCerrarMensaje = document.getElementById('mensaje-cerrar');

    let usuarioSesionActual = null;

    botonesMenu.forEach(boton => {
        boton.addEventListener('click', () => {
            if (boton.dataset.vista === 'vista-login' && usuarioSesionActual) {
                return;
            }

            botonesMenu.forEach(b => b.classList.remove('activo'));
            boton.classList.add('activo');

            vistas.forEach(v => v.classList.add('oculto'));
            document.getElementById(boton.dataset.vista).classList.remove('oculto');

            if (boton.dataset.vista === 'vista-inicio') {
                cargarInicio();
            }

            if (boton.dataset.vista === 'vista-listado') {
                mostrarListaLocales();
                cargarLocales();
            }

            if (boton.dataset.vista === 'vista-comerciantes') {
                mostrarListaComerciantes();
                cargarComerciantes();
            }

                       if (boton.dataset.vista === 'vista-clientes') {
                mostrarListaClientes();
                cargarClientes();
            }

                        if (boton.dataset.vista === 'vista-productos-admin') {
                cargarProductosAdmin();
            }

            if (boton.dataset.vista === 'vista-mis-productos') {
                cargarMisProductos();
            }

            if (boton.dataset.vista === 'vista-dashboard-comerciante') {
                cargarDashboardComerciante();
            }

            if (boton.dataset.vista === 'vista-resenas') {
                cargarDatosResenas();
            }

            if (boton.dataset.vista === 'vista-historiales') {
                cargarHistorialGlobal();
            }

            if (boton.dataset.vista === 'vista-dashboard-admin') {
                cargarDashboardAdmin();
            }

             if (boton.dataset.vista === 'vista-producto') {
                cargarLocalesComercianteParaProducto();
            }

            if (boton.dataset.vista === 'vista-seleccionar-local') {
                mostrarSelectorPerfilesLocal();
            }

            if (boton.dataset.vista === 'vista-mi-cuenta-cliente') {
                cargarMiCuentaCliente();
            }

            if (boton.dataset.vista === 'vista-mi-cuenta-comerciante') {
                cargarMiCuentaComerciante();
            }
        });
    });

    let temporizadorMensaje = null;

    function posicionarMensaje() {
        const cabecera = document.querySelector('.cabecera');
        const margen = 12;
        const topPredeterminado = cabecera
            ? cabecera.getBoundingClientRect().bottom + margen
            : margen;
        cajaMensaje.style.top = topPredeterminado + 'px';
    }

    function ocultarMensaje() {
        clearTimeout(temporizadorMensaje);
        cajaMensaje.className = 'mensaje oculto';
    }

    function mostrarMensaje(texto, tipo) {
        Swal.fire({
            text: texto,
            icon: tipo === 'error' ? 'error' : 'success',
            confirmButtonColor: '#8E7CC3'
        });
    }

    if (botonCerrarMensaje) {
        botonCerrarMensaje.addEventListener('click', ocultarMensaje);
    }

    window.addEventListener('resize', () => {
        if (!cajaMensaje.classList.contains('oculto')) {
            posicionarMensaje();
        }
    });

    function debounce(funcion, espera) {
        let temporizador;
        return (...args) => {
            clearTimeout(temporizador);
            temporizador = setTimeout(() => funcion(...args), espera);
        };
    }

    function soloDigitos(valor) {
        return valor.replace(/\D/g, '');
    }

    function formatearTelefono(input) {
        input.addEventListener('input', () => {
            const digitos = soloDigitos(input.value).slice(0, 8);
            input.value = digitos.length > 4
                ? digitos.slice(0, 4) + '-' + digitos.slice(4)
                : digitos;
        });
    }

    function activarValidacionRequerida(inputEl, mensajeEl, etiqueta) {
        function validar() {
            if (inputEl.value.trim() === '') {
                mensajeEl.textContent = `${etiqueta} es obligatorio`;
                mensajeEl.className = 'ayuda error';
                return false;
            }
            mensajeEl.textContent = '';
            mensajeEl.className = 'ayuda';
            return true;
        }
        inputEl.addEventListener('blur', validar);
        inputEl.addEventListener('input', () => {
            if (mensajeEl.classList.contains('error')) validar();
        });
        return validar;
    }

    const TEXTO_AYUDA_PASSWORD = 'Mínimo 8 caracteres, con al menos una letra mayúscula. Símbolos permitidos: ! @ # $ % ^ & * ( ) _ - + = [ ] { } ; : , . < > ?';
    const PATRON_PASSWORD_PERMITIDO = /^[A-Za-z0-9!@#$%^&*()_\-+=[\]{};:,.<>?]+$/;

    function evaluarPassword(password) {
        if (password.length < 8) {
            return 'La contraseña debe tener al menos 8 caracteres';
        }
        if (!/[A-Z]/.test(password)) {
            return 'La contraseña debe tener al menos una letra mayúscula';
        }
        if (!PATRON_PASSWORD_PERMITIDO.test(password)) {
            return 'La contraseña contiene símbolos no permitidos';
        }
        return null;
    }

    function activarValidacionPassword(inputEl, mensajeEl) {
        function validar() {
            const password = inputEl.value;
            if (password === '') {
                mensajeEl.textContent = TEXTO_AYUDA_PASSWORD;
                mensajeEl.className = 'ayuda';
                return false;
            }
            const error = evaluarPassword(password);
            if (error) {
                mensajeEl.textContent = error;
                mensajeEl.className = 'ayuda error';
                return false;
            }
            mensajeEl.textContent = 'Contraseña válida';
            mensajeEl.className = 'ayuda exito';
            return true;
        }
        inputEl.addEventListener('input', validar);
        inputEl.addEventListener('blur', validar);
        return validar;
    }

    function activarAutocompletadoTipo(inputEl, listaEl, endpoint) {
        const buscar = debounce(async () => {
            const texto = inputEl.value.trim();
            listaEl.innerHTML = '';
            listaEl.classList.add('oculto');

            if (texto.length < 2) return;

            try {
                const r = await fetch(`${endpoint}?texto=${encodeURIComponent(texto)}`);
                const res = await r.json();

                if (!res.exito || res.tipos.length === 0) return;

                res.tipos.forEach(tipo => {
                    const item = document.createElement('div');
                    item.className = 'sugerencia-item';
                    item.textContent = tipo.nombre;
                    item.addEventListener('click', () => {
                        inputEl.value = tipo.nombre;
                        listaEl.innerHTML = '';
                        listaEl.classList.add('oculto');
                    });
                    listaEl.appendChild(item);
                });

                listaEl.classList.remove('oculto');
            } catch (e) { }
        }, 300);

        inputEl.addEventListener('input', buscar);
        document.addEventListener('click', (e) => {
            if (e.target !== inputEl) {
                listaEl.classList.add('oculto');
            }
        });
    }

    activarAutocompletadoTipo(
        document.getElementById('l-tipoLocal'),
        document.getElementById('l-tipo-sugerencias'),
        'api/buscar_tipos_local.php'
    );
    activarAutocompletadoTipo(
        document.getElementById('e-tipoLocal'),
        document.getElementById('e-tipo-sugerencias'),
        'api/buscar_tipos_local.php'
    );

    function activarAlertaSimilares(inputEl, contenedorEl, endpoint, formatearItem) {
        const buscar = debounce(async () => {
            const texto = inputEl.value.trim();
            contenedorEl.innerHTML = '';
            contenedorEl.classList.add('oculto');

            if (texto.length < 3) return;

            try {
                const r = await fetch(`${endpoint}?nombre=${encodeURIComponent(texto)}`);
                const res = await r.json();

                if (!res.exito || res.similares.length === 0) return;

                const titulo = document.createElement('p');
                titulo.className = 'similares-titulo';
                titulo.textContent = '¿Quisiste decir...?';
                contenedorEl.appendChild(titulo);

                res.similares.slice(0, 5).forEach(item => {
                    const fila = document.createElement('div');
                    fila.className = 'similar-item';
                    fila.textContent = formatearItem(item);
                    contenedorEl.appendChild(fila);
                });

                contenedorEl.classList.remove('oculto');
            } catch (e) { }
        }, 400);

        inputEl.addEventListener('input', buscar);
    }

    function activarCascadaUbicacion(selectProvincia, selectCanton, selectDistrito) {
        async function cargarProvincias() {
            try {
                const r = await fetch('api/listar_provincias.php');
                const res = await r.json();
                if (!res.exito) return;

                res.provincias.forEach(p => {
                    const opcion = document.createElement('option');
                    opcion.value = p.idProvincia;
                    opcion.textContent = p.nombre;
                    selectProvincia.appendChild(opcion);
                });
            } catch (e) { }
        }
        cargarProvincias();

        selectProvincia.addEventListener('change', async () => {
            selectCanton.innerHTML = '<option value="">Cargando...</option>';
            selectCanton.disabled = true;
            selectDistrito.innerHTML = '<option value="">Primero elige cantón</option>';
            selectDistrito.disabled = true;

            if (!selectProvincia.value) {
                selectCanton.innerHTML = '<option value="">Primero elige provincia</option>';
                return;
            }

            try {
                const r = await fetch(`api/listar_cantones.php?idProvincia=${selectProvincia.value}`);
                const res = await r.json();

                selectCanton.innerHTML = '<option value="">Seleccione...</option>';
                res.cantones.forEach(c => {
                    const opcion = document.createElement('option');
                    opcion.value = c.idCanton;
                    opcion.textContent = c.nombre;
                    selectCanton.appendChild(opcion);
                });
                selectCanton.disabled = false;
            } catch (e) { }
        });

        selectCanton.addEventListener('change', async () => {
            selectDistrito.innerHTML = '<option value="">Cargando...</option>';
            selectDistrito.disabled = true;

            if (!selectCanton.value) {
                selectDistrito.innerHTML = '<option value="">Primero elige cantón</option>';
                return;
            }

            try {
                const r = await fetch(`api/listar_distritos.php?idCanton=${selectCanton.value}`);
                const res = await r.json();

                selectDistrito.innerHTML = '<option value="">Seleccione...</option>';
                res.distritos.forEach(d => {
                    const opcion = document.createElement('option');
                    opcion.value = d.idDistrito;
                    opcion.textContent = d.nombre;
                    selectDistrito.appendChild(opcion);
                });
                selectDistrito.disabled = false;
            } catch (e) { }
        });
    }

    const selectProvinciaLocal = document.getElementById('l-provincia');
    const selectCantonLocal = document.getElementById('l-canton');
    const selectDistritoLocal = document.getElementById('l-distrito');
    activarCascadaUbicacion(selectProvinciaLocal, selectCantonLocal, selectDistritoLocal);

    const selectProvinciaCliente = document.getElementById('cl-provincia');
    const selectCantonCliente = document.getElementById('cl-canton');
    const selectDistritoCliente = document.getElementById('cl-distrito');
    activarCascadaUbicacion(selectProvinciaCliente, selectCantonCliente, selectDistritoCliente);

    const inputNombreLocal = document.getElementById('l-nombreLocal');
    const mensajeNombreLocal = document.getElementById('l-nombre-msg');

    const verificarNombreLocalDebounced = debounce(async () => {
        const nombre = inputNombreLocal.value.trim();
        mensajeNombreLocal.textContent = '';
        mensajeNombreLocal.className = 'ayuda';
        if (nombre.length < 3) return;

        try {
            const r = await fetch(`api/verificar_nombre_local.php?nombre=${encodeURIComponent(nombre)}`);
            const res = await r.json();
            mensajeNombreLocal.textContent = res.disponible ? 'Nombre disponible' : 'Ya existe un local con ese nombre';
            mensajeNombreLocal.className = res.disponible ? 'ayuda exito' : 'ayuda error';
        } catch (e) { }
    }, 400);

    inputNombreLocal.addEventListener('input', verificarNombreLocalDebounced);

    activarAlertaSimilares(
        inputNombreLocal,
        document.getElementById('l-similares'),
        'api/buscar_locales_similares.php',
        (item) => `${item.nombre} — ${Math.round(item.similitud)}% (${item.tipoLocal ?? 'Sin tipo'}, ${item.totalProductos} producto${item.totalProductos === 1 ? '' : 's'})`
    );

    formatearTelefono(document.getElementById('l-telefono'));
    formatearTelefono(document.getElementById('e-telefono'));

    const formLocal = document.getElementById('form-local');
    const inputLatitudLocal = document.getElementById('l-latitud');
    const inputLongitudLocal = document.getElementById('l-longitud');
    const mensajeGpsLocal = document.getElementById('l-gps-msg');

    function normalizarTextoUbicacion(texto) {
        return String(texto ?? '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }

    function seleccionarOpcionPorTexto(selectEl, texto) {
        if (!texto) return false;
        const objetivo = normalizarTextoUbicacion(texto);
        const opcion = [...selectEl.options].find(o => {
            const t = normalizarTextoUbicacion(o.textContent);
            return t !== '' && (t === objetivo || t.includes(objetivo) || objetivo.includes(t));
        });
        if (opcion) {
            selectEl.value = opcion.value;
            return true;
        }
        return false;
    }

    async function autocompletarUbicacionPorGPS(lat, lng, selectProvincia, selectCanton, selectDistrito) {
        try {
            const r = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=14&addressdetails=1&accept-language=es`);
            const datos = await r.json();
            const direccion = datos.address || {};

            const nombreProvincia = direccion.state;
            const nombreCanton = direccion.county || direccion.city || direccion.town;
            const nombreDistrito = direccion.suburb || direccion.city_district || direccion.neighbourhood || direccion.village;

            if (!nombreProvincia || !seleccionarOpcionPorTexto(selectProvincia, nombreProvincia)) {
                return false;
            }

            selectCanton.innerHTML = '<option value="">Cargando...</option>';
            selectCanton.disabled = true;
            const rc = await fetch(`api/listar_cantones.php?idProvincia=${selectProvincia.value}`);
            const resc = await rc.json();
            selectCanton.innerHTML = '<option value="">Seleccione...</option>';
            (resc.cantones || []).forEach(c => {
                const opcion = document.createElement('option');
                opcion.value = c.idCanton;
                opcion.textContent = c.nombre;
                selectCanton.appendChild(opcion);
            });
            selectCanton.disabled = false;

            if (!nombreCanton || !seleccionarOpcionPorTexto(selectCanton, nombreCanton)) {
                return false;
            }

            selectDistrito.innerHTML = '<option value="">Cargando...</option>';
            selectDistrito.disabled = true;
            const rd = await fetch(`api/listar_distritos.php?idCanton=${selectCanton.value}`);
            const resd = await rd.json();
            selectDistrito.innerHTML = '<option value="">Seleccione...</option>';
            (resd.distritos || []).forEach(d => {
                const opcion = document.createElement('option');
                opcion.value = d.idDistrito;
                opcion.textContent = d.nombre;
                selectDistrito.appendChild(opcion);
            });
            selectDistrito.disabled = false;

            seleccionarOpcionPorTexto(selectDistrito, nombreDistrito);

            return true;
        } catch (e) {
            return false;
        }
    }

    document.getElementById('btn-gps-local')?.addEventListener('click', async () => {
        if (mensajeGpsLocal) mensajeGpsLocal.textContent = 'Obteniendo ubicación...';
        try {
            const coords = await obtenerCoordenadasGPS();
            if (inputLatitudLocal) inputLatitudLocal.value = coords.lat;
            if (inputLongitudLocal) inputLongitudLocal.value = coords.lng;
            if (mensajeGpsLocal) mensajeGpsLocal.textContent = `Ubicación capturada (${coords.lat.toFixed(5)}, ${coords.lng.toFixed(5)}). Buscando provincia, cantón y distrito...`;

            const completado = await autocompletarUbicacionPorGPS(coords.lat, coords.lng, selectProvinciaLocal, selectCantonLocal, selectDistritoLocal);

            if (mensajeGpsLocal) {
                mensajeGpsLocal.textContent = completado
                    ? `Ubicación capturada (${coords.lat.toFixed(5)}, ${coords.lng.toFixed(5)}). Provincia, cantón y distrito rellenados automáticamente — revísalos antes de guardar.`
                    : `Ubicación capturada (${coords.lat.toFixed(5)}, ${coords.lng.toFixed(5)}). No se pudo identificar provincia/cantón/distrito automáticamente, selecciónalos a mano.`;
            }
        } catch (e) {
            if (mensajeGpsLocal) mensajeGpsLocal.textContent = 'No se pudo obtener tu ubicación. Puedes registrar el local sin GPS.';
        }
    });

    formLocal.addEventListener('submit', async (evento) => {
        evento.preventDefault();
        const datos = new FormData();
        datos.append('alias', document.getElementById('l-alias').value);
        datos.append('nombreTipoLocal', document.getElementById('l-tipoLocal').value);
        datos.append('nombreLocal', inputNombreLocal.value);
        datos.append('descripcion', document.getElementById('l-descripcion').value);
        datos.append('telefono', document.getElementById('l-telefono').value);
        datos.append('idProvincia', selectProvinciaLocal.value);
        datos.append('idCanton', selectCantonLocal.value);
        datos.append('idDistrito', selectDistritoLocal.value);
        datos.append('direccionExacta', document.getElementById('l-direccion').value);
        datos.append('referencia', document.getElementById('l-referencia').value);
        datos.append('latitud', inputLatitudLocal ? inputLatitudLocal.value : '');
        datos.append('longitud', inputLongitudLocal ? inputLongitudLocal.value : '');

        const archivoLogo = document.getElementById('l-logo').files[0];
        if (archivoLogo) {
            datos.append('logo', archivoLogo);
        }

        try {
            const r = await fetch('api/registrar_local.php', {
                method: 'POST',
                body: datos
            });
            const res = await r.json();

            mostrarMensaje(res.mensaje, res.exito ? 'exito' : 'error');

            if (res.exito) {
                formLocal.reset();
                mensajeNombreLocal.textContent = '';
                if (mensajeGpsLocal) mensajeGpsLocal.textContent = '';
                selectCantonLocal.innerHTML = '<option value="">Primero elige provincia</option>';
                selectCantonLocal.disabled = true;
                selectDistritoLocal.innerHTML = '<option value="">Primero elige cantón</option>';
                selectDistritoLocal.disabled = true;

                if (res.usuario) {
                    actualizarIndicadorSesion(res.usuario);
                }

                await mostrarSelectorPerfilesLocal();
            }
        } catch (e) {
            mostrarMensaje('Error al crear el local', 'error');
        }
    });

    const inputIdentificacionCliente = document.getElementById('cl-numeroIdentificacion');
    const mensajeIdentificacionCliente = document.getElementById('cl-identificacion-msg');
    const inputCorreoCliente = document.getElementById('cl-correo');
    const mensajeCorreoCliente = document.getElementById('cl-correo-msg');

    const verificarIdentificacionClienteDebounced = debounce(async () => {
        const numeroIdentificacion = inputIdentificacionCliente.value.trim();
        mensajeIdentificacionCliente.textContent = '';
        mensajeIdentificacionCliente.className = 'ayuda';
        if (numeroIdentificacion.length < 5) return;

        try {
            const r = await fetch(`api/verificar_identificacion_cliente.php?numeroIdentificacion=${encodeURIComponent(numeroIdentificacion)}`);
            const res = await r.json();
            mensajeIdentificacionCliente.textContent = res.existe ? 'Esta identificación ya está registrada' : 'Identificación disponible';
            mensajeIdentificacionCliente.className = res.existe ? 'ayuda error' : 'ayuda exito';
        } catch (e) { }
    }, 400);

    inputIdentificacionCliente.addEventListener('input', verificarIdentificacionClienteDebounced);

    const verificarCorreoClienteDebounced = debounce(async () => {
        const correo = inputCorreoCliente.value.trim();
        mensajeCorreoCliente.textContent = '';
        mensajeCorreoCliente.className = 'ayuda';
        if (!correo.includes('@') || !correo.includes('.')) return;

        try {
            const r = await fetch(`api/verificar_correo_cliente.php?correo=${encodeURIComponent(correo)}`);
            const res = await r.json();
            mensajeCorreoCliente.textContent = res.existe ? 'Este correo ya está registrado' : 'Correo disponible';
            mensajeCorreoCliente.className = res.existe ? 'ayuda error' : 'ayuda exito';
        } catch (e) { }
    }, 500);

    inputCorreoCliente.addEventListener('input', verificarCorreoClienteDebounced);

    const inputNombreCliente = document.getElementById('cl-nombreCompleto');
    const inputPasswordCliente = document.getElementById('cl-password');

    const validarNombreCliente = activarValidacionRequerida(
        inputNombreCliente,
        document.getElementById('cl-nombreCompleto-msg'),
        'El nombre'
    );
    const validarPasswordCliente = activarValidacionPassword(
        inputPasswordCliente,
        document.getElementById('cl-password-msg')
    );

    document.getElementById('form-cliente').addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const camposValidosCliente = [
            validarNombreCliente(),
            validarPasswordCliente()
        ];

        if (camposValidosCliente.includes(false)) {
            mostrarMensaje('Revisa los campos marcados en el formulario', 'error');
            return;
        }

        const correoNuevo = inputCorreoCliente.value;
        const passwordNueva = document.getElementById('cl-password').value;

        const datos = new FormData();
        datos.append('nombreCompleto', document.getElementById('cl-nombreCompleto').value);
        datos.append('tipoIdentificacion', document.getElementById('cl-tipoIdentificacion').value);
        datos.append('numeroIdentificacion', inputIdentificacionCliente.value.trim());
        datos.append('correo', correoNuevo);
        datos.append('password', passwordNueva);
        datos.append('idProvincia', selectProvinciaCliente.value);
        datos.append('idCanton', selectCantonCliente.value);
        datos.append('idDistrito', selectDistritoCliente.value);
        datos.append('direccionExacta', document.getElementById('cl-direccion').value);
        datos.append('referencia', document.getElementById('cl-referencia').value);

        const archivoFoto = document.getElementById('cl-fotoPerfil').files[0];
        if (archivoFoto) {
            datos.append('fotoPerfil', archivoFoto);
        }

        try {
            const r = await fetch('api/registrar_cliente.php', {
                method: 'POST',
                body: datos
            });
            const res = await r.json();

            if (!res.exito) {
                mostrarMensaje(res.mensaje, 'error');
                return;
            }

            evento.target.reset();
            mensajeIdentificacionCliente.textContent = '';
            mensajeCorreoCliente.textContent = '';
            document.getElementById('cl-nombreCompleto-msg').textContent = '';
            document.getElementById('cl-nombreCompleto-msg').className = 'ayuda';
            const mensajePasswordCliente = document.getElementById('cl-password-msg');
            mensajePasswordCliente.textContent = TEXTO_AYUDA_PASSWORD;
            mensajePasswordCliente.className = 'ayuda';
            selectCantonCliente.innerHTML = '<option value="">Primero elige provincia</option>';
            selectCantonCliente.disabled = true;
            selectDistritoCliente.innerHTML = '<option value="">Primero elige cantón</option>';
            selectDistritoCliente.disabled = true;

            const datosLogin = new FormData();
            datosLogin.append('correo', correoNuevo);
            datosLogin.append('password', passwordNueva);

            const rLogin = await fetch('api/login.php', { method: 'POST', body: datosLogin });
            const resLogin = await rLogin.json();

            if (!resLogin.exito) {
                mostrarMensaje('Cuenta creada. Ahora inicia sesión.', 'exito');
                mostrarVistaLogin('vista-login');
                return;
            }

            actualizarIndicadorSesion(resLogin.usuario);
            mostrarMensaje('¡Cuenta creada! Sesión iniciada correctamente', 'exito');

            try {
                const coords = await obtenerCoordenadasGPS();
                await fetch('api/registrar_ubicacion_login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ latitud: coords.lat, longitud: coords.lng })
                });
            } catch (e) {
            }

            if (compraProductoPendiente) {
                const productoRetomado = compraProductoPendiente;
                compraProductoPendiente = null;
                mostrarVistaLogin('vista-inicio');
                abrirModalProducto(productoRetomado);
            } else {
                mostrarVistaLogin('vista-inicio');
            }
        } catch (e) {
            mostrarMensaje('Error de conexión con el servidor', 'error');
        }
    });

    const panelLista = document.getElementById('panel-lista-locales');
    const panelDetalle = document.getElementById('panel-detalle-local');

    function mostrarListaLocales() {
        panelDetalle.classList.add('oculto');
        panelLista.classList.remove('oculto');
    }

     function confirmarEliminarLocal(idLocal, nombreLocal, tarjeta) {
        Swal.fire({
            title: `¿Eliminar "${nombreLocal}"?`,
            text: 'Este local se marcará como inactivo y dejará de verse en la app.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#8E7CC3'
        }).then(async (resultado) => {
            if (!resultado.isConfirmed) return;

            try {
                const r = await fetch('api/eliminar_local.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ idLocal })
                });
                const res = await r.json();

                if (res.exito) {
                    tarjeta.remove();
                    mostrarMensaje('Local eliminado correctamente', 'exito');
                } else {
                    mostrarMensaje(res.mensaje || 'No se pudo eliminar el local', 'error');
                }
            } catch (e) {
                mostrarMensaje('Error al eliminar el local', 'error');
            }
        });
    }

    async function mostrarSugerenciasBusqueda(nombre, contenedor) {
        contenedor.innerHTML = '<p>Buscando algo parecido...</p>';

        try {
            const [rLocales, rProductos] = await Promise.all([
                fetch(`api/buscar_locales_similares.php?nombre=${encodeURIComponent(nombre)}`),
                fetch(`api/buscar_productos_similares.php?nombre=${encodeURIComponent(nombre)}`)
            ]);
            const resLocales = await rLocales.json();
            const resProductos = await rProductos.json();

            const localesSimilares = resLocales.exito ? resLocales.similares : [];
            const productosSimilares = resProductos.exito ? resProductos.similares : [];

            if (localesSimilares.length === 0 && productosSimilares.length === 0) {
                contenedor.innerHTML = '<p>No se encontró ningún local ni producto parecido a tu búsqueda.</p>';
                return;
            }

            contenedor.innerHTML = '';

            const titulo = document.createElement('p');
            titulo.className = 'similares-titulo';
            titulo.textContent = '¿Quisiste decir...?';
            contenedor.appendChild(titulo);

            localesSimilares.slice(0, 5).forEach(item => {
                const tarjeta = document.createElement('div');
                tarjeta.className = 'tarjeta tarjeta-clic';
                tarjeta.innerHTML = `
                    <h3>${item.nombre} <span class="etiqueta-tipo">${Math.round(item.similitud)}% parecido</span></h3>
                    <p class="etiqueta-tipo">${item.tipoLocal ?? ''}</p>
                    <p>${item.totalProductos} producto${item.totalProductos === 1 ? '' : 's'}</p>
                `;
                tarjeta.addEventListener('click', () => abrirDetalleLocal(item.idLocal));
                contenedor.appendChild(tarjeta);
            });

            productosSimilares.slice(0, 5).forEach(item => {
                item.locales.forEach(loc => {
                    const tarjeta = document.createElement('div');
                    tarjeta.className = 'tarjeta tarjeta-clic';
                    tarjeta.innerHTML = `
                        <h3>${item.nombre} <span class="etiqueta-tipo">${Math.round(item.similitud)}% parecido</span></h3>
                        <p>Disponible en: ${loc.nombreLocal}</p>
                    `;
                    tarjeta.addEventListener('click', () => abrirDetalleLocal(loc.idLocal));
                    contenedor.appendChild(tarjeta);
                });
            });
        } catch (e) {
            contenedor.innerHTML = '<p>Error al buscar sugerencias.</p>';
        }
    }

      async function abrirDetalleLocal(idLocal) {
        try {
            const r = await fetch(`api/buscar_local.php?id=${idLocal}`);
            const res = await r.json();

            if (!res.exito) {
                mostrarMensaje(res.mensaje || 'No se pudo cargar el local', 'error');
                return;
            }

            const { local, ubicacion } = res;

            const formEditarLocal = document.getElementById('form-editar-local');
            const infoSoloLectura = document.getElementById('e-info-solo-lectura');

            formEditarLocal.classList.add('oculto');
            infoSoloLectura.classList.remove('oculto');

            document.getElementById('e-solo-tipo').textContent = local.tipoLocal ?? '';
            document.getElementById('e-solo-nombre').textContent = local.nombreLocal;
            document.getElementById('e-solo-descripcion').textContent = local.descripcion ?? 'Sin descripción';
            document.getElementById('e-solo-telefono').textContent = local.telefono;

            const imgLogo = document.getElementById('e-logo-actual');
            if (local.logo) {
                imgLogo.src = `imagenes/${local.logo}`;
                imgLogo.classList.remove('oculto');
            } else {
                imgLogo.classList.add('oculto');
            }

            document.getElementById('e-ubicacion-texto').textContent =
                `${ubicacion.provincia}, ${ubicacion.canton}, ${ubicacion.distrito} — ${ubicacion.direccionExacta}` +
                (ubicacion.referencia ? ` (${ubicacion.referencia})` : '');

            panelLista.classList.add('oculto');
            panelDetalle.classList.remove('oculto');

            cargarProductosDelLocal(idLocal);

            document.getElementById('e-panel-actividad-local').classList.add('oculto');
        } catch (e) {
            mostrarMensaje('Error al cargar el detalle del local', 'error');
        }
    }

    let idLocalModalActual = null;
    let compraProductoPendiente = null;

    async function abrirModalLocal(idLocal) {
        idLocalModalActual = idLocal;

        try {
            const [rLocal, rProductos] = await Promise.all([
                fetch(`api/buscar_local.php?id=${idLocal}`),
                fetch(`api/listar_productos_local.php?idLocal=${idLocal}`)
            ]);
            const resLocal = await rLocal.json();
            const resProductos = await rProductos.json();

            if (!resLocal.exito) {
                mostrarMensaje(resLocal.mensaje || 'No se pudo cargar el local', 'error');
                return;
            }

            const { local, ubicacion } = resLocal;

            const imgLocal = document.getElementById('modal-local-imagen');
            const sinImagen = document.getElementById('modal-local-sin-imagen');
            if (local.logo) {
                imgLocal.src = `imagenes/${local.logo}`;
                imgLocal.classList.remove('oculto');
                sinImagen?.classList.add('oculto');
            } else {
                imgLocal.classList.add('oculto');
                sinImagen?.classList.remove('oculto');
            }

            document.getElementById('modal-local-categoria').textContent = local.tipoLocal ?? '';
            document.getElementById('modal-local-nombre').textContent = local.nombreLocal;
            document.getElementById('modal-local-descripcion').textContent = local.descripcion ?? '';
            document.getElementById('modal-local-telefono').textContent = local.telefono;
            document.getElementById('modal-local-ubicacion').textContent =
                `${ubicacion.provincia}, ${ubicacion.canton}, ${ubicacion.distrito} — ${ubicacion.direccionExacta}` +
                (ubicacion.referencia ? ` (${ubicacion.referencia})` : '');

            const contenedorProductos = document.getElementById('modal-local-productos-lista');
            if (!resProductos.exito || resProductos.productos.length === 0) {
                contenedorProductos.innerHTML = '<p class="ayuda">Este local todavía no tiene productos registrados.</p>';
            } else {
                contenedorProductos.innerHTML = '';
                resProductos.productos.forEach(producto => {
                    const precioHtml = producto.porcentajeDescuento
                        ? `<s>₡${producto.precioOriginal}</s> ₡${producto.precioFinal} <span class="etiqueta-tipo">-${producto.porcentajeDescuento}%</span>`
                        : `₡${producto.precioOriginal}`;

                    const botonAccion = usuarioSesionActual
                        ? `<button type="button" class="boton-comprar-modal" data-id="${producto.idProducto}" ${producto.agotado ? 'disabled' : ''}>Comprar</button>`
                        : `<button type="button" class="boton-secundario btn-login-para-comprar">Inicia sesión o regístrate para comprar</button>`;

                    const tarjeta = document.createElement('div');
                    tarjeta.className = 'tarjeta';
                    tarjeta.innerHTML = `
                        ${producto.imagen ? `<img src="imagenes/${producto.imagen}" alt="${escaparHtml(producto.nombre)}" class="imagen-producto">` : ''}
                        <h4>${escaparHtml(producto.nombre)}</h4>
                        <p>${producto.descripcion ?? ''}</p>
                        <p>${precioHtml}</p>
                        <p>${producto.agotado ? '<span class="ayuda error">Agotado</span>' : `Disponibles: ${producto.cantidadDisponible}`}</p>
                        ${botonAccion}
                        <button type="button" class="boton-secundario btn-comparar-producto">Comparar</button>
                    `;
                    contenedorProductos.appendChild(tarjeta);

                    const btnComprar = tarjeta.querySelector('.boton-comprar-modal');
                    if (btnComprar) {
                        btnComprar.addEventListener('click', () => {
                            mostrarMensaje('La compra directa estará disponible muy pronto 🛒', 'exito');
                        });
                    }

                    tarjeta.querySelector('.btn-comparar-producto')?.addEventListener('click', () => {
                        mostrarMensaje('Pronto podrás comparar productos 🔍', 'exito');
                    });

                    const btnLogin = tarjeta.querySelector('.btn-login-para-comprar');
                    if (btnLogin) {
                        btnLogin.addEventListener('click', () => {
                            cerrarModalLocal();
                            mostrarVistaLogin('vista-login');
                        });
                    }
                });
            }

            document.getElementById('modal-local-tab-detalles')?.classList.add('activo');
            document.getElementById('modal-local-tab-productos')?.classList.remove('activo');
            document.getElementById('modal-local-detalles-contenido')?.classList.remove('oculto');
            document.getElementById('modal-local-productos-contenido')?.classList.add('oculto');

            document.getElementById('modal-local').classList.remove('oculto');
        } catch (e) {
            mostrarMensaje('Error al cargar el local', 'error');
        }
    }

    document.getElementById('modal-local-tab-detalles')?.addEventListener('click', () => {
        document.getElementById('modal-local-tab-detalles').classList.add('activo');
        document.getElementById('modal-local-tab-productos').classList.remove('activo');
        document.getElementById('modal-local-detalles-contenido').classList.remove('oculto');
        document.getElementById('modal-local-productos-contenido').classList.add('oculto');
    });

    document.getElementById('modal-local-tab-productos')?.addEventListener('click', () => {
        document.getElementById('modal-local-tab-productos').classList.add('activo');
        document.getElementById('modal-local-tab-detalles').classList.remove('activo');
        document.getElementById('modal-local-productos-contenido').classList.remove('oculto');
        document.getElementById('modal-local-detalles-contenido').classList.add('oculto');
    });

    function cerrarModalLocal() {
        document.getElementById('modal-local').classList.add('oculto');
        idLocalModalActual = null;
    }

    document.getElementById('modal-local-cerrar')?.addEventListener('click', cerrarModalLocal);

    document.getElementById('modal-local')?.addEventListener('click', (evento) => {
        if (evento.target.id === 'modal-local') {
            cerrarModalLocal();
        }
    });

    function abrirModalProducto(producto) {
        const imgProducto = document.getElementById('modal-producto-imagen');
        const sinImagenProducto = document.getElementById('modal-producto-sin-imagen');
        if (producto.imagen) {
            imgProducto.src = `imagenes/${producto.imagen}`;
            imgProducto.classList.remove('oculto');
            sinImagenProducto?.classList.add('oculto');
        } else {
            imgProducto.classList.add('oculto');
            sinImagenProducto?.classList.remove('oculto');
        }

        const badge = document.getElementById('modal-producto-badge');
        if (badge) {
            if (producto.porcentajeDescuento) {
                badge.textContent = `-${producto.porcentajeDescuento}%`;
                badge.classList.remove('oculto');
            } else {
                badge.classList.add('oculto');
            }
        }

        const logoLocal = document.getElementById('modal-producto-logo-local');
        if (logoLocal) {
            if (producto.logoLocal) {
                logoLocal.src = `imagenes/${producto.logoLocal}`;
                logoLocal.classList.remove('oculto');
            } else {
                logoLocal.classList.add('oculto');
            }
        }
        const nombreLocalSpan = document.getElementById('modal-producto-nombre-local');
        if (nombreLocalSpan) {
            nombreLocalSpan.textContent = producto.nombreLocal ?? '';
        }

        document.getElementById('modal-producto-nombre').textContent = producto.nombre;
        document.getElementById('modal-producto-descripcion').textContent = producto.descripcion ?? '';

        const disponibles = document.getElementById('modal-producto-disponibles');
        if (disponibles) {
            disponibles.textContent = producto.agotado
                ? 'Agotado'
                : (producto.cantidadDisponible !== undefined ? `Disponibles: ${producto.cantidadDisponible}` : '');
        }

        const precioOriginal = document.getElementById('modal-producto-precio-original');
        const precioFinal = document.getElementById('modal-producto-precio-final');
        if (precioOriginal && precioFinal) {
            if (producto.porcentajeDescuento) {
                precioOriginal.textContent = `₡${producto.precioOriginal}`;
                precioOriginal.classList.remove('oculto');
                precioFinal.textContent = `₡${producto.precioFinal}`;
            } else {
                precioOriginal.classList.add('oculto');
                precioFinal.textContent = `₡${producto.precioOriginal}`;
            }
        }

                           

        

        const btnComprarModalProducto = document.getElementById('modal-producto-comprar');
        if (btnComprarModalProducto) {
            btnComprarModalProducto.disabled = !!producto.agotado;
            btnComprarModalProducto.textContent = usuarioSesionActual
                ? 'Comprar'
                : 'Inicia sesión o regístrate para comprar';

            btnComprarModalProducto.onclick = () => {
                if (usuarioSesionActual) {
                    mostrarMensaje('La compra directa estará disponible muy pronto 🛒', 'exito');
                } else {
                    compraProductoPendiente = producto;
                    cerrarModalProducto();
                    mostrarVistaLogin('vista-login');
                }
            };
        }

        document.getElementById('modal-producto').classList.remove('oculto');
    }

    function cerrarModalProducto() {
        document.getElementById('modal-producto').classList.add('oculto');
    }

    document.getElementById('modal-producto-cerrar')?.addEventListener('click', cerrarModalProducto);

    document.getElementById('modal-producto')?.addEventListener('click', (evento) => {
        if (evento.target.id === 'modal-producto') {
            cerrarModalProducto();
        }
    });

    async function cargarProductosDelLocal(idLocal) {
        const contenedor = document.getElementById('e-productos-lista');
        contenedor.innerHTML = '<p>Cargando productos...</p>';

        try {
            const r = await fetch(`api/listar_productos_local.php?idLocal=${idLocal}`);
            const res = await r.json();

            if (!res.exito || res.productos.length === 0) {
                contenedor.innerHTML = '<p>Este local todavía no tiene productos registrados.</p>';
                return;
            }

            contenedor.innerHTML = '';

            res.productos.forEach(producto => {
                const tarjeta = document.createElement('div');
                tarjeta.className = 'tarjeta';

                const precioHtml = producto.porcentajeDescuento
                    ? `<s>₡${producto.precioOriginal}</s> ₡${producto.precioFinal} <span class="etiqueta-tipo">-${producto.porcentajeDescuento}%</span>`
                    : `₡${producto.precioOriginal}`;

                tarjeta.innerHTML = `
                    ${producto.imagen ? `<img src="imagenes/${producto.imagen}" alt="${producto.nombre}" class="imagen-producto">` : ''}
                    <h4>${producto.nombre} ${producto.compartido ? '<span class="etiqueta-tipo">Compartido</span>' : ''}</h4>
                    <p>${producto.descripcion ?? ''}</p>
                    <p>${precioHtml}</p>
                    <p>${producto.agotado ? '<span class="ayuda error">Agotado</span>' : `Disponibles: ${producto.cantidadDisponible}`}</p>
                `;
                contenedor.appendChild(tarjeta);
            });
        } catch (e) {
            contenedor.innerHTML = '<p>Error al cargar los productos.</p>';
        }
    }

     let formLocalTieneCambios = false;
    document.getElementById('form-editar-local')?.addEventListener('input', () => {
        formLocalTieneCambios = true;
    });

    document.getElementById('btn-volver-lista')?.addEventListener('click', () => {
        if (!formLocalTieneCambios) {
            mostrarListaLocales();
            return;
        }

        Swal.fire({
            title: '¿Salir sin guardar?',
            text: 'Tienes cambios en este local que todavía no se han guardado. Se perderán si sales ahora.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, salir sin guardar',
            cancelButtonText: 'Seguir editando',
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#8E7CC3'
        }).then((resultado) => {
            if (resultado.isConfirmed) {
                formLocalTieneCambios = false;
                mostrarListaLocales();
            }
        });
    });

    document.getElementById('form-editar-local')?.addEventListener('submit', (evento) => {
        evento.preventDefault();

        Swal.fire({
            title: '¿Guardar estos cambios?',
            text: 'Se van a actualizar los datos de este local.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Seguir editando',
            confirmButtonColor: '#8E7CC3',
            cancelButtonColor: '#6B7280'
        }).then(async (resultado) => {
            if (!resultado.isConfirmed) return;

            const datos = new FormData();
            datos.append('idLocal', document.getElementById('e-idLocal').value);
            datos.append('nombreTipoLocal', document.getElementById('e-tipoLocal').value);
            datos.append('nombreLocal', document.getElementById('e-nombreLocal').value);
            datos.append('descripcion', document.getElementById('e-descripcion').value);
            datos.append('telefono', document.getElementById('e-telefono').value);

            const archivoLogo = document.getElementById('e-logo').files[0];
            if (archivoLogo) {
                datos.append('logo', archivoLogo);
            }

            try {
                const r = await fetch('api/editar_local.php', {
                    method: 'POST',
                    body: datos
                });
                const res = await r.json();

                mostrarMensaje(res.mensaje, res.exito ? 'exito' : 'error');

                if (res.exito) {
                    formLocalTieneCambios = false;
                    mostrarListaLocales();
                    cargarLocales();
                }
            } catch (e) {
                mostrarMensaje('No se pudo conectar con el servidor para guardar los cambios del local. Revisa tu conexión e intenta de nuevo.', 'error');
            }
        });
    });

    const selectIdLocalProducto = document.getElementById('p-idLocal');
    async function cargarLocalesComercianteParaProducto() {
        selectIdLocalProducto.innerHTML = '<option value="">Cargando...</option>';

        try {
            const r = await fetch('api/listar_locales_comerciante.php');
            const res = await r.json();

            if (!res.exito || res.locales.length === 0) {
                selectIdLocalProducto.innerHTML = '<option value="">No tienes locales registrados todavía</option>';
                return;
            }

            selectIdLocalProducto.innerHTML = '<option value="">Selecciona un local...</option>';
            res.locales.forEach(local => {
                const opcion = document.createElement('option');
                opcion.value = local.idLocal;
                opcion.textContent = local.nombreLocal;
                selectIdLocalProducto.appendChild(opcion);
            });
        } catch (e) {
            selectIdLocalProducto.innerHTML = '<option value="">Error al cargar tus locales</option>';
        }
    }

    activarAutocompletadoTipo(
        document.getElementById('p-tipoProducto'),
        document.getElementById('p-tipo-sugerencias'),
        'api/buscar_tipos_producto.php'
    );

    activarAlertaSimilares(
        document.getElementById('p-nombre'),
        document.getElementById('p-similares'),
        'api/buscar_productos_similares.php',
        (item) => `${item.nombre} — ${Math.round(item.similitud)}% (en ${item.locales.map(l => l.nombreLocal).join(', ')})`
    );

    document.getElementById('form-producto')?.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        if (!selectIdLocalProducto.value) {
            mostrarMensaje('Selecciona un local antes de continuar', 'error');
            return;
        }

        const datos = new FormData();
        datos.append('idLocal', selectIdLocalProducto.value);
        datos.append('nombreTipoProducto', document.getElementById('p-tipoProducto').value);
        datos.append('nombre', document.getElementById('p-nombre').value);
        datos.append('precioOriginal', document.getElementById('p-precio').value);
        datos.append('porcentajeDescuento', document.getElementById('p-descuento').value);
        datos.append('descripcion', document.getElementById('p-descripcion').value);
        datos.append('cantidadDisponible', document.getElementById('p-cantidad').value);

        const esTemporal = document.querySelector('input[name="p-duracion"]:checked')?.value === 'temporal';
        if (esTemporal) {
            const fecha = document.getElementById('p-fechaVencimiento').value;
            if (!fecha) {
                mostrarMensaje('Indica hasta cuándo estará disponible el producto', 'error');
                return;
            }
            if (new Date(fecha) <= new Date()) {
                mostrarMensaje('La fecha de disponibilidad debe ser posterior a este momento', 'error');
                return;
            }
            datos.append('fechaVencimiento', fecha);
        }

        const archivoImagen = document.getElementById('p-imagen').files[0];
        if (archivoImagen) {
            datos.append('imagen', archivoImagen);
        }

        try {
            const r = await fetch('api/registrar_producto.php', {
                method: 'POST',
                body: datos
            });
            const res = await r.json();

            mostrarMensaje(res.mensaje, res.exito ? 'exito' : 'error');

            if (res.exito) {
                evento.target.reset();
            }
        } catch (e) {
            mostrarMensaje('Error de conexión con el servidor', 'error');
        }
    });

    const panelEditarProducto = document.getElementById('panel-editar-producto');
    let idLocalProductoEditando = null;

    activarAutocompletadoTipo(
        document.getElementById('ep-tipoProducto'),
        document.getElementById('ep-tipo-sugerencias'),
        'api/buscar_tipos_producto.php'
    );

    async function abrirEditarProducto(idProducto, idLocal) {
        try {
            const r = await fetch(`api/buscar_producto.php?id=${idProducto}`);
            const res = await r.json();

            if (!res.exito) {
                mostrarMensaje(res.mensaje || 'No se pudo cargar el producto', 'error');
                return;
            }

            const p = res.producto;
            idLocalProductoEditando = idLocal;

            document.getElementById('ep-idProducto').value = p.idProducto;
            document.getElementById('ep-tipoProducto').value = p.tipoProducto ?? '';
            document.getElementById('ep-nombre').value = p.nombre;
            document.getElementById('ep-descripcion').value = p.descripcion ?? '';
            document.getElementById('ep-precio').value = p.precioOriginal;
            document.getElementById('ep-descuento').value = p.porcentajeDescuento ?? '';
            document.getElementById('ep-cantidad').value = p.cantidadDisponible;

            panelDetalle.classList.add('oculto');
            panelEditarProducto.classList.remove('oculto');

            cargarOtrosLocalesDelProducto(p.idProducto);
        } catch (e) {
            mostrarMensaje('Error al cargar el producto', 'error');
        }
    }

    document.getElementById('btn-cerrar-editar-producto')?.addEventListener('click', () => {
        panelEditarProducto.classList.add('oculto');
        panelDetalle.classList.remove('oculto');
    });

    document.getElementById('form-editar-producto')?.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const datos = new FormData();
        datos.append('idProducto', document.getElementById('ep-idProducto').value);
        datos.append('nombreTipoProducto', document.getElementById('ep-tipoProducto').value);
        datos.append('nombre', document.getElementById('ep-nombre').value);
        datos.append('precioOriginal', document.getElementById('ep-precio').value);
        datos.append('porcentajeDescuento', document.getElementById('ep-descuento').value);
        datos.append('descripcion', document.getElementById('ep-descripcion').value);
        datos.append('cantidadDisponible', document.getElementById('ep-cantidad').value);

        const esTemporalEdicion = document.querySelector('input[name="ep-duracion"]:checked')?.value === 'temporal';
        if (esTemporalEdicion) {
            const fechaEdicion = document.getElementById('ep-fechaVencimiento').value;
            if (!fechaEdicion) {
                mostrarMensaje('Indica hasta cuándo estará disponible el producto', 'error');
                return;
            }
            if (new Date(fechaEdicion) <= new Date()) {
                mostrarMensaje('La fecha de disponibilidad debe ser posterior a este momento', 'error');
                return;
            }
            datos.append('fechaVencimiento', fechaEdicion);
        }

        const archivoImagen = document.getElementById('ep-imagen').files[0];
        if (archivoImagen) {
            datos.append('imagen', archivoImagen);
        }

        try {
            const r = await fetch('api/editar_producto.php', {
                method: 'POST',
                body: datos
            });
            const res = await r.json();

            mostrarMensaje(res.mensaje, res.exito ? 'exito' : 'error');

            if (res.exito) {
                panelEditarProducto.classList.add('oculto');
                panelDetalle.classList.remove('oculto');
                cargarProductosDelLocal(idLocalProductoEditando);
            }
        } catch (e) {
            mostrarMensaje('Error de conexión con el servidor', 'error');
        }
    });

    const panelListaComerciantes = document.getElementById('panel-lista-comerciantes');
    const panelDetalleComerciante = document.getElementById('panel-detalle-comerciante');

    function mostrarListaComerciantes() {
        panelDetalleComerciante.classList.add('oculto');
        panelListaComerciantes.classList.remove('oculto');
    }

      async function cargarComerciantes() {
        const contenedor = document.getElementById('lista-comerciantes');
        contenedor.innerHTML = '<p class="ayuda">Cargando...</p>';

        const termino = document.getElementById('com-admin-buscar')?.value.trim() || '';
        const estado = document.getElementById('com-admin-filtro-estado')?.value || 'activos';

        try {
            const parametros = new URLSearchParams({ termino, estado });
            const r = await fetch(`api/listar_comerciantes.php?${parametros.toString()}`);
            const res = await r.json();

            if (!res.exito || res.comerciantes.length === 0) {
                contenedor.innerHTML = '<p class="ayuda">No se encontraron comerciantes.</p>';
                return;
            }

            contenedor.innerHTML = '';

            res.comerciantes.forEach(c => {
                const tarjeta = document.createElement('div');
                tarjeta.className = 'tarjeta tarjeta-clic tarjeta-persona';
                tarjeta.innerHTML = `
                    ${c.fotoPerfil
                        ? `<img src="imagenes/${c.fotoPerfil}" alt="${escaparHtml(c.nombre)}" class="avatar-persona">`
                        : `<div class="avatar-persona avatar-iniciales">${escaparHtml(obtenerIniciales(c.nombre))}</div>`}
                    <h4>${escaparHtml(c.nombre)}</h4>
                    <p class="etiqueta-tipo">${escaparHtml(c.alias)}</p>
                    <span class="${c.activo ? 'etiqueta-activo' : 'etiqueta-inactivo'}">${c.activo ? 'Activo' : 'Inactivo'}</span>
                    <p class="ayuda">✉️ ${escaparHtml(c.correo)}</p>
                `;
                tarjeta.addEventListener('click', () => abrirDetalleComerciante(c.idComerciante));
                contenedor.appendChild(tarjeta);
            });
        } catch (e) {
            contenedor.innerHTML = '<p class="ayuda error">Error al cargar los comerciantes.</p>';
        }
    }

    document.getElementById('com-admin-buscar')?.addEventListener('input', debounce(cargarComerciantes, 400));
    document.getElementById('com-admin-filtro-estado')?.addEventListener('change', cargarComerciantes);

    async function abrirDetalleComerciante(idComerciante) {
        try {
            const r = await fetch(`api/buscar_comerciante.php?id=${idComerciante}`);
            const res = await r.json();

            if (!res.exito) {
                mostrarMensaje(res.mensaje || 'No se pudo cargar el comerciante', 'error');
                return;
            }

            const c = res.comerciante;

            document.getElementById('dc-idComerciante').value = c.idComerciante;
            document.getElementById('dc-solo-nombre').textContent = c.nombre;
            document.getElementById('dc-solo-alias').textContent = c.alias;
            document.getElementById('dc-solo-correo').textContent = c.correo;
            document.getElementById('dc-identificacion').textContent = c.numeroIdentificacion;
            document.getElementById('dc-solo-estado').textContent = c.activo ? 'Activo' : 'Inactivo';

            const imgFoto = document.getElementById('dc-foto-actual');
            if (imgFoto) {
                if (c.fotoPerfil) {
                    imgFoto.src = `imagenes/${c.fotoPerfil}`;
                    imgFoto.classList.remove('oculto');
                } else {
                    imgFoto.classList.add('oculto');
                }
            }

            const btnDesactivar = document.getElementById('btn-desactivar-comerciante');
            const btnActivar = document.getElementById('btn-activar-comerciante');

            if (c.activo) {
                btnDesactivar.classList.remove('oculto');
                btnActivar.classList.add('oculto');
            } else {
                btnDesactivar.classList.add('oculto');
                btnActivar.classList.remove('oculto');
            }

            panelListaComerciantes.classList.add('oculto');
            panelDetalleComerciante.classList.remove('oculto');
        } catch (e) {
            mostrarMensaje('Error al cargar el detalle del comerciante', 'error');
        }
    }

    document.getElementById('btn-volver-comerciantes')?.addEventListener('click', mostrarListaComerciantes);

    document.getElementById('btn-desactivar-comerciante')?.addEventListener('click', async () => {
        const idComerciante = document.getElementById('dc-idComerciante').value;
        const nombre = document.getElementById('dc-solo-nombre').textContent;

        const resultado = await Swal.fire({
            title: '¿Desactivar comerciante?',
            text: `¿Seguro que querés desactivar a "${nombre}"? Sus locales seguirán existiendo, pero no podrá ingresar más.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, desactivar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545'
        });
        if (!resultado.isConfirmed) {
            return;
        }

        try {
            const r = await fetch('api/eliminar_comerciante.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idComerciante })
            });
            const res = await r.json();

            mostrarMensaje(res.mensaje, res.exito ? 'exito' : 'error');

            if (res.exito) {
                mostrarListaComerciantes();
                cargarComerciantes();
            }
        } catch (e) {
            mostrarMensaje('Error al eliminar el comerciante', 'error');
        }
    });

    document.getElementById('btn-activar-comerciante')?.addEventListener('click', async () => {
        const idComerciante = document.getElementById('dc-idComerciante').value;

        try {
            const r = await fetch('api/activar_comerciante.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idComerciante })
            });
            const res = await r.json();

            mostrarMensaje(res.mensaje, res.exito ? 'exito' : 'error');

            if (res.exito) {
                mostrarListaComerciantes();
                cargarComerciantes();
            }
        } catch (e) {
            mostrarMensaje('No se pudo conectar con el servidor para activar este comerciante. Revisa tu conexión e intenta de nuevo.', 'error');
        }
    });

    const panelListaClientes = document.getElementById('panel-lista-clientes');
    const panelDetalleCliente = document.getElementById('panel-detalle-cliente');

    function mostrarListaClientes() {
        panelDetalleCliente.classList.add('oculto');
        panelListaClientes.classList.remove('oculto');
    }

        function obtenerIniciales(nombre) {
        const partes = (nombre || '').trim().split(/\s+/);
        const primera = partes[0]?.[0] ?? '';
        const segunda = partes[1]?.[0] ?? '';
        return (primera + segunda).toUpperCase();
    }

    async function cargarClientes() {
        const contenedor = document.getElementById('lista-clientes');
        contenedor.innerHTML = '<p class="ayuda">Cargando...</p>';

        const termino = document.getElementById('cl-admin-buscar')?.value.trim() || '';
        const estado = document.getElementById('cl-admin-filtro-estado')?.value || 'activos';

        try {
            const parametros = new URLSearchParams({ termino, estado });
            const r = await fetch(`api/listar_clientes.php?${parametros.toString()}`);
            const res = await r.json();

            if (!res.exito || res.clientes.length === 0) {
                contenedor.innerHTML = '<p class="ayuda">No se encontraron clientes.</p>';
                return;
            }

            contenedor.innerHTML = '';

            res.clientes.forEach(c => {
                const tarjeta = document.createElement('div');
                tarjeta.className = 'tarjeta tarjeta-clic tarjeta-persona';
                tarjeta.innerHTML = `
                    ${c.fotoPerfil
                        ? `<img src="imagenes/${c.fotoPerfil}" alt="${escaparHtml(c.nombreCompleto)}" class="avatar-persona">`
                        : `<div class="avatar-persona avatar-iniciales">${escaparHtml(obtenerIniciales(c.nombreCompleto))}</div>`}
                    <h4>${escaparHtml(c.nombreCompleto)}</h4>
                    <span class="${c.activo ? 'etiqueta-activo' : 'etiqueta-inactivo'}">${c.activo ? 'Activo' : 'Inactivo'}</span>
                    <p class="ayuda">✉️ ${escaparHtml(c.correo)}</p>
                `;
                tarjeta.addEventListener('click', () => abrirDetalleCliente(c.idCliente));
                contenedor.appendChild(tarjeta);
            });
        } catch (e) {
            contenedor.innerHTML = '<p class="ayuda error">Error al cargar los clientes.</p>';
        }
    }
    document.getElementById('cl-admin-buscar')?.addEventListener('input', debounce(cargarClientes, 400));
    document.getElementById('cl-admin-filtro-estado')?.addEventListener('change', cargarClientes);

    async function abrirDetalleCliente(idCliente) {
        try {
            const r = await fetch(`api/buscar_cliente.php?id=${idCliente}`);
            const res = await r.json();

            if (!res.exito) {
                mostrarMensaje(res.mensaje || 'No se pudo cargar el cliente', 'error');
                return;
            }

            const c = res.cliente;
            const u = res.ubicacion;

            document.getElementById('dcl-idCliente').value = c.idCliente;
            document.getElementById('dcl-solo-nombre').textContent = c.nombreCompleto;
            document.getElementById('dcl-solo-correo').textContent = c.correo;
            document.getElementById('dcl-identificacion').textContent = c.numeroIdentificacion;
            document.getElementById('dcl-direccion').textContent =
                u.direccionExacta + (u.referencia ? ` (${u.referencia})` : '');
            document.getElementById('dcl-solo-estado').textContent = c.activo ? 'Activo' : 'Inactivo';

            const imgFoto = document.getElementById('dcl-foto-actual');
            if (imgFoto) {
                if (c.fotoPerfil) {
                    imgFoto.src = `imagenes/${c.fotoPerfil}`;
                    imgFoto.classList.remove('oculto');
                } else {
                    imgFoto.classList.add('oculto');
                }
            }

            const btnDesactivarCl = document.getElementById('btn-desactivar-cliente');
            const btnActivarCl = document.getElementById('btn-activar-cliente');

            if (c.activo) {
                btnDesactivarCl.classList.remove('oculto');
                btnActivarCl.classList.add('oculto');
            } else {
                btnDesactivarCl.classList.add('oculto');
                btnActivarCl.classList.remove('oculto');
            }

            panelListaClientes.classList.add('oculto');
            panelDetalleCliente.classList.remove('oculto');
        } catch (e) {
            mostrarMensaje('Error al cargar el detalle del cliente', 'error');
        }
    }

    document.getElementById('btn-volver-clientes')?.addEventListener('click', mostrarListaClientes);

    document.getElementById('form-editar-cliente')?.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const datos = new FormData();
        datos.append('idCliente', document.getElementById('dcl-idCliente').value);
        datos.append('nombreCompleto', document.getElementById('dcl-nombreCompleto')?.value ?? '');
        datos.append('correo', document.getElementById('dcl-correo')?.value ?? '');
        datos.append('password', document.getElementById('dcl-password')?.value ?? '');

        const archivoFoto = document.getElementById('dcl-fotoPerfil')?.files[0];
        if (archivoFoto) {
            datos.append('fotoPerfil', archivoFoto);
        }

        try {
            const r = await fetch('api/editar_cliente.php', {
                method: 'POST',
                body: datos
            });
            const res = await r.json();

            mostrarMensaje(res.mensaje, res.exito ? 'exito' : 'error');

            if (res.exito) {
                mostrarListaClientes();
                cargarClientes();
            }
        } catch (e) {
            mostrarMensaje('Error al editar el cliente', 'error');
        }
    });

    document.getElementById('btn-desactivar-cliente')?.addEventListener('click', async () => {
        const idCliente = document.getElementById('dcl-idCliente').value;
        const nombre = document.getElementById('dcl-solo-nombre').textContent;

        const resultado = await Swal.fire({
            title: '¿Desactivar cliente?',
            text: `¿Seguro que querés desactivar a "${nombre}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, desactivar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545'
        });
        if (!resultado.isConfirmed) {
            return;
        }

        try {
            const r = await fetch('api/eliminar_cliente.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idCliente })
            });
            const res = await r.json();

            mostrarMensaje(res.mensaje, res.exito ? 'exito' : 'error');

            if (res.exito) {
                mostrarListaClientes();
                cargarClientes();
            }
        } catch (e) {
            mostrarMensaje('Error de conexión con el servidor', 'error');
        }
    });

    document.getElementById('btn-activar-cliente')?.addEventListener('click', async () => {
        const idCliente = document.getElementById('dcl-idCliente').value;

        try {
            const r = await fetch('api/activar_cliente.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idCliente })
            });
            const res = await r.json();

            mostrarMensaje(res.mensaje, res.exito ? 'exito' : 'error');

            if (res.exito) {
                mostrarListaClientes();
                cargarClientes();
            }
        } catch (e) {
            mostrarMensaje('Error de conexión con el servidor', 'error');
        }
    });

    const reglasIdentificacion = {
        Cedula: { patron: /^\d{9}$/, maxlength: 9, placeholder: 'Ej: 118760512', ayuda: '9 dígitos numéricos' },
        DIMEX: { patron: /^\d{11,12}$/, maxlength: 12, placeholder: 'Ej: 155812345678', ayuda: '11 o 12 dígitos numéricos' },
        Pasaporte: { patron: /^[A-Za-z0-9]{6,15}$/, maxlength: 15, placeholder: 'Ej: AB1234567', ayuda: 'Entre 6 y 15 caracteres, letras y números' }
    };

       function activarValidacionIdentificacion(selectTipoEl, inputNumeroEl, mensajeFormatoEl) {
        if (!selectTipoEl || !inputNumeroEl || !mensajeFormatoEl) return;

        function aplicarReglasDelTipo() {
            const regla = reglasIdentificacion[selectTipoEl.value];
            if (!regla) return;

            inputNumeroEl.maxLength = regla.maxlength;
            inputNumeroEl.placeholder = regla.placeholder;
            mensajeFormatoEl.textContent = regla.ayuda;
            mensajeFormatoEl.className = 'ayuda';

            if (selectTipoEl.value === 'Cedula' || selectTipoEl.value === 'DIMEX') {
                inputNumeroEl.setAttribute('inputmode', 'numeric');
            } else {
                inputNumeroEl.removeAttribute('inputmode');
            }
        }

        function validarFormato() {
            const regla = reglasIdentificacion[selectTipoEl.value];
            if (!regla || inputNumeroEl.value.trim() === '') return;

            const valido = regla.patron.test(inputNumeroEl.value.trim());
            if (!valido) {
                mensajeFormatoEl.textContent = `Formato inválido: se espera ${regla.ayuda.toLowerCase()}`;
                mensajeFormatoEl.className = 'ayuda error';
            }
        }

        selectTipoEl.addEventListener('change', () => {
            inputNumeroEl.value = '';
            aplicarReglasDelTipo();
        });

        inputNumeroEl.addEventListener('blur', validarFormato);

        aplicarReglasDelTipo();
    }

    activarValidacionIdentificacion(
        document.getElementById('c-tipoIdentificacion'),
        document.getElementById('c-numeroIdentificacion'),
        document.getElementById('c-identificacion-msg')
    );


    const selectProvinciaFiltro = document.getElementById('f-provincia');
    const selectCantonFiltro = document.getElementById('f-canton');
    const selectDistritoFiltro = document.getElementById('f-distrito');

    activarCascadaUbicacion(selectProvinciaFiltro, selectCantonFiltro, selectDistritoFiltro);

        function confirmarEliminarLocal(idLocal, nombreLocal, tarjeta) {
        Swal.fire({
            title: `¿Eliminar "${nombreLocal}"?`,
            text: 'Este local se marcará como inactivo y dejará de verse en la app.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#8E7CC3'
        }).then(async (resultado) => {
            if (!resultado.isConfirmed) return;

            try {
                const r = await fetch('api/eliminar_local.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ idLocal })
                });
                const res = await r.json();

                if (res.exito) {
                    tarjeta.remove();
                    mostrarMensaje('Local eliminado correctamente', 'exito');
                } else {
                    mostrarMensaje(res.mensaje || 'No se pudo eliminar el local', 'error');
                }
            } catch (e) {
                mostrarMensaje('Error al eliminar el local', 'error');
            }
        });
    }

    async function cargarLocales() {
        const contenedor = document.getElementById('lista-locales');
        contenedor.innerHTML = '<p>Cargando...</p>';

        const parametros = new URLSearchParams();

        const nombre = document.getElementById('f-nombre').value.trim();
        if (nombre) parametros.set('nombre', nombre);

        const idProvincia = document.getElementById('f-provincia').value;
        if (idProvincia) parametros.set('idProvincia', idProvincia);

        const idCanton = document.getElementById('f-canton').value;
        if (idCanton) parametros.set('idCanton', idCanton);

        const idDistrito = document.getElementById('f-distrito').value;
        if (idDistrito) parametros.set('idDistrito', idDistrito);

        try {
            const r = await fetch(`api/listar_locales.php?${parametros.toString()}`);
            const res = await r.json();

            if (!res.exito || res.locales.length === 0) {
                if (nombre) {
                    await mostrarSugerenciasBusqueda(nombre, contenedor);
                } else {
                    const hayFiltros = parametros.toString() !== '';
                    contenedor.innerHTML = hayFiltros
                        ? '<p>No se encontraron locales con esos filtros.</p>'
                        : '<p>No hay locales registrados todavía.</p>';
                }
                return;
            }

            contenedor.innerHTML = '';

            res.locales.forEach(local => {
                const esAdmin = usuarioSesionActual?.tipo === 'SuperAdmin';

                const tarjeta = document.createElement('div');
                tarjeta.className = 'tarjeta tarjeta-clic';
                if (esAdmin) tarjeta.classList.add('tarjeta-con-borrar');
                                tarjeta.innerHTML = `
                    ${esAdmin ? `<button type="button" class="tarjeta-local-btn-eliminar" aria-label="Eliminar local"><i data-lucide="trash-2"></i></button>` : ''}
                    ${local.logo
                        ? `<img src="imagenes/${local.logo}" alt="${local.nombreLocal}" class="imagen-producto">`
                        : `<div class="imagen-producto imagen-producto-vacia"><i data-lucide="store"></i></div>`}
                    <h3>${local.nombreLocal}</h3>
                    <p class="etiqueta-tipo">${local.tipoLocal ?? ''}</p>
                    <p>${local.descripcion ?? ''}</p>
                    <p>📞 ${local.telefono}</p>
                `;
                tarjeta.addEventListener('click', () => abrirDetalleLocal(local.idLocal));

                if (esAdmin) {
                    tarjeta.querySelector('.tarjeta-local-btn-eliminar').addEventListener('click', (evento) => {
                        evento.stopPropagation();
                        confirmarEliminarLocal(local.idLocal, local.nombreLocal, tarjeta);
                    });
                }
                contenedor.appendChild(tarjeta);
            });

            if (window.lucide) lucide.createIcons();
        } catch (e) {
            contenedor.innerHTML = '<p>Error al cargar los locales.</p>';
        }
    }

    const buscarLocalesDebounced = debounce(cargarLocales, 400);

    document.getElementById('f-nombre')?.addEventListener('input', buscarLocalesDebounced);
    selectProvinciaFiltro.addEventListener('change', cargarLocales);
    selectCantonFiltro.addEventListener('change', cargarLocales);
    selectDistritoFiltro.addEventListener('change', cargarLocales);

    document.getElementById('btn-limpiar-filtros')?.addEventListener('click', () => {
        document.getElementById('f-nombre').value = '';
        selectProvinciaFiltro.value = '';
        selectCantonFiltro.innerHTML = '<option value="">Todos los cantones</option>';
        selectCantonFiltro.disabled = true;
        selectDistritoFiltro.innerHTML = '<option value="">Todos los distritos</option>';
        selectDistritoFiltro.disabled = true;
        cargarLocales();
    });

    async function cargarOtrosLocalesDelProducto(idProducto) {
        const contenedor = document.getElementById('ep-otros-locales-lista');
        contenedor.innerHTML = '<p class="ayuda">Cargando...</p>';

        try {
            const r = await fetch(`api/listar_locales_producto.php?idProducto=${idProducto}`);
            const res = await r.json();

            if (!res.exito || res.locales.length === 0) {
                contenedor.innerHTML = '<p class="ayuda">Por ahora solo se ofrece en su local original.</p>';
                return;
            }

            contenedor.innerHTML = '';

            res.locales.forEach(loc => {
                const fila = document.createElement('div');
                fila.className = 'fila-relacion';
                fila.innerHTML = `
                    <span>${loc.nombreLocal}</span>
                    <button type="button" class="boton-peligro btn-quitar-local-producto" data-id="${loc.idProductoLocal}">Quitar</button>
                `;
                fila.querySelector('.btn-quitar-local-producto').addEventListener('click', async () => {
                    try {
                        const rq = await fetch('api/quitar_producto_local.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ idProductoLocal: loc.idProductoLocal })
                        });
                        const resq = await rq.json();
                        if (resq.exito) {
                            cargarOtrosLocalesDelProducto(idProducto);
                        } else {
                            mostrarMensaje('No se pudo quitar el local', 'error');
                        }
                    } catch (e) {
                        mostrarMensaje('Error de conexión con el servidor', 'error');
                    }
                });
                contenedor.appendChild(fila);
            });
        } catch (e) {
            contenedor.innerHTML = '<p class="ayuda error">Error al cargar los locales.</p>';
        }
    }

    document.getElementById('btn-agregar-local-producto')?.addEventListener('click', async () => {
        const idProducto = document.getElementById('ep-idProducto').value;
        const nombreLocal = document.getElementById('ep-agregar-local-nombre').value.trim();
        const mensaje = document.getElementById('ep-agregar-local-msg');

        if (!nombreLocal) return;

        mensaje.textContent = 'Buscando local...';
        mensaje.className = 'ayuda';

        try {
            const rBuscar = await fetch(`api/buscar_local_por_nombre.php?nombre=${encodeURIComponent(nombreLocal)}`);
            const resBuscar = await rBuscar.json();

            if (!resBuscar.encontrado) {
                mensaje.textContent = 'No existe un local con ese nombre exacto';
                mensaje.className = 'ayuda error';
                return;
            }

            const rAgregar = await fetch('api/agregar_producto_local.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idProducto, idLocal: resBuscar.idLocal })
            });
            const resAgregar = await rAgregar.json();

            mensaje.textContent = resAgregar.mensaje;
            mensaje.className = resAgregar.exito ? 'ayuda exito' : 'ayuda error';

            if (resAgregar.exito) {
                document.getElementById('ep-agregar-local-nombre').value = '';
                cargarOtrosLocalesDelProducto(idProducto);
            }
        } catch (e) {
            mensaje.textContent = 'Error de conexión con el servidor';
            mensaje.className = 'ayuda error';
        }
    });

    async function cargarLocalesQueSigueCliente(idCliente, idContenedor = 'dcl-locales-lista') {
        const contenedor = document.getElementById(idContenedor);
        if (!contenedor) return;
        contenedor.innerHTML = '<p class="ayuda">Cargando...</p>';

        try {
            const r = await fetch(`api/listar_locales_cliente.php?idCliente=${idCliente}`);
            const res = await r.json();

            if (!res.exito || res.locales.length === 0) {
                contenedor.innerHTML = '<p class="ayuda">Este cliente todavía no sigue ningún local.</p>';
                return;
            }

            contenedor.innerHTML = '';

            res.locales.forEach(loc => {
                const fila = document.createElement('div');
                fila.className = 'fila-relacion';
                fila.innerHTML = `
                    <span>${loc.nombreLocal}</span>
                    <button type="button" class="boton-peligro btn-dejar-seguir-local" data-id="${loc.idClienteLocal}">Dejar de seguir</button>
                `;
                fila.querySelector('.btn-dejar-seguir-local').addEventListener('click', async () => {
                    try {
                        const rq = await fetch('api/dejar_seguir_local_cliente.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ idClienteLocal: loc.idClienteLocal })
                        });
                        const resq = await rq.json();
                        if (resq.exito) {
                            cargarLocalesQueSigueCliente(idCliente, idContenedor);
                        } else {
                            mostrarMensaje('No se pudo quitar el local', 'error');
                        }
                    } catch (e) {
                        mostrarMensaje('Error de conexión con el servidor', 'error');
                    }
                });
                contenedor.appendChild(fila);
            });
        } catch (e) {
            contenedor.innerHTML = '<p class="ayuda error">Error al cargar los locales.</p>';
        }
    }

    document.getElementById('btn-seguir-local')?.addEventListener('click', async () => {
        const idCliente = document.getElementById('dcl-idCliente').value;
        const nombreLocal = document.getElementById('dcl-agregar-local-nombre')?.value.trim();
        const mensaje = document.getElementById('dcl-seguir-local-msg');

        if (!nombreLocal || !mensaje) return;

        mensaje.textContent = 'Buscando local...';
        mensaje.className = 'ayuda';

        try {
            const rBuscar = await fetch(`api/buscar_local_por_nombre.php?nombre=${encodeURIComponent(nombreLocal)}`);
            const resBuscar = await rBuscar.json();

            if (!resBuscar.encontrado) {
                mensaje.textContent = 'No existe un local con ese nombre exacto';
                mensaje.className = 'ayuda error';
                return;
            }

            const rSeguir = await fetch('api/seguir_local_cliente.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idCliente, idLocal: resBuscar.idLocal })
            });
            const resSeguir = await rSeguir.json();

            mensaje.textContent = resSeguir.mensaje;
            mensaje.className = resSeguir.exito ? 'ayuda exito' : 'ayuda error';

            if (resSeguir.exito) {
                document.getElementById('dcl-agregar-local-nombre').value = '';
                cargarLocalesQueSigueCliente(idCliente);
            }
        } catch (e) {
            mensaje.textContent = 'Error de conexión con el servidor';
            mensaje.className = 'ayuda error';
        }
    });


    function escaparHtml(texto) {
        return String(texto ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function formatearFecha(fecha) {
        if (!fecha) return 'Fecha no disponible';
        const valor = new Date(fecha.replace(' ', 'T'));
        return Number.isNaN(valor.getTime()) ? fecha : valor.toLocaleString('es-CR');
    }

    async function obtenerClientesActivos() {
        const r = await fetch('api/listar_clientes.php?soloActivos=1');
        const res = await r.json();
        return res.exito ? res.clientes : [];
    }

    async function obtenerLocalesActivos() {
        const r = await fetch('api/listar_locales.php');
        const res = await r.json();
        return res.exito ? res.locales : [];
    }

    function llenarSelect(select, elementos, valorKey, textoKey) {
        const valorActual = select.value;
        select.innerHTML = '<option value="">Seleccione...</option>';
        elementos.forEach(elemento => {
            const opcion = document.createElement('option');
            opcion.value = elemento[valorKey];
            opcion.textContent = elemento[textoKey];
            select.appendChild(opcion);
        });
        if ([...select.options].some(o => o.value === valorActual)) {
            select.value = valorActual;
        }
    }

    async function cargarDatosResenas() {
        try {
            const [clientes, locales] = await Promise.all([
                obtenerClientesActivos(),
                obtenerLocalesActivos()
            ]);

            llenarSelect(document.getElementById('resena-filtro-local'), locales, 'idLocal', 'nombreLocal');
            llenarSelect(document.getElementById('resena-filtro-cliente'), clientes, 'idCliente', 'nombreCompleto');
        } catch (e) {
            mostrarMensaje('No se pudieron cargar los datos de reseñas', 'error');
        }
    }

    async function cargarResenasLocal() {
        const idLocal = document.getElementById('resena-filtro-local').value;
        const contenedor = document.getElementById('lista-resenas');
        const resumen = document.getElementById('resena-resumen');

        if (!idLocal) {
            resumen.textContent = 'Selecciona un local para ver su calificación.';
            if (contenedor) contenedor.innerHTML = '';
            return;
        }

        if (contenedor) contenedor.innerHTML = '<p class="ayuda">Cargando...</p>';

        try {
            const r = await fetch(`api/listar_resenias_local.php?idLocal=${encodeURIComponent(idLocal)}`);
            const res = await r.json();

            if (!res.exito) {
                resumen.textContent = 'No se pudo obtener la calificación.';
                if (contenedor) contenedor.innerHTML = `<p class="ayuda error">${escaparHtml(res.mensaje || 'Error al cargar reseñas')}</p>`;
                return;
            }

            const promedio = res.promedio === null ? 'Sin calificación' : `${Number(res.promedio).toFixed(1)} / 5`;
            resumen.textContent = `Promedio: ${promedio} · ${res.total} reseña${res.total === 1 ? '' : 's'}`;

            if (!contenedor) return;

            if (res.resenias.length === 0) {
                contenedor.innerHTML = '<p class="ayuda">Este local todavía no tiene reseñas.</p>';
                return;
            }

            contenedor.innerHTML = '';
            res.resenias.forEach(resenia => {
                const tarjeta = document.createElement('div');
                tarjeta.className = 'tarjeta';
                const estrellas = '★'.repeat(resenia.puntuacion) + '☆'.repeat(5 - resenia.puntuacion);

                tarjeta.innerHTML = `
                    <h3>${escaparHtml(resenia.nombreCliente)}</h3>
                    <p class="estrellas" aria-label="${resenia.puntuacion} de 5">${estrellas}</p>
                    <p>${escaparHtml(resenia.comentario)}</p>
                    <p class="ayuda">${escaparHtml(formatearFecha(resenia.fechaResenia))}</p>
                `;
                contenedor.appendChild(tarjeta);
            });
        } catch (e) {
            resumen.textContent = 'No se pudo obtener la calificación.';
            if (contenedor) contenedor.innerHTML = '<p class="ayuda error">Error de conexión al cargar reseñas.</p>';
        }
    }

    document.getElementById('btn-cargar-resenas')?.addEventListener('click', cargarResenasLocal);

    async function cargarUsuariosHistorial() {
        const tipo = document.getElementById('historial-tipo')?.value;
        const select = document.getElementById('historial-usuario');
        if (!select) return;
        select.innerHTML = '<option value="">Cargando...</option>';

        try {
            if (tipo === 'Cliente') {
                const clientes = await obtenerClientesActivos();
                llenarSelect(select, clientes, 'idCliente', 'nombreCompleto');
            } else {
                const r = await fetch('api/listar_comerciantes.php?soloActivos=1');
                const res = await r.json();
                llenarSelect(select, res.exito ? res.comerciantes : [], 'idComerciante', 'nombre');
            }

            document.getElementById('historial-password-lista')?.setAttribute('data-cargado', '1');
        } catch (e) {
            select.innerHTML = '<option value="">No se pudieron cargar usuarios</option>';
        }
    }

    document.getElementById('historial-tipo')?.addEventListener('change', cargarUsuariosHistorial);

    function obtenerCoordenadasGPS() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject(new Error('Tu navegador no soporta geolocalización'));
                return;
            }
            navigator.geolocation.getCurrentPosition(
                (posicion) => resolve({
                    lat: posicion.coords.latitude,
                    lng: posicion.coords.longitude
                }),
                (error) => reject(error),
                { enableHighAccuracy: true, timeout: 10000 }
            );
        });
    }

    function mostrarVistaLogin(idVista) {
        vistas.forEach(v => v.classList.add('oculto'));
        const destino = document.getElementById(idVista);
        if (destino) destino.classList.remove('oculto');

        botonesMenu.forEach(b => b.classList.remove('activo'));
        const boton = document.querySelector(`.menu-boton[data-vista="${idVista}"]`);
        if (boton) boton.classList.add('activo');

        const menuPrincipal = document.getElementById('menu-principal');
        if (menuPrincipal) {
            const esRegistro = idVista === 'vista-cliente';
            menuPrincipal.classList.toggle('oculto', esRegistro);
        }

        if (idVista === 'vista-listado' && typeof mostrarListaLocales === 'function') {
            mostrarListaLocales();
            cargarLocales();
        }
    }

    async function verificarSesionActual() {
        try {
            const r = await fetch('api/sesion_actual.php');
            const res = await r.json();

            if (res.autenticado) {
                actualizarIndicadorSesion(res.usuario);
                if (res.usuario.tipo === 'SuperAdmin') {
                    mostrarVistaLogin('vista-dashboard-admin');
                    cargarDashboardAdmin();
                } else {
                    mostrarVistaLogin('vista-inicio');
                }
            } else {
                actualizarIndicadorSesion(null);
            }
        } catch (e) {
            actualizarIndicadorSesion(null);
        }
    }

    function actualizarMenuPorRol(tipoUsuario) {
        botonesMenu.forEach(boton => {
            const rol = boton.dataset.rol;
            if (!rol) {
                boton.classList.remove('oculto');
                return;
            }
            boton.classList.toggle('oculto', rol !== tipoUsuario);
        });
    }

    function actualizarIndicadorSesion(usuario) {
        usuarioSesionActual = usuario;

        const indicador = document.getElementById('sesion-indicador');
        const texto = document.getElementById('sesion-texto');

        actualizarMenuPorRol(usuario ? usuario.tipo : null);

        const barraLateral = document.getElementById('barra-lateral');
        const topbarPublica = document.getElementById('topbar-publica');
        if (barraLateral) barraLateral.classList.toggle('oculto', !usuario);
        if (topbarPublica) topbarPublica.classList.toggle('oculto', !!usuario);

        const menuSecundario = document.getElementById('menu-secundario');
        if (menuSecundario) {
            menuSecundario.style.display = usuario ? 'flex' : 'none';
        }

        const botonLoginHeader = document.getElementById('btn-ir-login');
        if (botonLoginHeader) {
            botonLoginHeader.style.display = usuario ? 'none' : 'inline-flex';
        }

        const botonLogin = document.querySelector('.menu-boton[data-vista="vista-login"]');
        if (botonLogin) {
            botonLogin.classList.toggle('oculto', !!usuario);
        }

        if (indicador && texto) {
            if (usuario) {
                texto.textContent = `Sesión: ${usuario.nombre}`;
                indicador.classList.remove('oculto');
            } else {
                indicador.classList.add('oculto');
            }
        }

               const botonEmpezarVender = document.getElementById('btn-empezar-vender');
        if (botonEmpezarVender) {
            botonEmpezarVender.classList.toggle('oculto', !usuario || usuario.tipo !== 'Cliente');
        }

               const botonMiPerfil = document.getElementById('btn-mi-perfil');
        if (botonMiPerfil) {
            botonMiPerfil.classList.toggle('oculto', usuario?.tipo !== 'SuperAdmin');
        }

        const botonMiCuentaCliente = document.getElementById('btn-mi-cuenta-cliente');
        if (botonMiCuentaCliente) {
            botonMiCuentaCliente.classList.toggle('oculto', usuario?.tipo !== 'Cliente');
        }

        const botonMiCuentaComerciante = document.getElementById('btn-mi-cuenta-comerciante');
        if (botonMiCuentaComerciante) {
            botonMiCuentaComerciante.classList.toggle('oculto', usuario?.tipo !== 'Comerciante');
        }

        const piePagina = document.getElementById('pie-pagina');
        if (piePagina) piePagina.classList.toggle('oculto', !!usuario);
    }
    

    document.getElementById('btn-empezar-vender')?.addEventListener('click', () => {
        mostrarVistaLogin('vista-local');
    });

    document.getElementById('form-login')?.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const correo = document.getElementById('login-correo').value.trim();
        const password = document.getElementById('login-password').value;

        if (correo === '' || password === '') {
            mostrarMensaje('Ingresa tu correo y tu contraseña', 'error');
            return;
        }

        const formatoCorreoValido = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo);
        if (!formatoCorreoValido) {
            mostrarMensaje('El correo no tiene un formato válido', 'error');
            return;
        }

        const datos = new FormData();
        datos.append('correo', correo);
        datos.append('password', password);

        try {
            const r = await fetch('api/login.php', { method: 'POST', body: datos });
            const res = await r.json();

            mostrarMensaje(res.mensaje, res.exito ? 'exito' : 'error');

            if (res.exito) {
                actualizarIndicadorSesion(res.usuario);
                evento.target.reset();

                try {
                    const coords = await obtenerCoordenadasGPS();
                    await fetch('api/registrar_ubicacion_login.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ latitud: coords.lat, longitud: coords.lng })
                    });
                } catch (e) {
                }

                if (compraProductoPendiente) {
                    const productoRetomado = compraProductoPendiente;
                    compraProductoPendiente = null;
                    mostrarVistaLogin('vista-inicio');
                    abrirModalProducto(productoRetomado);
                } else if (res.usuario.tipo === 'SuperAdmin') {
                    mostrarVistaLogin('vista-dashboard-admin');
                    cargarDashboardAdmin();
                } else {
                    mostrarVistaLogin('vista-inicio');
                }
            }
        } catch (e) {
            mostrarMensaje('Error de conexión con el servidor', 'error');
        }
    });

    document.getElementById('btn-cerrar-sesion')?.addEventListener('click', async () => {
        const resultado = await Swal.fire({
            title: '¿Cerrar sesión?',
            text: '¿Seguro que quieres cerrar sesión?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, cerrar sesión',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#8E7CC3'
        });
        if (!resultado.isConfirmed) {
            return;
        }

        try {
            await fetch('api/cerrar_sesion.php', { method: 'POST' });
        } catch (e) { }
        actualizarIndicadorSesion(null);
        mostrarMensaje('Sesión cerrada', 'exito');
        mostrarPanelEntrar();
        mostrarVistaLogin('vista-login');
    });

    verificarSesionActual();

    const panelEntrar = document.getElementById('login-panel-entrar');
    const panelElegirTipo = document.getElementById('login-panel-elegir-tipo');

    function mostrarPanelEntrar() {
        panelElegirTipo?.classList.add('oculto');
        panelEntrar?.classList.remove('oculto');
    }

    function mostrarPanelElegirTipo() {
        panelEntrar?.classList.add('oculto');
        panelElegirTipo?.classList.remove('oculto');
    }

    document.getElementById('btn-registro')?.addEventListener('click', (evento) => {
        evento.preventDefault();
        mostrarVistaLogin('vista-cliente');
    });


    document.getElementById('link-volver-login')?.addEventListener('click', (evento) => {
        evento.preventDefault();
        mostrarPanelEntrar();
    });

    document.getElementById('btn-elegir-cliente')?.addEventListener('click', () => {
        mostrarVistaLogin('vista-cliente');
    });

    document.getElementById('btn-elegir-comerciante')?.addEventListener('click', () => {
        mostrarVistaLogin('vista-comerciante');
    });

    document.getElementById('btn-volver-login-comerciante')?.addEventListener('click', () => {
        mostrarPanelEntrar();
        mostrarVistaLogin('vista-login');
    });

    document.getElementById('btn-volver-login-cliente')?.addEventListener('click', () => {
        mostrarPanelEntrar();
        mostrarVistaLogin('vista-login');
    });

        async function mostrarSelectorPerfilesLocal() {
        mostrarVistaLogin('vista-seleccionar-local');

        const contenedor = document.getElementById('grid-perfiles-local');
        contenedor.innerHTML = '<p class="ayuda">Cargando tus locales...</p>';

        try {
            const r = await fetch('api/listar_locales_comerciante.php');
            const res = await r.json();

            if (!res.exito) {
                contenedor.innerHTML = `<p class="ayuda error">${res.mensaje || 'No se pudieron cargar tus locales'}</p>`;
                return;
            }

            contenedor.innerHTML = '';

            res.locales.forEach(local => {
                const tarjeta = document.createElement('div');
                tarjeta.className = 'tarjeta-perfil';
                tarjeta.classList.add('tarjeta-con-borrar');
                tarjeta.innerHTML = `
                    <button type="button" class="tarjeta-local-btn-editar" aria-label="Editar local"><i data-lucide="pencil"></i></button>
                    <button type="button" class="tarjeta-local-btn-eliminar" aria-label="Eliminar local"><i data-lucide="trash-2"></i></button>
                    ${local.logo
                        ? `<img src="imagenes/${local.logo}" alt="${local.nombreLocal}">`
                        : `<div class="icono-perfil"><i data-lucide="store"></i></div>`}
                    <span class="nombre-perfil">${local.nombreLocal}</span>
                    ${!local.activo ? '<span class="etiqueta-inactivo">Inactivo por falta de uso</span>' : ''}
                `;
                tarjeta.addEventListener('click', () => entrarPerfilLocal(local.idLocal));

                tarjeta.querySelector('.tarjeta-local-btn-editar').addEventListener('click', (evento) => {
                    evento.stopPropagation();
                    abrirModalEditarLocal(local.idLocal);
                });

                tarjeta.querySelector('.tarjeta-local-btn-eliminar').addEventListener('click', (evento) => {
                    evento.stopPropagation();
                    Swal.fire({
                        title: `¿Eliminar "${local.nombreLocal}"?`,
                        text: 'Este local se marcará como inactivo y dejará de verse en la app. Esta acción no se puede deshacer.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#DC2626',
                        cancelButtonColor: '#8E7CC3'
                    }).then(async (resultado) => {
                        if (!resultado.isConfirmed) return;

                        try {
                            const r2 = await fetch('api/eliminar_mi_local.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ idLocal: local.idLocal })
                            });
                            const res2 = await r2.json();
                            mostrarMensaje(res2.mensaje, res2.exito ? 'exito' : 'error');
                            if (res2.exito) {
                                await mostrarSelectorPerfilesLocal();
                            }
                        } catch (e) {
                            mostrarMensaje('No se pudo conectar con el servidor para eliminar el local. Revisa tu conexión e intenta de nuevo.', 'error');
                        }
                    });
                });

                contenedor.appendChild(tarjeta);
            });

            if (window.lucide) lucide.createIcons();

            const tarjetaNueva = document.createElement('div');
            tarjetaNueva.className = 'tarjeta-perfil crear-nuevo';
            tarjetaNueva.textContent = '+ Crear nuevo local';
            tarjetaNueva.addEventListener('click', () => mostrarVistaLogin('vista-local'));
            contenedor.appendChild(tarjetaNueva);
        } catch (e) {
            contenedor.innerHTML = '<p class="ayuda error">Error de conexión al cargar tus locales.</p>';
        }
    }

    async function entrarPerfilLocal(idLocal) {
        try {
            const r = await fetch('api/entrar_perfil_local.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idLocal })
            });
            const res = await r.json();

            if (!res.exito) {
                mostrarMensaje(res.mensaje || 'No se pudo entrar a ese local', 'error');
                return;
            }

            mostrarVistaLogin('vista-listado');
            if (typeof abrirDetalleLocal === 'function') {
                abrirDetalleLocal(idLocal);
            }
        } catch (e) {
            mostrarMensaje('Error de conexión con el servidor', 'error');
        }
    }

    //Validacion para frontend 
    function soloLetras(inputEl) {
        if (!inputEl) return;
        inputEl.addEventListener('input', () => {
            const filtrado = inputEl.value.replace(/[^A-Za-zÁÉÍÓÚÑáéíóúñÜü' -]/g, '');
            if (filtrado !== inputEl.value) {
                const posicion = inputEl.selectionStart - (inputEl.value.length - filtrado.length);
                inputEl.value = filtrado;
                inputEl.setSelectionRange(posicion, posicion);
            }
        });
    }

    soloLetras(document.getElementById('cl-nombreCompleto'));
    soloLetras(document.getElementById('dc-nombre'));
    soloLetras(document.getElementById('dc-alias'));
    soloLetras(document.getElementById('dcl-nombreCompleto'));

    function soloAlfanumerico(valor) {
        return valor.replace(/[^A-Za-z0-9]/g, '');
    }

    const pieAnio = document.getElementById('pie-anio');
    if (pieAnio) pieAnio.textContent = new Date().getFullYear();

    document.querySelectorAll('.pie-enlaces a[data-vista-footer]').forEach(enlace => {
        enlace.addEventListener('click', (evento) => {
            evento.preventDefault();
            const destino = enlace.dataset.vistaFooter;
            const boton = document.querySelector(`.menu-boton[data-vista="${destino}"]`);
            if (boton) {
                boton.click();
            } else if (typeof mostrarVistaLogin === 'function') {
                mostrarVistaLogin(destino);
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });

    document.getElementById('btn-cerc-ubicacion')?.addEventListener('click', async () => {
        const msg = document.getElementById('cerc-ubicacion-msg');
        msg.textContent = 'Obteniendo tu ubicación...';
        msg.className = 'ayuda';

        try {
            const coords = await obtenerCoordenadasGPS();
            document.getElementById('cerc-latitud').value = coords.lat;
            document.getElementById('cerc-longitud').value = coords.lng;

            const r = await fetch('api/actualizar_ubicacion_cliente.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ latitud: coords.lat, longitud: coords.lng })
            });
            const res = await r.json();

            if (res.exito) {
                msg.textContent = 'Ubicación obtenida correctamente ✅';
                msg.className = 'ayuda exito';
            } else {
                msg.textContent = res.mensaje || 'No se pudo guardar tu ubicación';
                msg.className = 'ayuda error';
            }
        } catch (e) {
            msg.textContent = 'No se pudo obtener tu ubicación GPS. Revisa los permisos del navegador.';
            msg.className = 'ayuda error';
        }
    });

    document.getElementById('form-cercanos')?.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const termino = document.getElementById('cerc-termino').value.trim();
        const lat = document.getElementById('cerc-latitud').value;
        const lng = document.getElementById('cerc-longitud').value;
        const radio = document.getElementById('cerc-radio').value;
        const contenedor = document.getElementById('lista-cercanos');

        if (!termino) {
            mostrarMensaje('Escribe qué producto o tipo de local buscas', 'error');
            return;
        }
        if (!lat || !lng) {
            mostrarMensaje('Primero presiona "Usar mi ubicación GPS"', 'error');
            return;
        }

        contenedor.innerHTML = '<p class="ayuda">Buscando locales cercanos...</p>';

        try {
            const parametros = new URLSearchParams({ q: termino, lat, lng, radio });
            const r = await fetch(`api/buscar_locales_cercanos.php?${parametros.toString()}`);
            const res = await r.json();

            if (!res.exito) {
                contenedor.innerHTML = `<p class="ayuda error">${escaparHtml(res.mensaje || 'No se pudo completar la búsqueda')}</p>`;
                return;
            }

            if (res.resultados.length === 0) {
                contenedor.innerHTML = '<p class="ayuda">No se encontraron locales cercanos con ese producto.</p>';
                return;
            }

            contenedor.innerHTML = '';
            res.resultados.forEach(item => {
                const precioHtml = item.descuento
                    ? `<s>₡${item.precio}</s> ₡${(item.precio - (item.precio * item.descuento / 100)).toFixed(2)} <span class="etiqueta-tipo">-${item.descuento}%</span>`
                    : `₡${item.precio}`;

                const tarjeta = document.createElement('div');
                tarjeta.className = 'tarjeta tarjeta-clic';
                tarjeta.innerHTML = `
                    ${item.logo ? `<img src="imagenes/${item.logo}" alt="${escaparHtml(item.nombreLocal)}" class="imagen-producto">` : ''}
                    <h3>${escaparHtml(item.nombreLocal)}</h3>
                    <span class="chip chip-distancia">📍 ${item.distanciaKm} km</span>
                    <p><strong>${escaparHtml(item.nombreProducto)}</strong></p>
                    <p>${precioHtml}</p>
                    <p>📞 ${escaparHtml(item.telefono ?? '')}</p>
                `;
                tarjeta.addEventListener('click', () => {
                    mostrarVistaLogin('vista-listado');
                    abrirDetalleLocal(item.idLocal);
                });
                contenedor.appendChild(tarjeta);
            });
        } catch (e) {
            contenedor.innerHTML = '<p class="ayuda error">Error de conexión al buscar locales cercanos.</p>';
        }
    });

    const inputBuscarIdentComerciante = document.getElementById('admin-buscar-identificacion');

    inputBuscarIdentComerciante?.addEventListener('input', () => {
        inputBuscarIdentComerciante.value = soloAlfanumerico(inputBuscarIdentComerciante.value);
    });

    document.getElementById('btn-buscar-comerciante-identificacion')?.addEventListener('click', async () => {
        const numero = inputBuscarIdentComerciante.value.trim();
        const msg = document.getElementById('admin-buscar-identificacion-msg');

        if (!numero) {
            msg.textContent = 'Escribe un número de identificación';
            msg.className = 'ayuda error';
            return;
        }

        msg.textContent = 'Buscando...';
        msg.className = 'ayuda';

        try {
            const r = await fetch(`api/buscar_comerciante_por_identificacion.php?numeroIdentificacion=${encodeURIComponent(numero)}`);
            const res = await r.json();

            if (!res.encontrado) {
                msg.textContent = 'No se encontró ningún comerciante con esa identificación';
                msg.className = 'ayuda error';
                return;
            }

            msg.textContent = '';
            abrirDetalleComerciante(res.idComerciante);
        } catch (e) {
            msg.textContent = 'Error de conexión al buscar';
            msg.className = 'ayuda error';
        }
    });

    async function cargarHistorialActividadLocal(idLocal) {
        const contenedor = document.getElementById('e-actividad-lista');
        const estado = document.getElementById('e-actividad-estado');
        contenedor.innerHTML = '<p class="ayuda">Cargando...</p>';

        try {
            const r = await fetch(`api/listar_sesion_activo_historico.php?idLocal=${idLocal}`);
            const res = await r.json();

            if (!res.exito) {
                estado.textContent = '';
                contenedor.innerHTML = `<p class="ayuda error">${escaparHtml(res.mensaje || 'No se pudo cargar el historial')}</p>`;
                return;
            }

            estado.textContent = res.activoPorActividad
                ? '✅ Este local está activo por actividad reciente.'
                : '⚠️ Este local lleva más de 7 días sin actividad.';
            estado.className = res.activoPorActividad ? 'ayuda exito' : 'ayuda error';

            if (res.historial.length === 0) {
                contenedor.innerHTML = '<p class="ayuda">Todavía no hay actividad registrada.</p>';
                return;
            }

            contenedor.innerHTML = '';
            res.historial.forEach(item => {
                const tarjeta = document.createElement('div');
                tarjeta.className = 'tarjeta';
                const cambio = item.valorNuevo ? 'Local activado' : 'Local desactivado';
                tarjeta.innerHTML = `
                    <h3>${escaparHtml(cambio)}</h3>
                    <p class="ayuda">${escaparHtml(formatearFecha(item.fecha))}</p>
                `;
                contenedor.appendChild(tarjeta);
            });
        } catch (e) {
            contenedor.innerHTML = '<p class="ayuda error">Error de conexión al cargar la actividad.</p>';
        }
    }

    document.getElementById('btn-ver-resenas-cliente')?.addEventListener('click', async () => {
        const idCliente = document.getElementById('resena-filtro-cliente').value;
        const contenedor = document.getElementById('lista-resenas-cliente');

        if (!idCliente) {
            mostrarMensaje('Selecciona un cliente', 'error');
            return;
        }

        contenedor.innerHTML = '<p class="ayuda">Cargando...</p>';

        try {
            const r = await fetch(`api/listar_resenias_cliente.php?idCliente=${idCliente}`);
            const res = await r.json();

            if (!res.exito) {
                contenedor.innerHTML = `<p class="ayuda error">${escaparHtml(res.mensaje || 'No se pudieron cargar las reseñas')}</p>`;
                return;
            }

            if (res.resenias.length === 0) {
                contenedor.innerHTML = '<p class="ayuda">Este cliente todavía no ha escrito reseñas.</p>';
                return;
            }

            contenedor.innerHTML = '';
            res.resenias.forEach(resenia => {
                const estrellas = '★'.repeat(resenia.puntuacion) + '☆'.repeat(5 - resenia.puntuacion);
                const tarjeta = document.createElement('div');
                tarjeta.className = 'tarjeta';
                tarjeta.innerHTML = `
                    <h3>${escaparHtml(resenia.nombreLocal)}</h3>
                    <p class="estrellas" aria-label="${resenia.puntuacion} de 5">${estrellas}</p>
                    <p>${escaparHtml(resenia.comentario)}</p>
                    <p class="ayuda">${escaparHtml(formatearFecha(resenia.fechaResenia))}</p>
                `;
                contenedor.appendChild(tarjeta);
            });
        } catch (e) {
            contenedor.innerHTML = '<p class="ayuda error">Error de conexión al cargar las reseñas.</p>';
        }
    });


    // ============================================================
    // Dashboard de SuperAdmin
    // ============================================================
    async function cargarDashboardAdmin() {
        const statLocales = document.getElementById('stat-locales');
        const statClientes = document.getElementById('stat-clientes');
        const statComerciantes = document.getElementById('stat-comerciantes');
        const statComerciantesInactivos = document.getElementById('stat-comerciantes-inactivos');

        if (!statLocales) return;

        statLocales.textContent = '—';
        statClientes.textContent = '—';
        statComerciantes.textContent = '—';
        statComerciantesInactivos.textContent = '—';

        try {
            const [locales, clientes, rComerciantesActivos, rComerciantesTodos] = await Promise.all([
                obtenerLocalesActivos(),
                obtenerClientesActivos(),
                fetch('api/listar_comerciantes.php?soloActivos=1').then(r => r.json()),
                fetch('api/listar_comerciantes.php?soloActivos=0').then(r => r.json())
            ]);

            statLocales.textContent = locales.length;
            statClientes.textContent = clientes.length;

            const comerciantesActivos = rComerciantesActivos.exito ? rComerciantesActivos.comerciantes.length : 0;
            const comerciantesTodos = rComerciantesTodos.exito ? rComerciantesTodos.comerciantes.length : 0;

            statComerciantes.textContent = comerciantesActivos;
            statComerciantesInactivos.textContent = Math.max(comerciantesTodos - comerciantesActivos, 0);

            if (window.lucide) lucide.createIcons();
        } catch (e) {
            mostrarMensaje('No se pudo cargar el resumen del dashboard', 'error');
        }
    }

    document.querySelectorAll('.acceso-dashboard-boton[data-vista]').forEach(boton => {
        boton.addEventListener('click', () => {
            const destino = document.querySelector(`.menu-boton[data-vista="${boton.dataset.vista}"]`);
            if (destino) destino.click();
        });
    });

    // ============================================================
    // Vista: Inicio (catálogo público + carrusel)
    // ============================================================
    let carruselLocales = [];
    let carruselIndice = 0;
    let carruselIntervalo = null;
    let localesInicioCache = [];

    function renderizarCarrusel() {
        const pista = document.getElementById('carrusel-pista');
        const puntos = document.getElementById('carrusel-puntos');
        if (!pista) return;

        if (carruselLocales.length === 0) {
            pista.innerHTML = '<div class="carrusel-slide"><p class="ayuda">Todavía no hay locales registrados.</p></div>';
            puntos.innerHTML = '';
            return;
        }

               pista.innerHTML = carruselLocales.map(local => `
            <div class="carrusel-slide">
                ${local.logo
                ? `<img src="imagenes/${local.logo}" alt="${escaparHtml(local.nombreLocal)}">`
                : `<div class="carrusel-slide-sin-logo"><i data-lucide="store"></i></div>`}
                <div class="carrusel-slide-info">
                    <span class="etiqueta-tipo">${escaparHtml(local.tipoLocal ?? '')}</span>
                    <h3>${escaparHtml(local.nombreLocal)}</h3>
                    <p>${escaparHtml(local.descripcion ?? 'Sin descripción')}</p>
                    <button type="button" class="boton-secundario carrusel-ver-local" data-id="${local.idLocal}">Ver local →</button>
                </div>
            </div>
        `).join('');

        if (window.lucide) lucide.createIcons();

        pista.style.transform = `translateX(-${carruselIndice * 100}%)`;

        puntos.innerHTML = carruselLocales.map((_, i) =>
            `<button type="button" class="carrusel-punto ${i === carruselIndice ? 'activo' : ''}" data-indice="${i}" aria-label="Ir al local ${i + 1}"></button>`
        ).join('');

        puntos.querySelectorAll('.carrusel-punto').forEach(punto => {
            punto.addEventListener('click', () => {
                carruselIndice = Number(punto.dataset.indice);
                renderizarCarrusel();
                iniciarAutoplayCarrusel();
            });
        });

        pista.querySelectorAll('.carrusel-ver-local').forEach(boton => {
            boton.addEventListener('click', () => {
                abrirModalLocal(Number(boton.dataset.id));
            });
        });
    }

    function moverCarrusel(direccion) {
        if (carruselLocales.length === 0) return;
        carruselIndice = (carruselIndice + direccion + carruselLocales.length) % carruselLocales.length;
        renderizarCarrusel();
    }

    document.getElementById('carrusel-prev')?.addEventListener('click', () => {
        moverCarrusel(-1);
        iniciarAutoplayCarrusel();
    });
    document.getElementById('carrusel-next')?.addEventListener('click', () => {
        moverCarrusel(1);
        iniciarAutoplayCarrusel();
    });

    function iniciarAutoplayCarrusel() {
        clearInterval(carruselIntervalo);
        carruselIntervalo = setInterval(() => moverCarrusel(1), 5000);
    }

       document.getElementById('inicio-buscar')?.addEventListener('input', debounce(() => {
        const termino = (document.getElementById('inicio-buscar')?.value || '').trim().toLowerCase();

        const filtrados = termino
            ? productosInicioCache.filter(p =>
                p.nombre.toLowerCase().includes(termino) ||
                p.nombreLocal.toLowerCase().includes(termino) ||
                (p.categoria ?? '').toLowerCase().includes(termino))
            : productosInicioCache;

        renderizarSeccionesProductos(filtrados);
    }, 300));

    async function cargarInicio() {
        try {
            const locales = await obtenerLocalesActivos();
            localesInicioCache = locales;

            carruselLocales = locales.slice(0, 8);
            carruselIndice = 0;
            renderizarCarrusel();
            iniciarAutoplayCarrusel();

            cargarProductosRecientesInicio();
        } catch (e) {
            mostrarMensaje('No se pudieron cargar los locales de Inicio', 'error');
        }
    }
    cargarInicio();


    // ============================================================
    // BOTÓN LOGIN - Redirige al login
    // ============================================================
    document.getElementById('btn-ir-login')?.addEventListener('click', function () {
        document.querySelectorAll('.vista').forEach(v => v.classList.add('oculto'));
        document.getElementById('vista-login')?.classList.remove('oculto');
        document.querySelectorAll('.menu-boton').forEach(b => b.classList.remove('activo'));
    });

    // ============================================================
    // TOGGLE PASSWORD
    // ============================================================
    document.getElementById('toggle-password')?.addEventListener('click', function () {
        const input = document.getElementById('login-password');
        if (input.type === 'password') {
            input.type = 'text';
            this.textContent = '🙈';
        } else {
            input.type = 'password';
            this.textContent = '👁️';
        }
    });

       let productosInicioCache = [];

    function formatearTiempoRestante(ms) {
        const totalSegundos = Math.floor(ms / 1000);
        const horas = Math.floor(totalSegundos / 3600);
        const minutos = Math.floor((totalSegundos % 3600) / 60);
        const segundos = totalSegundos % 60;

        return `${String(horas).padStart(2, '0')}:${String(minutos).padStart(2, '0')}:${String(segundos).padStart(2, '0')}`;
    }

    function actualizarCronometros() {
        const ahora = Date.now();
        document.querySelectorAll('[data-vence]').forEach(elemento => {
            const vence = new Date(elemento.dataset.vence).getTime();
            const restante = vence - ahora;
            const textoSpan = elemento.querySelector('.cronometro-texto') || elemento;

            if (isNaN(vence) || restante <= 0) {
                textoSpan.textContent = 'Vencido';
                elemento.classList.add('cronometro-vencido');
                return;
            }

            elemento.classList.remove('cronometro-vencido');
            textoSpan.textContent = formatearTiempoRestante(restante);
        });
    }

    setInterval(actualizarCronometros, 1000);

    function agruparProductosPorCategoria(productos) {
        const grupos = {};
        productos.forEach(p => {
            const cat = p.categoria || 'Otros';
            if (!grupos[cat]) grupos[cat] = [];
            grupos[cat].push(p);
        });
        return grupos;
    }

    function crearTarjetaProducto(p) {
        const div = document.createElement('div');
        div.className = 'tarjeta-producto';
        div.innerHTML = `
            <div class="tarjeta-producto-imagen">
                ${p.imagen
                ? `<img src="imagenes/${p.imagen}" alt="${escaparHtml(p.nombre)}">`
                : `<div class="tarjeta-producto-sin-imagen"><i data-lucide="package"></i></div>`}
                ${p.porcentajeDescuento ? `<span class="tarjeta-producto-badge">-${p.porcentajeDescuento}%</span>` : ''}
                ${p.logoLocal ? `<img class="tarjeta-producto-logo-local" src="imagenes/${p.logoLocal}" alt="${escaparHtml(p.nombreLocal)}">` : ''}
            </div>
            <div class="tarjeta-producto-info">
                <span class="tarjeta-producto-local">${escaparHtml(p.nombreLocal)}</span>
                <h4>${escaparHtml(p.nombre)}</h4>
                <p class="tarjeta-producto-disponibles">Disponibles: ${p.cantidadDisponible}</p>
                ${p.fechaVencimiento ? `
                <p class="tarjeta-producto-cronometro" data-vence="${p.fechaVencimiento}">
                    <i data-lucide="timer"></i> <span class="cronometro-texto">--:--:--</span>
                </p>` : ''}
                <p class="tarjeta-producto-precio">
                    ${p.porcentajeDescuento ? `<s>₡${p.precioOriginal}</s>` : ''}
                    <strong>₡${p.precioFinal}</strong>
                </p>
            </div>
        `;
        div.addEventListener('click', () => {
            abrirModalProducto(p);
        });
        return div;
    }

    function renderizarSeccionesProductos(productos) {
        const contenedor = document.getElementById('productos-recientes-inicio');
        if (!contenedor) return;

        if (productos.length === 0) {
            contenedor.innerHTML = '<p class="ayuda">Todavía no hay productos disponibles.</p>';
            return;
        }

        const grupos = agruparProductosPorCategoria(productos);
        contenedor.innerHTML = '';

        Object.keys(grupos).sort().forEach(categoria => {
            const seccion = document.createElement('section');
            seccion.className = 'seccion-categoria';
            seccion.innerHTML = `
                <div class="seccion-categoria-encabezado">
                    <h3>${escaparHtml(categoria)}</h3>
                    <div class="seccion-categoria-flechas">
                        <button type="button" class="carrusel-flecha-mini" data-dir="-1" aria-label="Anterior">&#10094;</button>
                        <button type="button" class="carrusel-flecha-mini" data-dir="1" aria-label="Siguiente">&#10095;</button>
                    </div>
                </div>
                <div class="fila-productos-carrusel"></div>
            `;

            const fila = seccion.querySelector('.fila-productos-carrusel');
            grupos[categoria].forEach(p => fila.appendChild(crearTarjetaProducto(p)));

            seccion.querySelectorAll('.carrusel-flecha-mini').forEach(boton => {
                boton.addEventListener('click', () => {
                    const direccion = Number(boton.dataset.dir);
                    fila.scrollBy({ left: direccion * 240, behavior: 'smooth' });
                });
            });

            contenedor.appendChild(seccion);
        });

        if (window.lucide) lucide.createIcons();
        actualizarCronometros();
    }

    async function cargarProductosRecientesInicio() {
        const contenedor = document.getElementById('productos-recientes-inicio');
        if (!contenedor) return;

        contenedor.innerHTML = '<p class="ayuda">Cargando productos...</p>';

        try {
            const r = await fetch('api/listar_productos_publicos.php');
            const res = await r.json();

            if (!res.exito) {
                contenedor.innerHTML = '<p class="ayuda error">No se pudieron cargar los productos.</p>';
                return;
            }

            productosInicioCache = res.productos;
            renderizarSeccionesProductos(productosInicioCache);
        } catch (e) {
            contenedor.innerHTML = '<p class="ayuda error">Error al cargar los productos.</p>';
        }
    }

    async function cargarMiCuentaCliente() {
        if (!usuarioSesionActual) return;

        try {
            const r = await fetch(`api/buscar_cliente.php?id=${usuarioSesionActual.id}`);
            const res = await r.json();

            if (!res.exito) {
                mostrarMensaje(res.mensaje || 'No se pudo cargar tu cuenta', 'error');
                return;
            }

            const c = res.cliente;
            const u = res.ubicacion;

            document.getElementById('mc-idCliente').value = c.idCliente;
            document.getElementById('mc-nombreCompleto').value = c.nombreCompleto;
            document.getElementById('mc-correo').value = c.correo;
            document.getElementById('mc-identificacion').textContent = c.numeroIdentificacion;
            document.getElementById('mc-direccion').textContent =
                u.direccionExacta + (u.referencia ? ` (${u.referencia})` : '');

            const imgFoto = document.getElementById('mc-foto-actual');
            if (c.fotoPerfil) {
                imgFoto.src = `imagenes/${c.fotoPerfil}`;
                imgFoto.classList.remove('oculto');
            } else {
                imgFoto.classList.add('oculto');
            }

            cargarLocalesQueSigueCliente(c.idCliente, 'mc-locales-lista');
            cargarMisResenasCliente(c.idCliente);
        } catch (e) {
            mostrarMensaje('Error al cargar tu cuenta', 'error');
        }
    }

    document.getElementById('form-mi-cuenta-cliente')?.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const datos = new FormData();
        datos.append('idCliente', document.getElementById('mc-idCliente').value);
        datos.append('nombreCompleto', document.getElementById('mc-nombreCompleto').value);
        datos.append('correo', document.getElementById('mc-correo').value);

        const archivoFoto = document.getElementById('mc-fotoPerfil').files[0];
        if (archivoFoto) {
            datos.append('fotoPerfil', archivoFoto);
        }

        try {
            const r = await fetch('api/editar_cliente.php', { method: 'POST', body: datos });
            const res = await r.json();
            mostrarMensaje(res.mensaje, res.exito ? 'exito' : 'error');
            if (res.exito) cargarMiCuentaCliente();
        } catch (e) {
            mostrarMensaje('Error de conexión con el servidor', 'error');
        }
    });

    document.getElementById('form-mi-cuenta-cliente-password')?.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const idUsuario = document.getElementById('mc-idCliente').value;
        const passwordActual = document.getElementById('mc-password-actual').value;
        const passwordNueva = document.getElementById('mc-password-nueva').value;

        try {
            const r = await fetch('api/cambiar_password_usuario.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idUsuario, tipoUsuario: 'Cliente', passwordActual, passwordNueva })
            });
            const res = await r.json();
            mostrarMensaje(res.mensaje, res.exito ? 'exito' : 'error');
            if (res.exito) evento.target.reset();
        } catch (e) {
            mostrarMensaje('Error de conexión con el servidor', 'error');
        }
    });

    document.getElementById('mc-btn-seguir-local')?.addEventListener('click', async () => {
        const idCliente = document.getElementById('mc-idCliente').value;
        const nombreLocal = document.getElementById('mc-agregar-local-nombre').value.trim();
        const mensaje = document.getElementById('mc-seguir-local-msg');

        if (!nombreLocal) return;

        mensaje.textContent = 'Buscando local...';
        mensaje.className = 'ayuda';

        try {
            const rBuscar = await fetch(`api/buscar_local_por_nombre.php?nombre=${encodeURIComponent(nombreLocal)}`);
            const resBuscar = await rBuscar.json();

            if (!resBuscar.encontrado) {
                mensaje.textContent = 'No existe un local con ese nombre exacto';
                mensaje.className = 'ayuda error';
                return;
            }

            const rSeguir = await fetch('api/seguir_local_cliente.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idCliente, idLocal: resBuscar.idLocal })
            });
            const resSeguir = await rSeguir.json();

            mensaje.textContent = resSeguir.mensaje;
            mensaje.className = resSeguir.exito ? 'ayuda exito' : 'ayuda error';

            if (resSeguir.exito) {
                document.getElementById('mc-agregar-local-nombre').value = '';
                cargarLocalesQueSigueCliente(idCliente, 'mc-locales-lista');
            }
        } catch (e) {
            mensaje.textContent = 'Error de conexión con el servidor';
            mensaje.className = 'ayuda error';
        }
    });

    async function cargarMisResenasCliente(idCliente) {
        const contenedor = document.getElementById('mc-resenas-lista');
        contenedor.innerHTML = '<p class="ayuda">Cargando...</p>';

        try {
            const r = await fetch(`api/listar_resenias_cliente.php?idCliente=${idCliente}`);
            const res = await r.json();

            if (!res.exito || res.resenias.length === 0) {
                contenedor.innerHTML = '<p class="ayuda">Todavía no has escrito reseñas.</p>';
                return;
            }

            contenedor.innerHTML = '';
            res.resenias.forEach(resenia => {
                const estrellas = '★'.repeat(resenia.puntuacion) + '☆'.repeat(5 - resenia.puntuacion);
                const tarjeta = document.createElement('div');
                tarjeta.className = 'tarjeta';
                tarjeta.innerHTML = `
                    <h3>${escaparHtml(resenia.nombreLocal)}</h3>
                    <p class="estrellas" aria-label="${resenia.puntuacion} de 5">${estrellas}</p>
                    <p>${escaparHtml(resenia.comentario)}</p>
                    <p class="ayuda">${escaparHtml(formatearFecha(resenia.fechaResenia))}</p>
                `;
                contenedor.appendChild(tarjeta);
            });
        } catch (e) {
            contenedor.innerHTML = '<p class="ayuda error">Error al cargar tus reseñas.</p>';
        }
    }

    async function cargarMiCuentaComerciante() {
        if (!usuarioSesionActual) return;

        try {
            const r = await fetch(`api/buscar_comerciante.php?id=${usuarioSesionActual.id}`);
            const res = await r.json();

            if (!res.exito) {
                mostrarMensaje(res.mensaje || 'No se pudo cargar tu cuenta', 'error');
                return;
            }

            const c = res.comerciante;

            document.getElementById('mco-idComerciante').value = c.idComerciante;
            document.getElementById('mco-nombre').value = c.nombre;
            document.getElementById('mco-alias').value = c.alias;
            document.getElementById('mco-correo').value = c.correo;
            document.getElementById('mco-identificacion').textContent = c.numeroIdentificacion;

            const imgFoto = document.getElementById('mco-foto-actual');
            if (c.fotoPerfil) {
                imgFoto.src = `imagenes/${c.fotoPerfil}`;
                imgFoto.classList.remove('oculto');
            } else {
                imgFoto.classList.add('oculto');
            }
        } catch (e) {
            mostrarMensaje('Error al cargar tu cuenta', 'error');
        }
    }

    document.getElementById('form-mi-cuenta-comerciante')?.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const datos = new FormData();
        datos.append('idComerciante', document.getElementById('mco-idComerciante').value);
        datos.append('nombre', document.getElementById('mco-nombre').value);
        datos.append('alias', document.getElementById('mco-alias').value);
        datos.append('correo', document.getElementById('mco-correo').value);

        const archivoFoto = document.getElementById('mco-fotoPerfil').files[0];
        if (archivoFoto) {
            datos.append('fotoPerfil', archivoFoto);
        }

        try {
            const r = await fetch('api/editar_comerciante.php', { method: 'POST', body: datos });
            const res = await r.json();
            mostrarMensaje(res.mensaje, res.exito ? 'exito' : 'error');
            if (res.exito) cargarMiCuentaComerciante();
        } catch (e) {
            mostrarMensaje('Error de conexión con el servidor', 'error');
        }
    });

    document.getElementById('form-mi-cuenta-comerciante-password')?.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const idUsuario = document.getElementById('mco-idComerciante').value;
        const passwordActual = document.getElementById('mco-password-actual').value;
        const passwordNueva = document.getElementById('mco-password-nueva').value;

        try {
            const r = await fetch('api/cambiar_password_usuario.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idUsuario, tipoUsuario: 'Comerciante', passwordActual, passwordNueva })
            });
            const res = await r.json();
            mostrarMensaje(res.mensaje, res.exito ? 'exito' : 'error');
            if (res.exito) evento.target.reset();
        } catch (e) {
            mostrarMensaje('Error de conexión con el servidor', 'error');
        }
    });

    document.getElementById('mco-btn-ir-mis-locales')?.addEventListener('click', async () => {
        await mostrarSelectorPerfilesLocal();
    });

    const ETIQUETAS_TIPO_HISTORIAL = {
        password: 'Cambio de contraseña',
        perfilImagen: 'Cambio de foto',
        nombre: 'Cambio de nombre',
        correo: 'Cambio de correo',
        telefono: 'Cambio de teléfono',
        logo: 'Cambio de logo',
        precio: 'Cambio de precio',
        descuento: 'Cambio de descuento',
        activo: 'Producto eliminado'
    };

    const ICONOS_TIPO_HISTORIAL = {
        password: '🔒',
        perfilImagen: '🖼️',
        nombre: '✏️',
        correo: '✉️',
        telefono: '📞',
        logo: '🏷️',
        precio: '💲',
        descuento: '🏷️',
        activo: '🗑️'
    };

    function etiquetaHistorial(registro) {
        if (registro.tipo === 'activo') {
            return Number(registro.valorNuevo) === 1 ? 'Producto reactivado' : 'Producto eliminado';
        }
        return ETIQUETAS_TIPO_HISTORIAL[registro.tipo] || registro.tipo;
    }

    let historialTipoActivo = 'todos';
    let historialBuscarDebounced = null;
    let historialRegistrosCache = [];

    async function cargarHistorialGlobal() {
        const contenedor = document.getElementById('hist-tabla');
        const tabsContenedor = document.getElementById('hist-tabs');
        const entidadTipo = document.getElementById('hist-filtro-entidad')?.value || 'todos';
        const termino = document.getElementById('hist-buscar')?.value.trim() || '';

        contenedor.innerHTML = '<p class="ayuda" style="padding: 1.2rem;">Cargando...</p>';

        try {
            const parametros = new URLSearchParams({ entidadTipo, termino, tipo: historialTipoActivo });
            const r = await fetch(`api/listar_historial_global.php?${parametros.toString()}`);
            const res = await r.json();

            if (!res.exito) {
                contenedor.innerHTML = `<p class="ayuda error" style="padding: 1.2rem;">${escaparHtml(res.mensaje || 'No se pudo cargar el historial')}</p>`;
                tabsContenedor.innerHTML = '';
                return;
            }

            historialRegistrosCache = res.registros;

            tabsContenedor.innerHTML = '';
            const conteos = res.conteos || { todos: 0 };
            Object.keys(conteos).forEach(tipo => {
                const boton = document.createElement('button');
                boton.type = 'button';
                boton.className = 'historial-tab' + (tipo === historialTipoActivo ? ' activo' : '');
                const etiqueta = tipo === 'todos' ? 'Todos' : (ETIQUETAS_TIPO_HISTORIAL[tipo] || tipo);
                boton.textContent = `${etiqueta} (${conteos[tipo]})`;
                boton.addEventListener('click', () => {
                    historialTipoActivo = tipo;
                    cargarHistorialGlobal();
                });
                tabsContenedor.appendChild(boton);
            });

            if (res.registros.length === 0) {
                contenedor.innerHTML = '<p class="ayuda" style="padding: 1.2rem;">No hay registros de historial todavía.</p>';
                return;
            }

            contenedor.innerHTML = '';
            res.registros.forEach(registro => {
                const fila = document.createElement('div');
                fila.className = 'fila-relacion historial-fila';

                let nombrePrincipal = registro.usuarioNombre ?? '(sin nombre)';
                if (registro.localNombre) {
                    nombrePrincipal += ` — ${registro.localNombre}`;
                }

                const esCreacion = registro.valorAnterior === null;
                let etiquetaAutor = '';
                if (registro.autorNombre) {
                    etiquetaAutor = `<span class="ayuda">${esCreacion ? 'Creado' : 'Editado'} por ${escaparHtml(registro.autorNombre)}</span>`;
                }

                fila.innerHTML = `
                    <span>${ICONOS_TIPO_HISTORIAL[registro.tipo] || '📋'} ${escaparHtml(etiquetaHistorial(registro))}</span>
                    <span class="etiqueta-tipo">${escaparHtml(registro.entidadTipo)}</span>
                    <span>${escaparHtml(nombrePrincipal)}</span>
                    ${etiquetaAutor}
                    <span class="ayuda">${escaparHtml(formatearFecha(registro.fecha))}</span>
                `;
                fila.style.cursor = 'pointer';
                fila.addEventListener('click', () => abrirPanelHistorial(registro));
                contenedor.appendChild(fila);
            });
        } catch (e) {
            contenedor.innerHTML = '<p class="ayuda error" style="padding: 1.2rem;">Error de conexión al cargar el historial.</p>';
        }
    }

    function abrirPanelHistorial(registro) {
        document.getElementById('hist-panel-icono').textContent = ICONOS_TIPO_HISTORIAL[registro.tipo] || '📋';
        document.getElementById('hist-panel-tipo-nombre').textContent = etiquetaHistorial(registro);
        document.getElementById('hist-panel-entidad-badge').textContent = registro.entidadTipo;

        let nombreUsuario = registro.usuarioNombre ?? '(sin nombre)';
        if (registro.localNombre) {
            nombreUsuario += ` (local: ${registro.localNombre})`;
        }
        document.getElementById('hist-panel-usuario').textContent = nombreUsuario;
        document.getElementById('hist-panel-entidad-tipo').textContent = registro.autorNombre
            ? `${registro.entidadTipo} — hecho por ${registro.autorNombre}`
            : registro.entidadTipo;

        const esCreacion = registro.valorAnterior === null;
        document.getElementById('hist-panel-accion').textContent =
            registro.tipo === 'activo' ? etiquetaHistorial(registro) : (esCreacion ? 'Creado — ' : '') + (ETIQUETAS_TIPO_HISTORIAL[registro.tipo] || registro.tipo);
        document.getElementById('hist-panel-fecha').textContent = formatearFecha(registro.fecha);

        const esPassword = registro.tipo === 'password' || registro.tipo === 'activo';
        document.getElementById('hist-panel-cambio-wrap').classList.toggle('oculto', esPassword);
        if (!esPassword) {
            document.getElementById('hist-panel-cambio').textContent = esCreacion
                ? `Valor inicial: ${registro.valorNuevo ?? '(vacío)'}`
                : `${registro.valorAnterior ?? '(vacío)'} → ${registro.valorNuevo ?? '(vacío)'}`;
        }

        document.getElementById('hist-panel-overlay').classList.remove('oculto');
    }

    function activarMensajesValidacionNativa() {
        document.querySelectorAll('input[required], select[required], textarea[required]').forEach(campo => {
            campo.addEventListener('invalid', (evento) => {
                evento.preventDefault();

                const label = document.querySelector(`label[for="${campo.id}"]`);
                const nombreCampo = label ? label.textContent.trim() : 'Este campo';

                let mensaje;
                if (campo.validity.valueMissing) {
                    mensaje = `${nombreCampo} es obligatorio`;
                } else if (campo.validity.typeMismatch && campo.type === 'email') {
                    mensaje = `Escribe un correo válido en "${nombreCampo}"`;
                } else if (campo.validity.tooShort) {
                    mensaje = `${nombreCampo} debe tener al menos ${campo.minLength} caracteres`;
                } else if (campo.validity.rangeUnderflow) {
                    mensaje = `${nombreCampo} debe ser mayor o igual a ${campo.min}`;
                } else if (campo.validity.rangeOverflow) {
                    mensaje = `${nombreCampo} debe ser menor o igual a ${campo.max}`;
                } else if (campo.validity.patternMismatch) {
                    mensaje = `El formato de "${nombreCampo}" no es válido`;
                } else {
                    mensaje = `Revisa el campo "${nombreCampo}"`;
                }

                mostrarMensaje(mensaje, 'error');
                campo.focus();
            });
        });
    }

    activarMensajesValidacionNativa();

    function establecerMinimoFechaHoraActual(inputId) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const ahora = new Date();
        ahora.setMinutes(ahora.getMinutes() - ahora.getTimezoneOffset());
        input.min = ahora.toISOString().slice(0, 16);
    }
    document.querySelectorAll('input[name="p-duracion"], input[name="ep-duracion"]').forEach(radio => {
        radio.addEventListener('change', () => {
            establecerMinimoFechaHoraActual('p-fechaVencimiento');
            establecerMinimoFechaHoraActual('ep-fechaVencimiento');
        });
    });

    document.getElementById('hist-panel-cerrar')?.addEventListener('click', () => {
        document.getElementById('hist-panel-overlay').classList.add('oculto');
    });

    document.getElementById('hist-panel-overlay')?.addEventListener('click', (evento) => {
        if (evento.target.id === 'hist-panel-overlay') {
            document.getElementById('hist-panel-overlay').classList.add('oculto');
        }
    });

    document.getElementById('hist-filtro-entidad')?.addEventListener('change', () => {
        historialTipoActivo = 'todos';
        cargarHistorialGlobal();
    });

    historialBuscarDebounced = debounce(cargarHistorialGlobal, 400);
    document.getElementById('hist-buscar')?.addEventListener('input', historialBuscarDebounced);

    document.querySelectorAll('input[name="p-duracion"]').forEach(radio => {
        radio.addEventListener('change', () => {
            document.getElementById('p-fechaVencimiento-wrap').classList.toggle(
                'oculto',
                document.querySelector('input[name="p-duracion"]:checked').value !== 'temporal'
            );
        });
    });

    document.querySelectorAll('input[name="ep-duracion"]').forEach(radio => {
        radio.addEventListener('change', () => {
            document.getElementById('ep-fechaVencimiento-wrap').classList.toggle(
                'oculto',
                document.querySelector('input[name="ep-duracion"]:checked').value !== 'temporal'
            );
        });
    });

    document.getElementById('btn-eliminar-local')?.addEventListener('click', async () => {
        const idLocal = document.getElementById('e-idLocal').value;
        const nombreLocal = document.getElementById('e-solo-nombre').textContent;

        const resultado = await Swal.fire({
            title: '¿Eliminar local?',
            text: `Se eliminará "${nombreLocal}" y dejará de aparecer en la plataforma. Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545'
        });
        if (!resultado.isConfirmed) return;

        try {
            const r = await fetch('api/eliminar_mi_local.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idLocal })
            });
            const res = await r.json();
            mostrarMensaje(res.mensaje, res.exito ? 'exito' : 'error');
            if (res.exito) {
                await mostrarSelectorPerfilesLocal();
            }
        } catch (e) {
            mostrarMensaje('Error de conexión con el servidor', 'error');
        }
    });


        // ------------------------------------------------------------
    // Colapsar sidebar a solo íconos
    // ------------------------------------------------------------
    const barraLateralEl = document.getElementById('barra-lateral');
    const btnColapsarSidebar = document.getElementById('btn-colapsar-sidebar');

    function aplicarEstadoSidebar() {
        const colapsada = localStorage.getItem('sidebarColapsada') === '1';
        barraLateralEl?.classList.toggle('colapsada', colapsada);
    }

    btnColapsarSidebar?.addEventListener('click', () => {
        const estaColapsada = barraLateralEl.classList.toggle('colapsada');
        localStorage.setItem('sidebarColapsada', estaColapsada ? '1' : '0');
    });

    aplicarEstadoSidebar();

    // ------------------------------------------------------------
    // Mi Perfil (Admin)
    // ------------------------------------------------------------
       document.getElementById('btn-mi-perfil')?.addEventListener('click', () => {
        mostrarVistaLogin('vista-mi-perfil');
        cargarMiPerfil();
    });

    document.getElementById('btn-mi-cuenta-cliente')?.addEventListener('click', () => {
        mostrarVistaLogin('vista-mi-cuenta-cliente');
        cargarMiCuentaCliente();
    });

    document.getElementById('btn-mi-cuenta-comerciante')?.addEventListener('click', () => {
        mostrarVistaLogin('vista-mi-cuenta-comerciante');
        cargarMiCuentaComerciante();
    });
    async function cargarMiPerfil() {
        const infoSoloLectura = document.getElementById('mp-info-solo-lectura');
        const formEditar = document.getElementById('form-editar-mi-perfil');

        formEditar.classList.add('oculto');
        infoSoloLectura.classList.remove('oculto');

        try {
            const r = await fetch('api/buscar_superadmin.php');
            const res = await r.json();

            if (!res.exito) {
                mostrarMensaje(res.mensaje || 'No se pudo cargar tu perfil', 'error');
                return;
            }

            const a = res.admin;
            document.getElementById('mp-solo-nombre').textContent = a.nombreCompleto;
            document.getElementById('mp-solo-correo').textContent = a.correo;
            document.getElementById('mp-nombreCompleto').value = a.nombreCompleto;
            document.getElementById('mp-correo').value = a.correo;
        } catch (e) {
            mostrarMensaje('Error al cargar tu perfil', 'error');
        }
    }

    document.getElementById('btn-editar-mi-perfil')?.addEventListener('click', () => {
        document.getElementById('mp-info-solo-lectura').classList.add('oculto');
        document.getElementById('form-editar-mi-perfil').classList.remove('oculto');
    });

    document.getElementById('form-editar-mi-perfil')?.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const nombreCompleto = document.getElementById('mp-nombreCompleto').value.trim();
        const correo = document.getElementById('mp-correo').value.trim();

        try {
            const r = await fetch('api/editar_superadmin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nombreCompleto, correo })
            });
            const res = await r.json();

            mostrarMensaje(res.mensaje, res.exito ? 'exito' : 'error');

            if (res.exito) {
                actualizarIndicadorSesion({ ...usuarioSesionActual, nombre: nombreCompleto });
                await cargarMiPerfil();
            }
        } catch (e) {
            mostrarMensaje('Error de conexión con el servidor', 'error');
        }
    });

     activarValidacionPassword(
        document.getElementById('mp-password-nueva'),
        document.getElementById('mp-password-msg')
    );

    document.querySelectorAll('.toggle-pwd[data-toggle-para]').forEach(boton => {
        boton.addEventListener('click', () => {
            const input = document.getElementById(boton.dataset.togglePara);
            if (!input) return;
            if (input.type === 'password') {
                input.type = 'text';
                boton.textContent = '🙈';
            } else {
                input.type = 'password';
                boton.textContent = '👁️';
            }
        });
    });

    document.getElementById('form-cambiar-password-superadmin')?.addEventListener('submit', (evento) => {
        evento.preventDefault();

        const passwordActual = document.getElementById('mp-password-actual').value;
        const passwordNueva = document.getElementById('mp-password-nueva').value;

        Swal.fire({
            title: '¿Cambiar tu contraseña?',
            text: 'Vas a necesitar la nueva contraseña la próxima vez que inicies sesión.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, cambiarla',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#8E7CC3',
            cancelButtonColor: '#6B7280'
        }).then(async (resultado) => {
            if (!resultado.isConfirmed) return;

            try {
                const r = await fetch('api/cambiar_password_superadmin.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ passwordActual, passwordNueva })
                });
                const res = await r.json();

                mostrarMensaje(res.mensaje, res.exito ? 'exito' : 'error');

                if (res.exito) {
                    document.getElementById('mp-password-actual').value = '';
                    document.getElementById('mp-password-nueva').value = '';
                }
            } catch (e) {
                mostrarMensaje('No se pudo conectar con el servidor para cambiar tu contraseña. Revisa tu conexión e intenta de nuevo.', 'error');
            }
        });
    });

    // ------------------------------------------------------------
    // Ver Productos (Admin)
    // ------------------------------------------------------------
    function confirmarEliminarProducto(idProducto, nombreProducto, tarjeta) {
        Swal.fire({
            title: `¿Eliminar "${nombreProducto}"?`,
            text: 'Este producto se marcará como inactivo y dejará de verse en la app.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#8E7CC3'
        }).then(async (resultado) => {
            if (!resultado.isConfirmed) return;

            try {
                const r = await fetch('api/eliminar_producto.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ idProducto })
                });
                const res = await r.json();

                if (res.exito) {
                    tarjeta.remove();
                    mostrarMensaje('Producto eliminado correctamente', 'exito');
                } else {
                    mostrarMensaje(res.mensaje || 'No se pudo eliminar el producto', 'error');
                }
            } catch (e) {
                mostrarMensaje('Error al eliminar el producto', 'error');
            }
        });
    }

    async function cargarProductosAdmin() {
        const contenedor = document.getElementById('lista-productos-admin');
        contenedor.innerHTML = '<p class="ayuda">Cargando productos...</p>';

        const parametros = new URLSearchParams();

        const nombre = document.getElementById('pa-buscar').value.trim();
        if (nombre) parametros.set('nombre', nombre);

        const soloActivos = document.getElementById('pa-inactivos').checked ? '0' : '1';
        parametros.set('soloActivos', soloActivos);

        try {
            const r = await fetch(`api/listar_productos_admin.php?${parametros.toString()}`);
            const res = await r.json();

            if (!res.exito || res.productos.length === 0) {
                contenedor.innerHTML = '<p class="ayuda">No se encontraron productos.</p>';
                return;
            }

            contenedor.innerHTML = '';

            res.productos.forEach(producto => {
                const tarjeta = document.createElement('div');
                tarjeta.className = 'tarjeta tarjeta-con-borrar';

                const precioFinal = producto.porcentajeDescuento
                    ? (producto.precioOriginal * (1 - producto.porcentajeDescuento / 100)).toFixed(2)
                    : producto.precioOriginal;

                const precioHtml = producto.porcentajeDescuento
                    ? `<s>₡${producto.precioOriginal}</s> ₡${precioFinal}`
                    : `₡${producto.precioOriginal}`;

                tarjeta.innerHTML = `
                    <button type="button" class="tarjeta-local-btn-eliminar" aria-label="Eliminar producto"><i data-lucide="trash-2"></i></button>
                    ${producto.imagen
                        ? `<img src="imagenes/${producto.imagen}" alt="${producto.nombre}" class="imagen-producto">`
                        : `<div class="imagen-producto imagen-producto-vacia"><i data-lucide="package"></i></div>`}
                    <h4>${producto.nombre}</h4>
                    <p class="etiqueta-tipo">${producto.categoria}</p>
                    <p>Local: ${producto.nombreLocal}</p>
                    <p>${precioHtml}</p>
                    ${producto.activo ? '' : '<p><span class="etiqueta-inactivo">Inactivo</span></p>'}
                `;

                contenedor.appendChild(tarjeta);

                tarjeta.querySelector('.tarjeta-local-btn-eliminar').addEventListener('click', (evento) => {
                    evento.stopPropagation();
                    confirmarEliminarProducto(producto.idProducto, producto.nombre, tarjeta);
                });
            });

            if (window.lucide) lucide.createIcons();
        } catch (e) {
            contenedor.innerHTML = '<p class="ayuda error">Error al cargar los productos.</p>';
        }
    }

    document.getElementById('pa-buscar')?.addEventListener('input', debounce(() => cargarProductosAdmin(), 300));
    document.getElementById('pa-inactivos')?.addEventListener('change', () => cargarProductosAdmin());
    // ------------------------------------------------------------
    // Mis Productos (Comerciante)
    // ------------------------------------------------------------
    let misProductosCache = [];

    function crearTarjetaMiProducto(p) {
        const div = document.createElement('div');
        div.className = 'tarjeta tarjeta-con-borrar';

        const precioHtml = p.porcentajeDescuento
            ? `<s>₡${p.precioOriginal}</s> ₡${p.precioFinal} <span class="etiqueta-tipo">-${p.porcentajeDescuento}%</span>`
            : `₡${p.precioOriginal}`;

        div.innerHTML = `
            <button type="button" class="tarjeta-local-btn-editar" aria-label="Editar producto"><i data-lucide="pencil"></i></button>
            <button type="button" class="tarjeta-local-btn-eliminar" aria-label="Eliminar producto"><i data-lucide="trash-2"></i></button>
            ${p.imagen
                ? `<img src="imagenes/${p.imagen}" alt="${escaparHtml(p.nombre)}" class="imagen-producto">`
                : `<div class="imagen-producto imagen-producto-vacia"><i data-lucide="package"></i></div>`}
            <h4>${escaparHtml(p.nombre)}</h4>
            <p class="etiqueta-tipo">${escaparHtml(p.nombreLocal)}</p>
            <p>${precioHtml}</p>
            <p>${p.agotado ? '<span class="ayuda error">Agotado</span>' : `Disponibles: ${p.cantidadDisponible}`}</p>
        `;

                div.querySelector('.tarjeta-local-btn-editar').addEventListener('click', (evento) => {
            evento.stopPropagation();
            abrirModalEditarMiProducto(p.idProducto);
        });

        div.querySelector('.tarjeta-local-btn-eliminar').addEventListener('click', (evento) => {
            evento.stopPropagation();
            Swal.fire({
                title: `¿Eliminar "${p.nombre}"?`,
                text: 'Este producto se marcará como inactivo y dejará de verse en la app.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#DC2626',
                cancelButtonColor: '#8E7CC3'
            }).then(async (resultado) => {
                if (!resultado.isConfirmed) return;

                try {
                    const r = await fetch('api/eliminar_mi_producto.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ idProducto: p.idProducto })
                    });
                    const res = await r.json();
                    if (res.exito) {
                        div.remove();
                        mostrarMensaje('Producto eliminado correctamente', 'exito');
                    } else {
                        mostrarMensaje(res.mensaje || 'No se pudo eliminar el producto', 'error');
                    }
                } catch (e) {
                    mostrarMensaje('No se pudo conectar con el servidor para eliminar el producto. Revisa tu conexión e intenta de nuevo.', 'error');
                }
            });
        });

        return div;
    }

    function renderizarMisProductos(productos) {
        const contenedor = document.getElementById('lista-mis-productos');
        if (!contenedor) return;

        if (productos.length === 0) {
            contenedor.innerHTML = '<p class="ayuda">Todavía no tienes productos registrados.</p>';
            return;
        }

        contenedor.innerHTML = '';
        productos.forEach(p => contenedor.appendChild(crearTarjetaMiProducto(p)));
        if (window.lucide) lucide.createIcons();
    }

    async function cargarMisProductos() {
        const contenedor = document.getElementById('lista-mis-productos');
        if (!contenedor) return;

        contenedor.innerHTML = '<p class="ayuda">Cargando productos...</p>';

        try {
            const r = await fetch('api/listar_mis_productos.php');
            const res = await r.json();

            if (!res.exito) {
                contenedor.innerHTML = `<p class="ayuda error">${escaparHtml(res.mensaje || 'No se pudieron cargar tus productos')}</p>`;
                return;
            }

            misProductosCache = res.productos;
            renderizarMisProductos(misProductosCache);
        } catch (e) {
            contenedor.innerHTML = '<p class="ayuda error">Error de conexión al cargar tus productos.</p>';
        }
    }

    document.getElementById('mp-productos-buscar')?.addEventListener('input', debounce(() => {
        const termino = (document.getElementById('mp-productos-buscar')?.value || '').trim().toLowerCase();
        const filtrados = termino
            ? misProductosCache.filter(p =>
                p.nombre.toLowerCase().includes(termino) ||
                p.nombreLocal.toLowerCase().includes(termino))
            : misProductosCache;
        renderizarMisProductos(filtrados);
    }, 300));

    // ------------------------------------------------------------
    // Dashboard del Comerciante
    // ------------------------------------------------------------
    async function cargarDashboardComerciante() {
        const statLocales = document.getElementById('stat-mis-locales');
        if (!statLocales) return;

        statLocales.textContent = '—';
        document.getElementById('stat-mis-productos').textContent = '—';
        document.getElementById('stat-productos-agotados').textContent = '—';

        try {
            const [rLocales, rProductos] = await Promise.all([
                fetch('api/listar_locales_comerciante.php').then(r => r.json()),
                fetch('api/listar_mis_productos.php').then(r => r.json())
            ]);

            const locales = rLocales.exito ? rLocales.locales : [];
            const productos = rProductos.exito ? rProductos.productos : [];

            statLocales.textContent = locales.filter(l => l.activo).length;
            document.getElementById('stat-mis-productos').textContent = productos.length;
            document.getElementById('stat-productos-agotados').textContent = productos.filter(p => p.agotado).length;

            if (window.lucide) lucide.createIcons();
        } catch (e) {
            mostrarMensaje('No se pudo cargar tu dashboard', 'error');
        }
    }

        // ------------------------------------------------------------
    // Modal: Editar Mi Local
    // ------------------------------------------------------------
    async function abrirModalEditarLocal(idLocal) {
        try {
            const r = await fetch(`api/buscar_local.php?id=${idLocal}`);
            const res = await r.json();

            if (!res.exito) {
                mostrarMensaje(res.mensaje || 'No se pudo cargar el local', 'error');
                return;
            }

            const { local } = res;
            document.getElementById('mel-idLocal').value = local.idLocal;
            document.getElementById('mel-tipoLocal').value = local.tipoLocal ?? '';
            document.getElementById('mel-nombreLocal').value = local.nombreLocal;
            document.getElementById('mel-descripcion').value = local.descripcion ?? '';
            document.getElementById('mel-telefono').value = local.telefono;

            document.getElementById('modal-editar-local').classList.remove('oculto');
        } catch (e) {
            mostrarMensaje('Error al cargar el local', 'error');
        }
    }

    function cerrarModalEditarLocal() {
        document.getElementById('modal-editar-local').classList.add('oculto');
    }

    document.getElementById('modal-editar-local-cerrar')?.addEventListener('click', cerrarModalEditarLocal);
    document.getElementById('modal-editar-local')?.addEventListener('click', (evento) => {
        if (evento.target.id === 'modal-editar-local') cerrarModalEditarLocal();
    });

    document.getElementById('form-modal-editar-local')?.addEventListener('submit', (evento) => {
        evento.preventDefault();

        Swal.fire({
            title: '¿Guardar estos cambios?',
            text: 'Se van a actualizar los datos de este local.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Seguir editando',
            confirmButtonColor: '#8E7CC3',
            cancelButtonColor: '#6B7280'
        }).then(async (resultado) => {
            if (!resultado.isConfirmed) return;

            const datos = new FormData();
            datos.append('idLocal', document.getElementById('mel-idLocal').value);
            datos.append('nombreTipoLocal', document.getElementById('mel-tipoLocal').value);
            datos.append('nombreLocal', document.getElementById('mel-nombreLocal').value);
            datos.append('descripcion', document.getElementById('mel-descripcion').value);
            datos.append('telefono', document.getElementById('mel-telefono').value);

            const archivoLogo = document.getElementById('mel-logo').files[0];
            if (archivoLogo) datos.append('logo', archivoLogo);

            try {
                const r = await fetch('api/editar_local.php', { method: 'POST', body: datos });
                const res = await r.json();

                mostrarMensaje(res.mensaje, res.exito ? 'exito' : 'error');

                if (res.exito) {
                    cerrarModalEditarLocal();
                    await mostrarSelectorPerfilesLocal();
                }
            } catch (e) {
                mostrarMensaje('No se pudo conectar con el servidor para guardar los cambios. Revisa tu conexión e intenta de nuevo.', 'error');
            }
        });
    });

    // ------------------------------------------------------------
    // Modal: Editar Mi Producto
    // ------------------------------------------------------------
    async function abrirModalEditarMiProducto(idProducto) {
        try {
            const r = await fetch(`api/buscar_producto.php?id=${idProducto}`);
            const res = await r.json();

            if (!res.exito) {
                mostrarMensaje(res.mensaje || 'No se pudo cargar el producto', 'error');
                return;
            }

            const p = res.producto;
            document.getElementById('mep-idProducto').value = p.idProducto;
            document.getElementById('mep-tipoProducto').value = p.tipoProducto ?? '';
            document.getElementById('mep-nombre').value = p.nombre;
            document.getElementById('mep-precio').value = p.precioOriginal;
            document.getElementById('mep-descuento').value = p.porcentajeDescuento ?? '';
            document.getElementById('mep-descripcion').value = p.descripcion ?? '';
            document.getElementById('mep-cantidad').value = p.cantidadDisponible;

            document.getElementById('modal-editar-producto-mp').classList.remove('oculto');
        } catch (e) {
            mostrarMensaje('Error al cargar el producto', 'error');
        }
    }

    function cerrarModalEditarMiProducto() {
        document.getElementById('modal-editar-producto-mp').classList.add('oculto');
    }

    document.getElementById('modal-editar-producto-mp-cerrar')?.addEventListener('click', cerrarModalEditarMiProducto);
    document.getElementById('modal-editar-producto-mp')?.addEventListener('click', (evento) => {
        if (evento.target.id === 'modal-editar-producto-mp') cerrarModalEditarMiProducto();
    });

    document.getElementById('form-modal-editar-producto')?.addEventListener('submit', (evento) => {
        evento.preventDefault();

        Swal.fire({
            title: '¿Guardar estos cambios?',
            text: 'Se van a actualizar los datos de este producto.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Seguir editando',
            confirmButtonColor: '#8E7CC3',
            cancelButtonColor: '#6B7280'
        }).then(async (resultado) => {
            if (!resultado.isConfirmed) return;

            const datos = new FormData();
            datos.append('idProducto', document.getElementById('mep-idProducto').value);
            datos.append('nombreTipoProducto', document.getElementById('mep-tipoProducto').value);
            datos.append('nombre', document.getElementById('mep-nombre').value);
            datos.append('precioOriginal', document.getElementById('mep-precio').value);
            datos.append('porcentajeDescuento', document.getElementById('mep-descuento').value);
            datos.append('descripcion', document.getElementById('mep-descripcion').value);
            datos.append('cantidadDisponible', document.getElementById('mep-cantidad').value);

            const archivoImagen = document.getElementById('mep-imagen').files[0];
            if (archivoImagen) datos.append('imagen', archivoImagen);

            try {
                const r = await fetch('api/editar_producto.php', { method: 'POST', body: datos });
                const res = await r.json();

                mostrarMensaje(res.mensaje, res.exito ? 'exito' : 'error');

                if (res.exito) {
                    cerrarModalEditarMiProducto();
                    await cargarMisProductos();
                }
            } catch (e) {
                mostrarMensaje('No se pudo conectar con el servidor para guardar los cambios. Revisa tu conexión e intenta de nuevo.', 'error');
            }
        });
    });
});
