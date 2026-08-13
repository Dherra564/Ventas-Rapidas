document.addEventListener('DOMContentLoaded', () => {

    const botonesMenu = document.querySelectorAll('.menu-boton');
    const vistas = document.querySelectorAll('.vista');
    const cajaMensaje = document.getElementById('mensaje');

    // Navegación por pestañas
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

    // REGISTRAR COMERCIANTE
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
                sessionStorage.setItem('identificacionComercianteActual', numeroIdentificacion);
                evento.target.reset();
                mensajeIdentificacionComerciante.textContent = '';
                mensajeCorreoComerciante.textContent = '';
            }
        } catch (e) {
            mostrarMensaje('Error de conexión con el servidor', 'error');
        }
    });

    // AUTOCOMPLETADO DE TIPO (reutilizable para tipo de local y tipo de producto)
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

    // SELECTS EN CASCADA (reutilizable): PROVINCIA -> CANTÓN -> DISTRITO
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

    // REGISTRAR LOCAL: identificar comerciante por número de identificación
    const inputIdentificacionLocal = document.getElementById('l-numeroIdentificacion');
    const infoComerciante = document.getElementById('l-comerciante-info');
    const inputIdComerciante = document.getElementById('l-idComerciante');

    const buscarComercianteDebounced = debounce(async () => {
        const numeroIdentificacion = inputIdentificacionLocal.value.trim();
        infoComerciante.textContent = '';
        infoComerciante.className = 'ayuda';
        if (numeroIdentificacion.length < 5) return;

        try {
            const r = await fetch(`api/buscar_comerciante_por_identificacion.php?numeroIdentificacion=${encodeURIComponent(numeroIdentificacion)}`);
            const res = await r.json();

            if (res.encontrado) {
                inputIdComerciante.value = res.idComerciante;
                infoComerciante.textContent = `Comerciante: ${res.nombre} (${res.alias})`;
                infoComerciante.className = 'ayuda exito';
            } else {
                inputIdComerciante.value = '';
                infoComerciante.textContent = 'No existe un comerciante con esa identificación. Regístrate primero.';
                infoComerciante.className = 'ayuda error';
            }
        } catch (e) {
            infoComerciante.textContent = 'No se pudo verificar la identificación';
            infoComerciante.className = 'ayuda error';
        }
    }, 400);

    inputIdentificacionLocal.addEventListener('input', () => {
        inputIdComerciante.value = '';
        buscarComercianteDebounced();
    });

    const identificacionGuardada = sessionStorage.getItem('identificacionComercianteActual');
    if (identificacionGuardada) {
        inputIdentificacionLocal.value = identificacionGuardada;
        buscarComercianteDebounced();
        sessionStorage.removeItem('identificacionComercianteActual');
    }

    // Nombre del local: disponibilidad
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

    // Correo del local: disponibilidad
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

    // Registrar Local
    const formLocal = document.getElementById('form-local');

    formLocal.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        if (!inputIdComerciante.value) {
            mostrarMensaje('Ingresa un número de identificación de comerciante válido antes de continuar', 'error');
            return;
        }

        const datos = new FormData();
        datos.append('idComerciante', inputIdComerciante.value);
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
                infoComerciante.textContent = '';
                mensajeNombreLocal.textContent = '';
                inputIdComerciante.value = '';
                selectCantonLocal.innerHTML = '<option value="">Primero elige provincia</option>';
                selectCantonLocal.disabled = true;
                selectDistritoLocal.innerHTML = '<option value="">Primero elige cantón</option>';
                selectDistritoLocal.disabled = true;
            }
        } catch (e) {
            mostrarMensaje('Error de conexión con el servidor', 'error');
        }
    });

    // REGISTRAR CLIENTE
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
        datos.append('nombre', document.getElementById('c-nombre').value);
        datos.append('alias', document.getElementById('c-alias').value);
        datos.append('tipoIdentificacion', document.getElementById('c-tipoIdentificacion').value);
        datos.append('numeroIdentificacion', numeroIdentificacion);
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

    // VER LOCALES
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
                    <h4>${producto.nombre}</h4>
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
    
    // REGISTRAR PRODUCTO
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

    // EDITAR PRODUCTO
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

    // VER / EDITAR / DESACTIVAR COMERCIANTES
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

    // VER / EDITAR / DESACTIVAR CLIENTES
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

    // VALIDACIÓN DE IDENTIFICACIÓN SEGÚN TIPO (reutilizable)
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

    // BÚSQUEDA / FILTROS EN "VER LOCALES"
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

});