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
    const inputCedulaComerciante = document.getElementById('c-cedula');
    const mensajeCedulaComerciante = document.getElementById('c-cedula-msg');
    const inputCorreoComerciante = document.getElementById('c-correo');
    const mensajeCorreoComerciante = document.getElementById('c-correo-msg');

    const verificarCedulaComercianteDebounced = debounce(async () => {
        const cedula = inputCedulaComerciante.value;
        mensajeCedulaComerciante.textContent = '';
        mensajeCedulaComerciante.className = 'ayuda';
        if (cedula.length !== 9) return;

        try {
            const r = await fetch(`api/verificar_cedula.php?cedula=${cedula}`);
            const res = await r.json();
            mensajeCedulaComerciante.textContent = res.existe ? 'Esta cédula ya está registrada' : 'Cédula disponible';
            mensajeCedulaComerciante.className = res.existe ? 'ayuda error' : 'ayuda exito';
        } catch (e) {}
    }, 400);

    inputCedulaComerciante.addEventListener('input', () => {
        inputCedulaComerciante.value = soloDigitos(inputCedulaComerciante.value).slice(0, 9);
        verificarCedulaComercianteDebounced();
    });

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

        const datos = {
            nombre: document.getElementById('c-nombre').value,
            alias: document.getElementById('c-alias').value,
            cedula: inputCedulaComerciante.value,
            correo: inputCorreoComerciante.value,
            password: document.getElementById('c-password').value
        };

        try {
            const r = await fetch('api/registrar_comerciante.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            });
            const res = await r.json();

            mostrarMensaje(res.mensaje, res.exito ? 'exito' : 'error');

            if (res.exito) {
                sessionStorage.setItem('cedulaComercianteActual', datos.cedula);
                evento.target.reset();
                mensajeCedulaComerciante.textContent = '';
                mensajeCorreoComerciante.textContent = '';
            }
        } catch (e) {
            mostrarMensaje('Error de conexión con el servidor', 'error');
        }
    });

    // AUTOCOMPLETADO DE TIPO DE LOCAL (reutilizable)
    function activarAutocompletadoTipoLocal(inputEl, listaEl) {
        const buscar = debounce(async () => {
            const texto = inputEl.value.trim();
            listaEl.innerHTML = '';
            listaEl.classList.add('oculto');

            if (texto.length < 2) return;

            try {
                const r = await fetch(`api/buscar_tipos_local.php?texto=${encodeURIComponent(texto)}`);
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

    activarAutocompletadoTipoLocal(
        document.getElementById('l-tipoLocal'),
        document.getElementById('l-tipo-sugerencias')
    );
    activarAutocompletadoTipoLocal(
        document.getElementById('e-tipoLocal'),
        document.getElementById('e-tipo-sugerencias')
    );

    // SELECTS EN CASCADA: PROVINCIA -> CANTÓN -> DISTRITO
    const selectProvincia = document.getElementById('l-provincia');
    const selectCanton = document.getElementById('l-canton');
    const selectDistrito = document.getElementById('l-distrito');

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

    // REGISTRAR LOCAL: identificar comerciante por cédula
    const inputCedulaLocal = document.getElementById('l-cedula');
    const infoComerciante = document.getElementById('l-comerciante-info');
    const inputIdComerciante = document.getElementById('l-idComerciante');

    const buscarComercianteDebounced = debounce(async () => {
        const cedula = inputCedulaLocal.value;
        infoComerciante.textContent = '';
        infoComerciante.className = 'ayuda';
        if (cedula.length !== 9) return;

        try {
            const r = await fetch(`api/buscar_comerciante_por_cedula.php?cedula=${cedula}`);
            const res = await r.json();

            if (res.encontrado) {
                inputIdComerciante.value = res.idComerciante;
                infoComerciante.textContent = `Comerciante: ${res.nombre} (${res.alias})`;
                infoComerciante.className = 'ayuda exito';
            } else {
                inputIdComerciante.value = '';
                infoComerciante.textContent = 'No existe un comerciante con esa cédula. Regístrate primero.';
                infoComerciante.className = 'ayuda error';
            }
        } catch (e) {
            infoComerciante.textContent = 'No se pudo verificar la cédula';
            infoComerciante.className = 'ayuda error';
        }
    }, 400);

    inputCedulaLocal.addEventListener('input', () => {
        inputCedulaLocal.value = soloDigitos(inputCedulaLocal.value).slice(0, 9);
        inputIdComerciante.value = '';
        buscarComercianteDebounced();
    });

    const cedulaGuardada = sessionStorage.getItem('cedulaComercianteActual');
    if (cedulaGuardada) {
        inputCedulaLocal.value = cedulaGuardada;
        buscarComercianteDebounced();
        sessionStorage.removeItem('cedulaComercianteActual');
    }

    // ombre del local: disponibilidad
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

    formatearTelefono(document.getElementById('l-telefono'));
    formatearTelefono(document.getElementById('e-telefono'));

    //Registrar Local
    const formLocal = document.getElementById('form-local');

    formLocal.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        if (!inputIdComerciante.value) {
            mostrarMensaje('Ingresa una cédula de comerciante válida antes de continuar', 'error');
            return;
        }

        const datos = {
            idComerciante: inputIdComerciante.value,
            nombreTipoLocal: document.getElementById('l-tipoLocal').value,
            nombreLocal: inputNombreLocal.value,
            descripcion: document.getElementById('l-descripcion').value,
            productosAOfrecer: document.getElementById('l-productos').value,
            telefono: document.getElementById('l-telefono').value,
            correo: document.getElementById('l-correo').value,
            idProvincia: selectProvincia.value,
            idCanton: selectCanton.value,
            idDistrito: selectDistrito.value,
            direccionExacta: document.getElementById('l-direccion').value,
            referencia: document.getElementById('l-referencia').value
        };

        try {
            const r = await fetch('api/registrar_local.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            });
            const res = await r.json();

            mostrarMensaje(res.mensaje, res.exito ? 'exito' : 'error');

            if (res.exito) {
                formLocal.reset();
                infoComerciante.textContent = '';
                mensajeNombreLocal.textContent = '';
                inputIdComerciante.value = '';
                selectCanton.innerHTML = '<option value="">Primero elige provincia</option>';
                selectCanton.disabled = true;
                selectDistrito.innerHTML = '<option value="">Primero elige cantón</option>';
                selectDistrito.disabled = true;
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

        try {
            const r = await fetch('api/listar_locales.php');
            const res = await r.json();

            if (!res.exito || res.locales.length === 0) {
                contenedor.innerHTML = '<p>No hay locales registrados todavía.</p>';
                return;
            }

            contenedor.innerHTML = '';

            res.locales.forEach(local => {
                const tarjeta = document.createElement('div');
                tarjeta.className = 'tarjeta tarjeta-clic';
                tarjeta.innerHTML = `
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
            document.getElementById('e-productos').value = local.productosAOfrecer ?? '';
            document.getElementById('e-telefono').value = local.telefono;
            document.getElementById('e-correo').value = local.correo;

            document.getElementById('e-ubicacion-texto').textContent =
                `${ubicacion.provincia}, ${ubicacion.canton}, ${ubicacion.distrito} — ${ubicacion.direccionExacta}` +
                (ubicacion.referencia ? ` (${ubicacion.referencia})` : '');

            panelLista.classList.add('oculto');
            panelDetalle.classList.remove('oculto');
        } catch (e) {
            mostrarMensaje('Error al cargar el detalle del local', 'error');
        }
    }

    document.getElementById('btn-volver-lista').addEventListener('click', mostrarListaLocales);

    document.getElementById('form-editar-local').addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const datos = {
            idLocal: document.getElementById('e-idLocal').value,
            nombreTipoLocal: document.getElementById('e-tipoLocal').value,
            nombreLocal: document.getElementById('e-nombreLocal').value,
            descripcion: document.getElementById('e-descripcion').value,
            productosAOfrecer: document.getElementById('e-productos').value,
            telefono: document.getElementById('e-telefono').value,
            correo: document.getElementById('e-correo').value
        };

        try {
            const r = await fetch('api/editar_local.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
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

});