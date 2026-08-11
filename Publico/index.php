<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas Rápidas</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

    <header class="cabecera">
        <h1>Ventas Rápidas</h1>
        <nav class="menu">
            <button class="menu-boton activo" data-vista="vista-comerciante">Registrar Comerciante</button>
            <button class="menu-boton" data-vista="vista-local">Registrar Local</button>
            <button class="menu-boton" data-vista="vista-listado">Ver Locales</button>
        </nav>
    </header>

    <main class="contenedor">

        <div id="mensaje" class="mensaje oculto"></div>

        <!-- Vista: Registrar Comerciante -->
        <section id="vista-comerciante" class="vista">
            <h2>Registro de Comerciante</h2>
            <form id="form-comerciante" class="formulario">
                <label for="c-nombre">Nombre completo</label>
                <input type="text" id="c-nombre" required>

                <label for="c-alias">Alias</label>
                <input type="text" id="c-alias" required>

                <label for="c-cedula">Cédula</label>
                <input type="text" id="c-cedula" inputmode="numeric" maxlength="9" placeholder="9 dígitos" required>
                <span class="ayuda" id="c-cedula-msg"></span>

                <label for="c-correo">Correo</label>
                <input type="email" id="c-correo" placeholder="ejemplo@gmail.com" required>
                <span class="ayuda" id="c-correo-msg"></span>

                <label for="c-password">Contraseña</label>
                <input type="password" id="c-password" required>

                <button type="submit">Registrar Comerciante</button>
            </form>
        </section>

        <!-- Vista: Registrar Local -->
        <section id="vista-local" class="vista oculto">
            <h2>Registro de Local</h2>
            <form id="form-local" class="formulario">
                <label for="l-cedula">Comerciante (ingresa tu cédula)</label>
                <input type="text" id="l-cedula" inputmode="numeric" maxlength="9" placeholder="9 dígitos" required>
                <span class="ayuda" id="l-comerciante-info"></span>
                <input type="hidden" id="l-idComerciante">

                <label for="l-tipoLocal">Tipo de Local</label>
                <input type="text" id="l-tipoLocal" autocomplete="off" placeholder="Ej: Soda, Feria, Repostería..." required>
                <div class="sugerencias oculto" id="l-tipo-sugerencias"></div>

                <label for="l-nombreLocal">Nombre del Local</label>
                <input type="text" id="l-nombreLocal" required>
                <span class="ayuda" id="l-nombre-msg"></span>

                <label for="l-descripcion">Descripción</label>
                <textarea id="l-descripcion"></textarea>

                <label for="l-productos">Productos a ofrecer</label>
                <textarea id="l-productos" placeholder="Ej: empanadas, tamales, rosquillas..."></textarea>

                <label for="l-telefono">Teléfono</label>
                <input type="text" id="l-telefono" inputmode="numeric" placeholder="8888-8888" maxlength="9" required>

                <label for="l-correo">Correo</label>
                <input type="email" id="l-correo" placeholder="ejemplo@gmail.com" required>

                <hr>

                <label for="l-provincia">Provincia</label>
                <select id="l-provincia" required>
                    <option value="">Seleccione...</option>
                </select>

                <label for="l-canton">Cantón</label>
                <select id="l-canton" required disabled>
                    <option value="">Primero elige provincia</option>
                </select>

                <label for="l-distrito">Distrito</label>
                <select id="l-distrito" required disabled>
                    <option value="">Primero elige cantón</option>
                </select>

                <label for="l-direccion">Dirección exacta</label>
                <input type="text" id="l-direccion" required>

                <label for="l-referencia">Punto de referencia</label>
                <input type="text" id="l-referencia">

                <button type="submit">Registrar Local</button>
            </form>
        </section>

        <!-- Vista: Listado de locales -->
        <section id="vista-listado" class="vista oculto">

            <div id="panel-lista-locales">
                <h2>Locales Registrados</h2>
                <div id="lista-locales" class="tarjetas"></div>
            </div>

            <div id="panel-detalle-local" class="oculto">
                <button type="button" id="btn-volver-lista" class="boton-secundario">&larr; Volver al listado</button>
                <h2>Detalle del Local</h2>

                <div class="campo-lectura">
                    <strong>Ubicación registrada</strong>
                    <p id="e-ubicacion-texto"></p>
                </div>

                <form id="form-editar-local" class="formulario">
                    <input type="hidden" id="e-idLocal">

                    <label for="e-tipoLocal">Tipo de Local</label>
                    <input type="text" id="e-tipoLocal" autocomplete="off" required>
                    <div class="sugerencias oculto" id="e-tipo-sugerencias"></div>

                    <label for="e-nombreLocal">Nombre del Local</label>
                    <input type="text" id="e-nombreLocal" required>

                    <label for="e-descripcion">Descripción</label>
                    <textarea id="e-descripcion"></textarea>

                    <label for="e-productos">Productos a ofrecer</label>
                    <textarea id="e-productos"></textarea>

                    <label for="e-telefono">Teléfono</label>
                    <input type="text" id="e-telefono" inputmode="numeric" placeholder="8888-8888" maxlength="9" required>

                    <label for="e-correo">Correo</label>
                    <input type="email" id="e-correo" placeholder="ejemplo@gmail.com" required>

                    <button type="submit">Guardar Cambios</button>
                </form>
            </div>

        </section>

    </main>

    <script src="js/app.js"></script>
</body>
</html>