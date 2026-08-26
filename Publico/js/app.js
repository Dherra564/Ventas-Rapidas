document.addEventListener('DOMContentLoaded', () => {

    const botonesMenu = document.querySelectorAll('.menu-boton');
    const vistas = document.querySelectorAll('.vista');
    const cajaMensaje = document.getElementById('mensaje');

    botonesMenu.forEach(boton => {
        boton.addEventListener('click', () => {
            botonesMenu.forEach(b => b.classList.remove('activo'));
            boton.classList.add('activo');

            vistas.forEach(v => v.classList.add('oculto'));
            document.getElementById(boton.dataset.vista).classList.remove('oculto');

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

            if (boton.dataset.vista === 'vista-compras') {
                cargarDatosCompras();
            }

            if (boton.dataset.vista === 'vista-resenas') {
                cargarDatosResenas();
            }

            if (boton.dataset.vista === 'vista-historiales') {
                cargarUsuariosHistorial();
            }
        });
    });

    function mostrarMensaje(texto, tipo) {
        cajaMensaje.textContent = texto;
        cajaMensaje.className = 'mensaje ' + tipo;
        setTimeout(() => { cajaMensaje.className = 'mensaje oculto'; }, 4000);
    }

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

    const inputIdentificacionComerciante = document.getElementById('c-numeroIdentificacion');
    const mensajeIdentificacionComerciante = document.getElementById('c-identificacion-msg');
    const inputCorreoComerciante = document.getElementById('c-correo');
    const mensajeCorreoComerciante = document.getElementById('c-correo-msg');

    const verificarIdentificacionComercianteDebounced = debounce(async () => {
        const numeroIdentificacion = inputIdentificacionComerciante.value.trim();
        mensajeIdentificacionComerciante.textContent = '';
        mensajeIdentificacionComerciante.className = 'ayuda';
        if (numeroIdentificacion.length < 5) return;

        try {
            const r = await fetch(`api/verificar_identificacion.php?numeroIdentificacion=${encodeURIComponent(numeroIdentificacion)}`);
            const res = await r.json();
            mensajeIdentificacionComerciante.textContent = res.existe ? 'Esta identificación ya está registrada' : 'Identificación disponible';
            mensajeIdentificacionComerciante.className = res.existe ? 'ayuda error' : 'ayuda exito';
        } catch (e) {}
    }, 400);

    inputIdentificacionComerciante.addEventListener('input', verificarIdentificacionComercianteDebounced);

    const verificarCorreoComercianteDebounced = debounce(async () => {
        const correo = inputCorreoComerciante.value.trim();
        mensajeCorreoComerciante.textContent = '';
        mensajeCorreoComerciante.className = 'ayuda';
        if (!correo.includes('@') || !correo.includes('.')) return;

        try {
            const r = await fetch(`api/verificar_correo.php?correo=${encodeURIComponent(correo)}`);
            const res = await r.json();
            mensajeCorreoComerciante.textContent = res.existe ? 'Este correo ya está registrado' : 'Correo disponible';
            mensajeCorreoComerciante.className = res.existe ? 'ayuda error' : 'ayuda exito';
        } catch (e) {}
    }, 500);

    inputCorreoComerciante.addEventListener('input', verificarCorreoComercianteDebounced);

    document.getElementById('form-comerciante').addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const numeroIdentificacion = inputIdentificacionComerciante.value.trim();

        const datos = new FormData();
        datos.append('nombre', document.getElementById('c-nombre').value);
        datos.append('alias', document.getElementById('c-alias').value);
        datos.append('tipoIdentificacion', document.getElementById('c-tipoIdentificacion').value);
        datos.append('numeroIdentificacion', numeroIdentificacion);
        datos.append('correo', inputCorreoComerciante.value);
        datos.append('password', document.getElementById('c-password').value);

        const archivoFoto = document.getElementById('c-fotoPerfil').files[0];
        if (archivoFoto) {
            datos.append('fotoPerfil', archivoFoto);
        }

        try {
            const r = await fetch('api/registrar_comerciante.php', {
                method: 'POST',
                body: datos
            });
            const res = await r.json();

            mostrarMensaje(res.mensaje, res.exito ? 'exito' : 'error');

            if (res.exito) {
                evento.target.reset();
                mensajeIdentificacionComerciante.textContent = '';
                mensajeCorreoComerciante.textContent = '';
            }
        } catch (e) {
            mostrarMensaje('Error de conexión con el servidor', 'error');
        }
    });

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
            } catch (e) {}
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
            } catch (e) {}
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
            } catch (e) {}
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
            } catch (e) {}
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
        } catch (e) {}
    }, 400);

    inputNombreLocal.addEventListener('input', verificarNombreLocalDebounced);

    const inputCorreoLocal = document.getElementById('l-correo');
    const mensajeCorreoLocal = document.getElementById('l-correo-msg');

    const verificarCorreoLocalDebounced = debounce(async () => {
        const correo = inputCorreoLocal.value.trim();
        mensajeCorreoLocal.textContent = '';
        mensajeCorreoLocal.className = 'ayuda';
        if (!correo.includes('@') || !correo.includes('.')) return;

        try {
            const r = await fetch(`api/verificar_correo_local.php?correo=${encodeURIComponent(correo)}`);
            const res = await r.json();
            mensajeCorreoLocal.textContent = res.existe ? 'Ese correo ya está registrado en otro local' : 'Correo disponible';
            mensajeCorreoLocal.className = res.existe ? 'ayuda error' : 'ayuda exito';
        } catch (e) {}
    }, 500);

    inputCorreoLocal.addEventListener('input', verificarCorreoLocalDebounced);

    formatearTelefono(document.getElementById('l-telefono'));
    formatearTelefono(document.getElementById('e-telefono'));

    const formLocal = document.getElementById('form-local');
    const inputLatitudLocal = document.getElementById('l-latitud');
    const inputLongitudLocal = document.getElementById('l-longitud');
    const mensajeGpsLocal = document.getElementById('l-gps-msg');

    document.getElementById('btn-gps-local')?.addEventListener('click', async () => {
        if (mensajeGpsLocal) mensajeGpsLocal.textContent = 'Obteniendo ubicación...';
        try {
            const coords = await obtenerCoordenadasGPS();
            if (inputLatitudLocal) inputLatitudLocal.value = coords.lat;
            if (inputLongitudLocal) inputLongitudLocal.value = coords.lng;
            if (mensajeGpsLocal) mensajeGpsLocal.textContent = `Ubicación capturada (${coords.lat.toFixed(5)}, ${coords.lng.toFixed(5)})`;
        } catch (e) {
            if (mensajeGpsLocal) mensajeGpsLocal.textContent = 'No se pudo obtener tu ubicación. Puedes registrar el local sin GPS.';
        }
    });

    formLocal.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const datos = new FormData();
        datos.append('nombreTipoLocal', document.getElementById('l-tipoLocal').value);
        datos.append('nombreLocal', inputNombreLocal.value);
        datos.append('descripcion', document.getElementById('l-descripcion').value);
        datos.append('telefono', document.getElementById('l-telefono').value);
        datos.append('correo', document.getElementById('l-correo').value);
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
            }
        } catch (e) {
            mostrarMensaje('Error de conexión con el servidor', 'error');
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
        } catch (e) {}
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
        } catch (e) {}
    }, 500);

    inputCorreoCliente.addEventListener('input', verificarCorreoClienteDebounced);

    document.getElementById('form-cliente').addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const datos = new FormData();
        datos.append('nombreCompleto', document.getElementById('cl-nombreCompleto').value);
        datos.append('tipoIdentificacion', document.getElementById('cl-tipoIdentificacion').value);
        datos.append('numeroIdentificacion', inputIdentificacionCliente.value.trim());
        datos.append('correo', inputCorreoCliente.value);
        datos.append('password', document.getElementById('cl-password').value);
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

            mostrarMensaje(res.mensaje, res.exito ? 'exito' : 'error');

            if (res.exito) {
                evento.target.reset();
                mensajeIdentificacionCliente.textContent = '';
                mensajeCorreoCliente.textContent = '';
                selectCantonCliente.innerHTML = '<option value="">Primero elige provincia</option>';
                selectCantonCliente.disabled = true;
                selectDistritoCliente.innerHTML = '<option value="">Primero elige cantón</option>';
                selectDistritoCliente.disabled = true;
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
                const hayFiltros = parametros.toString() !== '';
                contenedor.innerHTML = hayFiltros
                    ? '<p>No se encontraron locales con esos filtros.</p>'
                    : '<p>No hay locales registrados todavía.</p>';
                return;
            }

            contenedor.innerHTML = '';

            res.locales.forEach(local => {
                const tarjeta = document.createElement('div');
                tarjeta.className = 'tarjeta tarjeta-clic';
                tarjeta.innerHTML = `
                    ${local.logo ? `<img src="imagenes/${local.logo}" alt="${local.nombreLocal}" class="imagen-producto">` : ''}
                    <h3>${local.nombreLocal}</h3>
                    <p class="etiqueta-tipo">${local.tipoLocal ?? ''}</p>
                    <p>${local.descripcion ?? ''}</p>
                    <p>📞 ${local.telefono} &nbsp; ✉️ ${local.correo}</p>
                `;
                tarjeta.addEventListener('click', () => abrirDetalleLocal(local.idLocal));
                contenedor.appendChild(tarjeta);
            });
        } catch (e) {
            contenedor.innerHTML = '<p>Error al cargar los locales.</p>';
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

            document.getElementById('e-idLocal').value = local.idLocal;
            document.getElementById('e-tipoLocal').value = local.tipoLocal ?? '';
            document.getElementById('e-nombreLocal').value = local.nombreLocal;
            document.getElementById('e-descripcion').value = local.descripcion ?? '';
            document.getElementById('e-telefono').value = local.telefono;
            document.getElementById('e-correo').value = local.correo;
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
        } catch (e) {
            mostrarMensaje('Error al cargar el detalle del local', 'error');
        }
    }

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
                    <button type="button" class="boton-secundario btn-editar-producto" data-id="${producto.idProducto}">Editar</button>
                `;
                contenedor.appendChild(tarjeta);
                tarjeta.querySelector('.btn-editar-producto').addEventListener('click', () => {
                    abrirEditarProducto(producto.idProducto, idLocal);
                });
            });
        } catch (e) {
            contenedor.innerHTML = '<p>Error al cargar los productos.</p>';
        }
    }

    document.getElementById('btn-volver-lista').addEventListener('click', mostrarListaLocales);

    document.getElementById('form-editar-local').addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const datos = new FormData();
        datos.append('idLocal', document.getElementById('e-idLocal').value);
        datos.append('nombreTipoLocal', document.getElementById('e-tipoLocal').value);
        datos.append('nombreLocal', document.getElementById('e-nombreLocal').value);
        datos.append('descripcion', document.getElementById('e-descripcion').value);
        datos.append('telefono', document.getElementById('e-telefono').value);
        datos.append('correo', document.getElementById('e-correo').value);

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
                mostrarListaLocales();
                cargarLocales();
            }
        } catch (e) {
            mostrarMensaje('Error de conexión con el servidor', 'error');
        }
    });
    
    const inputNombreLocalProducto = document.getElementById('p-nombreLocal');
    const infoLocalProducto = document.getElementById('p-local-info');
    const inputIdLocalProducto = document.getElementById('p-idLocal');

    const buscarLocalDebounced = debounce(async () => {
        const nombre = inputNombreLocalProducto.value.trim();
        infoLocalProducto.textContent = '';
        infoLocalProducto.className = 'ayuda';
        if (nombre.length < 3) return;

        try {
            const r = await fetch(`api/buscar_local_por_nombre.php?nombre=${encodeURIComponent(nombre)}`);
            const res = await r.json();

            if (res.encontrado) {
                inputIdLocalProducto.value = res.idLocal;
                infoLocalProducto.textContent = 'Local encontrado';
                infoLocalProducto.className = 'ayuda exito';
            } else {
                inputIdLocalProducto.value = '';
                infoLocalProducto.textContent = 'No existe un local con ese nombre exacto';
                infoLocalProducto.className = 'ayuda error';    
            }
        } catch (e) {}
    }, 400);

    inputNombreLocalProducto.addEventListener('input', () => {
        inputIdLocalProducto.value = '';
        buscarLocalDebounced();
    });

    activarAutocompletadoTipo(
        document.getElementById('p-tipoProducto'),
        document.getElementById('p-tipo-sugerencias'),
        'api/buscar_tipos_producto.php'
    );

    document.getElementById('form-producto').addEventListener('submit', async (evento) => {
        evento.preventDefault();

        if (!inputIdLocalProducto.value) {
            mostrarMensaje('Ingresa el nombre exacto de un local válido antes de continuar', 'error');
            return;
        }

        const datos = new FormData();
        datos.append('idLocal', inputIdLocalProducto.value);
        datos.append('nombreTipoProducto', document.getElementById('p-tipoProducto').value);
        datos.append('nombre', document.getElementById('p-nombre').value);
        datos.append('precioOriginal', document.getElementById('p-precio').value);
        datos.append('porcentajeDescuento', document.getElementById('p-descuento').value);
        datos.append('descripcion', document.getElementById('p-descripcion').value);
        datos.append('cantidadDisponible', document.getElementById('p-cantidad').value);

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
                infoLocalProducto.textContent = '';
                inputIdLocalProducto.value = '';
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

    document.getElementById('btn-cerrar-editar-producto').addEventListener('click', () => {
        panelEditarProducto.classList.add('oculto');
        panelDetalle.classList.remove('oculto');
    });

    document.getElementById('form-editar-producto').addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const datos = new FormData();
        datos.append('idProducto', document.getElementById('ep-idProducto').value);
        datos.append('nombreTipoProducto', document.getElementById('ep-tipoProducto').value);
        datos.append('nombre', document.getElementById('ep-nombre').value);
        datos.append('precioOriginal', document.getElementById('ep-precio').value);
        datos.append('porcentajeDescuento', document.getElementById('ep-descuento').value);
        datos.append('descripcion', document.getElementById('ep-descripcion').value);
        datos.append('cantidadDisponible', document.getElementById('ep-cantidad').value);

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
        contenedor.innerHTML = '<p>Cargando...</p>';

        const soloActivos = !document.getElementById('chk-inactivos-comerciantes').checked;

        try {
            const r = await fetch(`api/listar_comerciantes.php?soloActivos=${soloActivos ? '1' : '0'}`);
            const res = await r.json();

            if (!res.exito || res.comerciantes.length === 0) {
                contenedor.innerHTML = '<p>No hay comerciantes registrados todavía.</p>';
                return;
            }

            contenedor.innerHTML = '';

            res.comerciantes.forEach(c => {
                const tarjeta = document.createElement('div');
                tarjeta.className = 'tarjeta tarjeta-clic';
                tarjeta.innerHTML = `
                    ${c.fotoPerfil ? `<img src="imagenes/${c.fotoPerfil}" alt="${c.nombre}" class="imagen-producto">` : ''}
                    <h3>${c.nombre} ${!c.activo ? '<span class="ayuda error">(inactivo)</span>' : ''}</h3>
                    <p class="etiqueta-tipo">${c.alias}</p>
                    <p>✉️ ${c.correo}</p>
                `;
                tarjeta.addEventListener('click', () => abrirDetalleComerciante(c.idComerciante));
                contenedor.appendChild(tarjeta);
            });
        } catch (e) {
            contenedor.innerHTML = '<p>Error al cargar los comerciantes.</p>';
        }
    }

    document.getElementById('chk-inactivos-comerciantes').addEventListener('change', cargarComerciantes);

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
            document.getElementById('dc-nombre').value = c.nombre;
            document.getElementById('dc-alias').value = c.alias;
            document.getElementById('dc-correo').value = c.correo;
            document.getElementById('dc-identificacion').textContent = c.numeroIdentificacion;
            document.getElementById('dc-password').value = '';

            const imgFoto = document.getElementById('dc-foto-actual');
            if (c.fotoPerfil) {
                imgFoto.src = `imagenes/${c.fotoPerfil}`;
                imgFoto.classList.remove('oculto');
            } else {
                imgFoto.classList.add('oculto');
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

    document.getElementById('btn-volver-comerciantes').addEventListener('click', mostrarListaComerciantes);

    document.getElementById('form-editar-comerciante').addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const datos = new FormData();
        datos.append('idComerciante', document.getElementById('dc-idComerciante').value);
        datos.append('nombre', document.getElementById('dc-nombre').value);
        datos.append('alias', document.getElementById('dc-alias').value);
        datos.append('correo', document.getElementById('dc-correo').value);
        datos.append('password', document.getElementById('dc-password').value);

        const archivoFoto = document.getElementById('dc-fotoPerfil').files[0];
        if (archivoFoto) {
            datos.append('fotoPerfil', archivoFoto);
        }

        try {
            const r = await fetch('api/editar_comerciante.php', {
                method: 'POST',
                body: datos
            });
            const res = await r.json();

            mostrarMensaje(res.mensaje, res.exito ? 'exito' : 'error');

            if (res.exito) {
                mostrarListaComerciantes();
                cargarComerciantes();
            }
        } catch (e) {
            mostrarMensaje('Error de conexión con el servidor', 'error');
        }
    });

    document.getElementById('btn-desactivar-comerciante').addEventListener('click', async () => {
        const idComerciante = document.getElementById('dc-idComerciante').value;
        const nombre = document.getElementById('dc-nombre').value;

        if (!confirm(`¿Seguro que querés desactivar a "${nombre}"? Sus locales seguirán existiendo, pero no podrá ingresar más.`)) {
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
            mostrarMensaje('Error de conexión con el servidor', 'error');
        }
    });

    document.getElementById('btn-activar-comerciante').addEventListener('click', async () => {
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
            mostrarMensaje('Error de conexión con el servidor', 'error');
        }
    });

    const panelListaClientes = document.getElementById('panel-lista-clientes');
    const panelDetalleCliente = document.getElementById('panel-detalle-cliente');

    function mostrarListaClientes() {
        panelDetalleCliente.classList.add('oculto');
        panelListaClientes.classList.remove('oculto');
    }

    async function cargarClientes() {
        const contenedor = document.getElementById('lista-clientes');
        contenedor.innerHTML = '<p>Cargando...</p>';

        const soloActivos = !document.getElementById('chk-inactivos-clientes').checked;

        try {
            const r = await fetch(`api/listar_clientes.php?soloActivos=${soloActivos ? '1' : '0'}`);
            const res = await r.json();

            if (!res.exito || res.clientes.length === 0) {
                contenedor.innerHTML = '<p>No hay clientes registrados todavía.</p>';
                return;
            }

            contenedor.innerHTML = '';

            res.clientes.forEach(c => {
                const tarjeta = document.createElement('div');
                tarjeta.className = 'tarjeta tarjeta-clic';
                tarjeta.innerHTML = `
                    ${c.fotoPerfil ? `<img src="imagenes/${c.fotoPerfil}" alt="${c.nombreCompleto}" class="imagen-producto">` : ''}
                    <h3>${c.nombreCompleto} ${!c.activo ? '<span class="ayuda error">(inactivo)</span>' : ''}</h3>
                    <p>✉️ ${c.correo}</p>
                `;
                tarjeta.addEventListener('click', () => abrirDetalleCliente(c.idCliente));
                contenedor.appendChild(tarjeta);
            });
        } catch (e) {
            contenedor.innerHTML = '<p>Error al cargar los clientes.</p>';
        }
    }

    document.getElementById('chk-inactivos-clientes').addEventListener('change', cargarClientes);

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
            document.getElementById('dcl-nombreCompleto').value = c.nombreCompleto;
            document.getElementById('dcl-correo').value = c.correo;
            document.getElementById('dcl-identificacion').textContent = c.numeroIdentificacion;
            document.getElementById('dcl-direccion').textContent =
                u.direccionExacta + (u.referencia ? ` (${u.referencia})` : '');
            document.getElementById('dcl-password').value = '';

            const imgFoto = document.getElementById('dcl-foto-actual');
            if (c.fotoPerfil) {
                imgFoto.src = `imagenes/${c.fotoPerfil}`;
                imgFoto.classList.remove('oculto');
            } else {
                imgFoto.classList.add('oculto');
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

            cargarLocalesQueSigueCliente(c.idCliente);
        } catch (e) {
            mostrarMensaje('Error al cargar el detalle del cliente', 'error');
        }
    }

    document.getElementById('btn-volver-clientes').addEventListener('click', mostrarListaClientes);

    document.getElementById('form-editar-cliente').addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const datos = new FormData();
        datos.append('idCliente', document.getElementById('dcl-idCliente').value);
        datos.append('nombreCompleto', document.getElementById('dcl-nombreCompleto').value);
        datos.append('correo', document.getElementById('dcl-correo').value);
        datos.append('password', document.getElementById('dcl-password').value);

        const archivoFoto = document.getElementById('dcl-fotoPerfil').files[0];
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
            mostrarMensaje('Error de conexión con el servidor', 'error');
        }
    });

    document.getElementById('btn-desactivar-cliente').addEventListener('click', async () => {
        const idCliente = document.getElementById('dcl-idCliente').value;
        const nombre = document.getElementById('dcl-nombreCompleto').value;

        if (!confirm(`¿Seguro que querés desactivar a "${nombre}"?`)) {
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

    document.getElementById('btn-activar-cliente').addEventListener('click', async () => {
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

    activarValidacionIdentificacion(
        document.getElementById('cl-tipoIdentificacion'),
        document.getElementById('cl-numeroIdentificacion'),
        document.getElementById('cl-identificacion-msg')
    );

    const selectProvinciaFiltro = document.getElementById('f-provincia');
    const selectCantonFiltro = document.getElementById('f-canton');
    const selectDistritoFiltro = document.getElementById('f-distrito');

    activarCascadaUbicacion(selectProvinciaFiltro, selectCantonFiltro, selectDistritoFiltro);

    const buscarLocalesDebounced = debounce(cargarLocales, 400);

    document.getElementById('f-nombre').addEventListener('input', buscarLocalesDebounced);
    selectProvinciaFiltro.addEventListener('change', cargarLocales);
    selectCantonFiltro.addEventListener('change', cargarLocales);
    selectDistritoFiltro.addEventListener('change', cargarLocales);

    document.getElementById('btn-limpiar-filtros').addEventListener('click', () => {
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

    document.getElementById('btn-agregar-local-producto').addEventListener('click', async () => {
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

    async function cargarLocalesQueSigueCliente(idCliente) {
        const contenedor = document.getElementById('dcl-locales-lista');
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
                            cargarLocalesQueSigueCliente(idCliente);
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

    document.getElementById('btn-seguir-local').addEventListener('click', async () => {
        const idCliente = document.getElementById('dcl-idCliente').value;
        const nombreLocal = document.getElementById('dcl-agregar-local-nombre').value.trim();
        const mensaje = document.getElementById('dcl-seguir-local-msg');

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

    async function cargarDatosCompras() {
        try {
            const [clientes, locales] = await Promise.all([
                obtenerClientesActivos(),
                obtenerLocalesActivos()
            ]);

            llenarSelect(document.getElementById('compra-cliente'), clientes, 'idCliente', 'nombreCompleto');
            llenarSelect(document.getElementById('compras-historial-cliente'), clientes, 'idCliente', 'nombreCompleto');
            llenarSelect(document.getElementById('compra-local'), locales, 'idLocal', 'nombreLocal');
            cargarRankingCompras();
        } catch (e) {
            mostrarMensaje('No se pudieron cargar los datos de compras', 'error');
        }
    }

    document.getElementById('form-compra').addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const idCliente = document.getElementById('compra-cliente').value;
        const idLocal = document.getElementById('compra-local').value;

        try {
            const r = await fetch('api/registrar_compra.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idCliente, idLocal })
            });
            const res = await r.json();
            mostrarMensaje(res.mensaje, res.exito ? 'exito' : 'error');

            if (res.exito) {
                document.getElementById('compras-historial-cliente').value = idCliente;
                await cargarHistorialCompras();
                await cargarRankingCompras();
            }
        } catch (e) {
            mostrarMensaje('Error de conexión al registrar la compra', 'error');
        }
    });

    async function cargarHistorialCompras() {
        const idCliente = document.getElementById('compras-historial-cliente').value;
        const fecha = document.getElementById('compras-fecha').value;
        const contenedor = document.getElementById('lista-compras');

        if (!idCliente) {
            contenedor.innerHTML = '<p class="ayuda">Selecciona un cliente.</p>';
            return;
        }

        contenedor.innerHTML = '<p class="ayuda">Cargando...</p>';

        try {
            const parametros = new URLSearchParams({ idCliente });
            if (fecha) parametros.set('fecha', fecha);

            const r = await fetch(`api/listar_compras_cliente.php?${parametros.toString()}`);
            const res = await r.json();

            if (!res.exito) {
                contenedor.innerHTML = `<p class="ayuda error">${escaparHtml(res.mensaje || 'No se pudieron consultar las compras')}</p>`;
                return;
            }

            if (res.compras.length === 0) {
                contenedor.innerHTML = '<p class="ayuda">No hay compras registradas para esta consulta.</p>';
                return;
            }

            contenedor.innerHTML = '';
            res.compras.forEach(compra => {
                const tarjeta = document.createElement('div');
                tarjeta.className = 'tarjeta';
                tarjeta.innerHTML = `
                    <h3>${escaparHtml(compra.nombreLocal)}</h3>
                    <p><strong>Compra #${compra.idRegistroCompra}</strong></p>
                    <p>${escaparHtml(formatearFecha(compra.fechaCompra))}</p>
                `;
                contenedor.appendChild(tarjeta);
            });
        } catch (e) {
            contenedor.innerHTML = '<p class="ayuda error">Error de conexión al consultar las compras.</p>';
        }
    }

    document.getElementById('btn-buscar-compras').addEventListener('click', cargarHistorialCompras);

    async function cargarRankingCompras() {
        const contenedor = document.getElementById('ranking-compras');
        contenedor.innerHTML = '<p class="ayuda">Cargando...</p>';

        try {
            const r = await fetch('api/locales_mas_comprados.php?limite=5');
            const res = await r.json();

            if (!res.exito || res.locales.length === 0) {
                contenedor.innerHTML = '<p class="ayuda">Todavía no hay compras suficientes para mostrar un ranking.</p>';
                return;
            }

            contenedor.innerHTML = '';
            res.locales.forEach((local, indice) => {
                const tarjeta = document.createElement('div');
                tarjeta.className = 'tarjeta';
                tarjeta.innerHTML = `
                    <h3>${indice + 1}. ${escaparHtml(local.nombreLocal)}</h3>
                    <p>${local.totalCompras} compra${local.totalCompras === 1 ? '' : 's'} registrada${local.totalCompras === 1 ? '' : 's'}</p>
                `;
                contenedor.appendChild(tarjeta);
            });
        } catch (e) {
            contenedor.innerHTML = '<p class="ayuda error">No se pudo cargar el ranking.</p>';
        }
    }

    async function cargarDatosResenas() {
        try {
            const [clientes, locales] = await Promise.all([
                obtenerClientesActivos(),
                obtenerLocalesActivos()
            ]);

            llenarSelect(document.getElementById('resena-cliente'), clientes, 'idCliente', 'nombreCompleto');
            llenarSelect(document.getElementById('resena-local'), locales, 'idLocal', 'nombreLocal');
            llenarSelect(document.getElementById('resena-filtro-local'), locales, 'idLocal', 'nombreLocal');
        } catch (e) {
            mostrarMensaje('No se pudieron cargar los datos de reseñas', 'error');
        }
    }

    document.getElementById('form-resena').addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const idCliente = document.getElementById('resena-cliente').value;
        const idLocal = document.getElementById('resena-local').value;
        const puntuacion = document.getElementById('resena-puntuacion').value;
        const comentario = document.getElementById('resena-comentario').value.trim();

        try {
            const r = await fetch('api/registrar_resenia.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idLocal, puntuacion, comentario })
            });
            const res = await r.json();
            mostrarMensaje(res.mensaje, res.exito ? 'exito' : 'error');

            if (res.exito) {
                document.getElementById('resena-comentario').value = '';
                document.getElementById('resena-filtro-local').value = idLocal;
                await cargarResenasLocal();
            }
        } catch (e) {
            mostrarMensaje('Error de conexión al publicar la reseña', 'error');
        }
    });

    async function cargarResenasLocal() {
        const idLocal = document.getElementById('resena-filtro-local').value;
        const contenedor = document.getElementById('lista-resenas');
        const resumen = document.getElementById('resena-resumen');

        if (!idLocal) {
            resumen.textContent = 'Selecciona un local para ver su calificación.';
            contenedor.innerHTML = '';
            return;
        }

        contenedor.innerHTML = '<p class="ayuda">Cargando...</p>';

        try {
            const r = await fetch(`api/listar_resenias_local.php?idLocal=${encodeURIComponent(idLocal)}`);
            const res = await r.json();

            if (!res.exito) {
                resumen.textContent = 'No se pudo obtener la calificación.';
                contenedor.innerHTML = `<p class="ayuda error">${escaparHtml(res.mensaje || 'Error al cargar reseñas')}</p>`;
                return;
            }

            const promedio = res.promedio === null ? 'Sin calificación' : `${Number(res.promedio).toFixed(1)} / 5`;
            resumen.textContent = `Promedio: ${promedio} · ${res.total} reseña${res.total === 1 ? '' : 's'}`;

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
                    <div class="acciones-tarjeta">
                        <button type="button" class="boton-pequeno boton-editar btn-editar-resena">Editar</button>
                        <button type="button" class="boton-peligro btn-eliminar-resena">Eliminar</button>
                    </div>
                `;

                tarjeta.querySelector('.btn-editar-resena').addEventListener('click', () => editarResenaDesdeLista(resenia));
                tarjeta.querySelector('.btn-eliminar-resena').addEventListener('click', () => eliminarResenaDesdeLista(resenia.idResenia));
                contenedor.appendChild(tarjeta);
            });
        } catch (e) {
            resumen.textContent = 'No se pudo obtener la calificación.';
            contenedor.innerHTML = '<p class="ayuda error">Error de conexión al cargar reseñas.</p>';
        }
    }

    document.getElementById('btn-cargar-resenas').addEventListener('click', cargarResenasLocal);

    async function editarResenaDesdeLista(resenia) {
        const comentario = prompt('Edita el comentario:', resenia.comentario);
        if (comentario === null) return;

        const puntuacionTexto = prompt('Nueva puntuación del 1 al 5:', String(resenia.puntuacion));
        if (puntuacionTexto === null) return;

        const puntuacion = Number(puntuacionTexto);
        if (!Number.isInteger(puntuacion) || puntuacion < 1 || puntuacion > 5) {
            mostrarMensaje('La puntuación debe ser un número entero del 1 al 5', 'error');
            return;
        }

        try {
            const r = await fetch('api/editar_resenia.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idResenia: resenia.idResenia, comentario, puntuacion })
            });
            const respuesta = await r.json();
            mostrarMensaje(respuesta.mensaje, respuesta.exito ? 'exito' : 'error');
            if (respuesta.exito) cargarResenasLocal();
        } catch (e) {
            mostrarMensaje('Error de conexión al editar la reseña', 'error');
        }
    }

    async function eliminarResenaDesdeLista(idResenia) {
        if (!confirm('¿Seguro que querés eliminar esta reseña?')) return;

        try {
            const r = await fetch('api/eliminar_resenia.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idResenia })
            });
            const res = await r.json();
            mostrarMensaje(res.mensaje, res.exito ? 'exito' : 'error');
            if (res.exito) cargarResenasLocal();
        } catch (e) {
            mostrarMensaje('Error de conexión al eliminar la reseña', 'error');
        }
    }

    async function cargarUsuariosHistorial() {
        const tipo = document.getElementById('historial-tipo').value;
        const select = document.getElementById('historial-usuario');
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

            document.getElementById('historial-password-lista').innerHTML = '<p class="ayuda">Selecciona un usuario y consulta su historial.</p>';
            document.getElementById('historial-fotos-lista').innerHTML = '<p class="ayuda">Selecciona un usuario y consulta su historial.</p>';
        } catch (e) {
            select.innerHTML = '<option value="">No se pudieron cargar usuarios</option>';
        }
    }

    document.getElementById('historial-tipo').addEventListener('change', cargarUsuariosHistorial);

    async function consultarHistorialUsuario() {
        const tipoUsuario = document.getElementById('historial-tipo').value;
        const idUsuario = document.getElementById('historial-usuario').value;
        const listaPassword = document.getElementById('historial-password-lista');
        const listaFotos = document.getElementById('historial-fotos-lista');

        if (!idUsuario) {
            mostrarMensaje('Selecciona un usuario para consultar el historial', 'error');
            return;
        }

        listaPassword.innerHTML = '<p class="ayuda">Cargando...</p>';
        listaFotos.innerHTML = '<p class="ayuda">Cargando...</p>';

        try {
            const parametros = new URLSearchParams({ idUsuario, tipoUsuario });
            const r = await fetch(`api/listar_historial_usuario.php?${parametros.toString()}`);
            const res = await r.json();

            if (!res.exito) {
                listaPassword.innerHTML = `<p class="ayuda error">${escaparHtml(res.mensaje)}</p>`;
                listaFotos.innerHTML = `<p class="ayuda error">${escaparHtml(res.mensaje)}</p>`;
                return;
            }

            if (res.passwords.length === 0) {
                listaPassword.innerHTML = '<p class="ayuda">No hay cambios de contraseña registrados.</p>';
            } else {
                listaPassword.innerHTML = '';
                res.passwords.forEach(item => {
                    const tarjeta = document.createElement('div');
                    tarjeta.className = 'tarjeta';
                    tarjeta.innerHTML = `
                        <h3>${item.exitoso ? 'Cambio exitoso' : 'Intento fallido'}</h3>
                        <p>${escaparHtml(formatearFecha(item.fecha))}</p>
                    `;
                    listaPassword.appendChild(tarjeta);
                });
            }

            if (res.fotos.length === 0) {
                listaFotos.innerHTML = '<p class="ayuda">No hay cambios de foto registrados.</p>';
            } else {
                listaFotos.innerHTML = '';
                res.fotos.forEach(item => {
                    const tarjeta = document.createElement('div');
                    tarjeta.className = 'tarjeta';
                    tarjeta.innerHTML = `
                        ${item.rutaNueva ? `<img src="imagenes/${encodeURIComponent(item.rutaNueva)}" alt="Nueva foto" class="imagen-producto">` : ''}
                        <h3>Cambio de foto</h3>
                        <p>${escaparHtml(formatearFecha(item.fecha))}</p>
                    `;
                    listaFotos.appendChild(tarjeta);
                });
            }
        } catch (e) {
            listaPassword.innerHTML = '<p class="ayuda error">Error de conexión.</p>';
            listaFotos.innerHTML = '<p class="ayuda error">Error de conexión.</p>';
        }
    }

    document.getElementById('btn-ver-historial').addEventListener('click', consultarHistorialUsuario);

    document.getElementById('form-cambiar-password').addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const tipoUsuario = document.getElementById('historial-tipo').value;
        const idUsuario = document.getElementById('historial-usuario').value;
        const passwordActual = document.getElementById('historial-password-actual').value;
        const passwordNueva = document.getElementById('historial-password-nueva').value;

        if (!idUsuario) {
            mostrarMensaje('Selecciona un usuario antes de cambiar la contraseña', 'error');
            return;
        }

        try {
            const r = await fetch('api/cambiar_password_usuario.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idUsuario, tipoUsuario, passwordActual, passwordNueva })
            });
            const res = await r.json();
            mostrarMensaje(res.mensaje, res.exito ? 'exito' : 'error');

            document.getElementById('historial-password-actual').value = '';
            document.getElementById('historial-password-nueva').value = '';
            await consultarHistorialUsuario();
        } catch (e) {
            mostrarMensaje('Error de conexión al cambiar la contraseña', 'error');
        }
    });

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
            const esRegistro = idVista === 'vista-comerciante' || idVista === 'vista-cliente';
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
            actualizarIndicadorSesion(res.autenticado ? res.usuario : null);
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
        const indicador = document.getElementById('sesion-indicador');
        const texto = document.getElementById('sesion-texto');

        actualizarMenuPorRol(usuario ? usuario.tipo : null);

        if (!indicador || !texto) return;

        if (usuario) {
            texto.textContent = `Sesión: ${usuario.nombre} (${usuario.tipo})`;
            indicador.classList.remove('oculto');
        } else {
            indicador.classList.add('oculto');
        }
    }

    document.getElementById('form-login')?.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const tipo = document.getElementById('login-tipo').value;
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

        const endpoint = tipo === 'cliente' ? 'api/login_cliente.php' : 'api/login_comerciante.php';

        const datos = new FormData();
        datos.append('correo', correo);
        datos.append('password', password);

        try {
            const r = await fetch(endpoint, { method: 'POST', body: datos });
            const res = await r.json();

            mostrarMensaje(res.mensaje, res.exito ? 'exito' : 'error');

            if (res.exito) {
                actualizarIndicadorSesion(res.usuario);
                evento.target.reset();

                if (res.usuario.tipo === 'Cliente') {
                    try {
                        const coords = await obtenerCoordenadasGPS();
                        await fetch('api/actualizar_ubicacion_cliente.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ latitud: coords.lat, longitud: coords.lng })
                        });
                    } catch (e) {
                    }

                    mostrarVistaLogin('vista-listado');
                } else {
                    await mostrarSelectorPerfilesLocal();
                }
            }
        } catch (e) {
            mostrarMensaje('Error de conexión con el servidor', 'error');
        }
    });

    document.getElementById('btn-cerrar-sesion')?.addEventListener('click', async () => {
        try {
            await fetch('api/cerrar_sesion.php', { method: 'POST' });
        } catch (e) {}
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

    document.getElementById('link-crear-cuenta')?.addEventListener('click', (evento) => {
        evento.preventDefault();
        mostrarPanelElegirTipo();
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
                tarjeta.innerHTML = `
                    ${local.logo
                        ? `<img src="imagenes/${local.logo}" alt="${local.nombreLocal}">`
                        : `<div class="icono-perfil">🏪</div>`}
                    <span class="nombre-perfil">${local.nombreLocal}</span>
                    ${!local.activo ? '<span class="etiqueta-inactivo">Inactivo por falta de uso</span>' : ''}
                `;
                tarjeta.addEventListener('click', () => entrarPerfilLocal(local.idLocal));
                contenedor.appendChild(tarjeta);
            });

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

});