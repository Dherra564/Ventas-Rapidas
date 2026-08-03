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
        setTimeout(() => {
            cajaMensaje.className = 'mensaje oculto';
        }, 4000);
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

    // Cédula del proveedor: solo 9 dígitos + disponibilidad
    const inputCedulaProveedor = document.getElementById('p-cedula');
    const mensajeCedula = document.getElementById('p-cedula-msg');

    const verificarCedulaDebounced = debounce(async () => {
        const cedula = inputCedulaProveedor.value;
        mensajeCedula.textContent = '';
        mensajeCedula.className = 'ayuda';

        if (cedula.length !== 9) return;

        try {
            const respuesta = await fetch(`api/verificar_cedula.php?cedula=${cedula}`);
            const resultado = await respuesta.json();

            if (resultado.existe) {
                mensajeCedula.textContent = 'Esta cédula ya está registrada';
                mensajeCedula.className = 'ayuda error';
            } else {
                mensajeCedula.textContent = 'Cédula disponible';
                mensajeCedula.className = 'ayuda exito';
            }
        } catch (error) {
            // silencioso, no bloquea el formulario
        }
    }, 400);

    inputCedulaProveedor.addEventListener('input', () => {
        inputCedulaProveedor.value = soloDigitos(inputCedulaProveedor.value).slice(0, 9);
        verificarCedulaDebounced();
    });

    // Correo del proveedor: disponibilidad
    const inputCorreoProveedor = document.getElementById('p-correo');
    const mensajeCorreo = document.getElementById('p-correo-msg');

    const verificarCorreoDebounced = debounce(async () => {
        const correo = inputCorreoProveedor.value.trim();
        mensajeCorreo.textContent = '';
        mensajeCorreo.className = 'ayuda';

        if (!correo.includes('@') || !correo.includes('.')) return;

        try {
            const respuesta = await fetch(`api/verificar_correo.php?correo=${encodeURIComponent(correo)}`);
            const resultado = await respuesta.json();

            if (resultado.existe) {
                mensajeCorreo.textContent = 'Este correo ya está registrado';
                mensajeCorreo.className = 'ayuda error';
            } else {
                mensajeCorreo.textContent = 'Correo disponible';
                mensajeCorreo.className = 'ayuda exito';
            }
        } catch (error) {
            // silencioso
        }
    }, 500);

    inputCorreoProveedor.addEventListener('input', verificarCorreoDebounced);

    // Registrar Proveedor
    const formProveedor = document.getElementById('form-proveedor');

    formProveedor.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const datos = {
            nombre: document.getElementById('p-nombre').value,
            apellido: document.getElementById('p-apellido').value,
            cedula: inputCedulaProveedor.value,
            correo: inputCorreoProveedor.value,
            password: document.getElementById('p-password').value
        };

        try {
            const respuesta = await fetch('api/registrar_proveedor.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            });

            const resultado = await respuesta.json();

            mostrarMensaje(resultado.mensaje, resultado.exito ? 'exito' : 'error');

            if (resultado.exito) {
                sessionStorage.setItem('cedulaProveedorActual', datos.cedula);
                formProveedor.reset();
                mensajeCedula.textContent = '';
                mensajeCorreo.textContent = '';
            }

        } catch (error) {
            mostrarMensaje('Error de conexión con el servidor', 'error');
        }
    });

    // Registrar Local, identificar proveedor por cédula
    const inputCedulaLocal = document.getElementById('l-cedula');
    const infoProveedor = document.getElementById('l-proveedor-info');
    const inputIdProveedor = document.getElementById('l-idProveedor');

    const buscarProveedorDebounced = debounce(async () => {
        const cedula = inputCedulaLocal.value;
        infoProveedor.textContent = '';
        infoProveedor.className = 'ayuda';

        if (cedula.length !== 9) return;

        try {
            const respuesta = await fetch(`api/buscar_proveedor_por_cedula.php?cedula=${cedula}`);
            const resultado = await respuesta.json();

            if (resultado.encontrado) {
                inputIdProveedor.value = resultado.idProveedor;
                infoProveedor.textContent = `Proveedor: ${resultado.nombre} ${resultado.apellido}`;
                infoProveedor.className = 'ayuda exito';
            } else {
                inputIdProveedor.value = '';
                infoProveedor.textContent = 'No existe un proveedor con esa cédula. Regístrate primero.';
                infoProveedor.className = 'ayuda error';
            }
        } catch (error) {
            infoProveedor.textContent = 'No se pudo verificar la cédula';
            infoProveedor.className = 'ayuda error';
        }
    }, 400);

    inputCedulaLocal.addEventListener('input', () => {
        inputCedulaLocal.value = soloDigitos(inputCedulaLocal.value).slice(0, 9);
        inputIdProveedor.value = '';
        buscarProveedorDebounced();
    });

    // Si se acaba de registrar un proveedor, se autocompleta la cédula aquí y se usa una sola vez, luego se limpia
    const cedulaGuardada = sessionStorage.getItem('cedulaProveedorActual');
    if (cedulaGuardada) {
        inputCedulaLocal.value = cedulaGuardada;
        buscarProveedorDebounced();
        sessionStorage.removeItem('cedulaProveedorActual');
    }

    // Nombre del local, disponibilidad
    const inputNombreLocal = document.getElementById('l-nombreLocal');
    const mensajeNombreLocal = document.getElementById('l-nombre-msg');

    const verificarNombreLocalDebounced = debounce(async () => {
        const nombre = inputNombreLocal.value.trim();
        mensajeNombreLocal.textContent = '';
        mensajeNombreLocal.className = 'ayuda';

        if (nombre.length < 3) return;

        try {
            const respuesta = await fetch(`api/verificar_nombre_local.php?nombre=${encodeURIComponent(nombre)}`);
            const resultado = await respuesta.json();

            if (resultado.disponible) {
                mensajeNombreLocal.textContent = 'Nombre disponible';
                mensajeNombreLocal.className = 'ayuda exito';
            } else {
                mensajeNombreLocal.textContent = 'Ya existe un local con ese nombre';
                mensajeNombreLocal.className = 'ayuda error';
            }
        } catch (error) {
            // silencioso
        }
    }, 400);

    inputNombreLocal.addEventListener('input', verificarNombreLocalDebounced);

    // Formato del telefono 8888-8888
    function formatearTelefono(input) {
        input.addEventListener('input', () => {
            const digitos = soloDigitos(input.value).slice(0, 8);
            input.value = digitos.length > 4
                ? digitos.slice(0, 4) + '-' + digitos.slice(4)
                : digitos;
        });
    }

    formatearTelefono(document.getElementById('l-telefono'));
    formatearTelefono(document.getElementById('e-telefono'));

    // Registrar Local
    const formLocal = document.getElementById('form-local');

    formLocal.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        if (!inputIdProveedor.value) {
            mostrarMensaje('Ingresa una cédula de proveedor válida antes de continuar', 'error');
            return;
        }

        const datos = {
            idProveedor: inputIdProveedor.value,
            nombreLocal: inputNombreLocal.value,
            descripcion: document.getElementById('l-descripcion').value,
            telefono: document.getElementById('l-telefono').value,
            correo: document.getElementById('l-correo').value,
            provincia: document.getElementById('l-provincia').value,
            canton: document.getElementById('l-canton').value,
            distrito: document.getElementById('l-distrito').value,
            direccionExacta: document.getElementById('l-direccion').value,
            referencia: document.getElementById('l-referencia').value
        };

        try {
            const respuesta = await fetch('api/registrar_local.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            });

            const resultado = await respuesta.json();

            mostrarMensaje(resultado.mensaje, resultado.exito ? 'exito' : 'error');

            if (resultado.exito) {
                formLocal.reset();
                infoProveedor.textContent = '';
                mensajeNombreLocal.textContent = '';
                inputIdProveedor.value = '';
            }

        } catch (error) {
            mostrarMensaje('Error de conexión con el servidor', 'error');
        }
    });

    // Listar Locales
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
            const respuesta = await fetch('api/listar_locales.php');
            const resultado = await respuesta.json();

            if (!resultado.exito || resultado.locales.length === 0) {
                contenedor.innerHTML = '<p>No hay locales registrados todavía.</p>';
                return;
            }

            contenedor.innerHTML = '';

            resultado.locales.forEach(local => {
                const tarjeta = document.createElement('div');
                tarjeta.className = 'tarjeta tarjeta-clic';
                tarjeta.innerHTML = `
                    <h3>${local.nombreLocal}</h3>
                    <p>${local.descripcion ?? ''}</p>
                    <p>📞 ${local.telefono} &nbsp; ✉️ ${local.correo}</p>
                `;
                tarjeta.addEventListener('click', () => abrirDetalleLocal(local.idLocal));
                contenedor.appendChild(tarjeta);
            });

        } catch (error) {
            contenedor.innerHTML = '<p>Error al cargar los locales.</p>';
        }
    }

    // Detalle y edición de un local
    async function abrirDetalleLocal(idLocal) {
        try {
            const respuesta = await fetch(`api/buscar_local.php?id=${idLocal}`);
            const resultado = await respuesta.json();

            if (!resultado.exito) {
                mostrarMensaje(resultado.mensaje || 'No se pudo cargar el local', 'error');
                return;
            }

            const { local, ubicacion } = resultado;

            document.getElementById('e-idLocal').value = local.idLocal;
            document.getElementById('e-idProveedor').value = local.idProveedor;
            document.getElementById('e-idUbicacion').value = ubicacion.idUbicacion;

            document.getElementById('e-nombreLocal').value = local.nombreLocal;
            document.getElementById('e-descripcion').value = local.descripcion ?? '';
            document.getElementById('e-telefono').value = local.telefono;
            document.getElementById('e-correo').value = local.correo;

            document.getElementById('e-provincia').value = ubicacion.provincia;
            document.getElementById('e-canton').value = ubicacion.canton;
            document.getElementById('e-distrito').value = ubicacion.distrito;
            document.getElementById('e-direccion').value = ubicacion.direccionExacta;
            document.getElementById('e-referencia').value = ubicacion.referencia ?? '';

            panelLista.classList.add('oculto');
            panelDetalle.classList.remove('oculto');

        } catch (error) {
            mostrarMensaje('Error al cargar el detalle del local', 'error');
        }
    }

    document.getElementById('btn-volver-lista').addEventListener('click', mostrarListaLocales);

    document.getElementById('form-editar-local').addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const datos = {
            idLocal: document.getElementById('e-idLocal').value,
            idProveedor: document.getElementById('e-idProveedor').value,
            idUbicacion: document.getElementById('e-idUbicacion').value,
            nombreLocal: document.getElementById('e-nombreLocal').value,
            descripcion: document.getElementById('e-descripcion').value,
            telefono: document.getElementById('e-telefono').value,
            correo: document.getElementById('e-correo').value,
            provincia: document.getElementById('e-provincia').value,
            canton: document.getElementById('e-canton').value,
            distrito: document.getElementById('e-distrito').value,
            direccionExacta: document.getElementById('e-direccion').value,
            referencia: document.getElementById('e-referencia').value
        };

        try {
            const respuesta = await fetch('api/editar_local.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            });

            const resultado = await respuesta.json();

            mostrarMensaje(resultado.mensaje, resultado.exito ? 'exito' : 'error');

            if (resultado.exito) {
                mostrarListaLocales();
                cargarLocales();
            }

        } catch (error) {
            mostrarMensaje('Error de conexión con el servidor', 'error');
        }
    });

});