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
            <button class="menu-boton activo" data-vista="vista-proveedor">Registrar Proveedor</button>
            <button class="menu-boton" data-vista="vista-local">Registrar Local</button>
            <button class="menu-boton" data-vista="vista-listado">Ver Locales</button>
        </nav>
    </header>

    <main class="contenedor">

        <div id="mensaje" class="mensaje oculto"></div>

        <!-- Vista: Registrar Proveedor -->
        <section id="vista-proveedor" class="vista">
            <h2>Registro de Proveedor</h2>
            <form id="form-proveedor" class="formulario">
                <label for="p-nombre">Nombre</label>
                <input type="text" id="p-nombre" name="nombre" required>

                <label for="p-apellido">Apellido</label>
                <input type="text" id="p-apellido" name="apellido" required>

                <label for="p-cedula">Cédula</label>
                <input type="text" id="p-cedula" name="cedula" inputmode="numeric" maxlength="9" placeholder="9 dígitos" required>
                <span class="ayuda" id="p-cedula-msg"></span>

                <label for="p-correo">Correo</label>
                <input type="email" id="p-correo" name="correo" placeholder="ejemplo@gmail.com" required>
                <span class="ayuda" id="p-correo-msg"></span>

                <label for="p-password">Contraseña</label>
                <input type="password" id="p-password" name="password" required>

                <button type="submit">Registrar Proveedor</button>
            </form>
        </section>

        <!-- Vista: Registrar Local -->
        <section id="vista-local" class="vista oculto">
            <h2>Registro de Local</h2>
            <form id="form-local" class="formulario">
                <label for="l-cedula">Proveedor (ingresa tu cédula)</label>
                <input type="text" id="l-cedula" inputmode="numeric" maxlength="9" placeholder="9 dígitos" required>
                <span class="ayuda" id="l-proveedor-info"></span>
                <input type="hidden" id="l-idProveedor">

                <label for="l-nombreLocal">Nombre del Local</label>
                <input type="text" id="l-nombreLocal" name="nombreLocal" required>
                <span class="ayuda" id="l-nombre-msg"></span>

                <label for="l-descripcion">Descripción</label>
                <textarea id="l-descripcion" name="descripcion"></textarea>

                <label for="l-telefono">Teléfono</label>
                <input type="text" id="l-telefono" name="telefono" inputmode="numeric" placeholder="8888-8888" maxlength="9" required>

                <label for="l-correo">Correo</label>
                <input type="email" id="l-correo" name="correo" placeholder="ejemplo@gmail.com" required>

                <hr>

                <label for="l-provincia">Provincia</label>
                <select id="l-provincia" name="provincia" required>
                    <option value="">Seleccione...</option>
                    <option>San José</option>
                    <option>Alajuela</option>
                    <option>Cartago</option>
                    <option>Heredia</option>
                    <option>Guanacaste</option>
                    <option>Puntarenas</option>
                    <option>Limón</option>
                </select>

                <label for="l-canton">Cantón</label>
                <input type="text" id="l-canton" name="canton" required>

                <label for="l-distrito">Distrito</label>
                <input type="text" id="l-distrito" name="distrito" required>

                <label for="l-direccion">Dirección exacta</label>
                <input type="text" id="l-direccion" name="direccionExacta" required>

                <label for="l-referencia">Punto de referencia</label>
                <input type="text" id="l-referencia" name="referencia">

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

                <form id="form-editar-local" class="formulario">
                    <input type="hidden" id="e-idLocal">
                    <input type="hidden" id="e-idProveedor">
                    <input type="hidden" id="e-idUbicacion">

                    <label for="e-nombreLocal">Nombre del Local</label>
                    <input type="text" id="e-nombreLocal" required>

                    <label for="e-descripcion">Descripción</label>
                    <textarea id="e-descripcion"></textarea>

                    <label for="e-telefono">Teléfono</label>
                    <input type="text" id="e-telefono" inputmode="numeric" placeholder="8888-8888" maxlength="9" required>

                    <label for="e-correo">Correo</label>
                    <input type="email" id="e-correo" placeholder="ejemplo@gmail.com" required>

                    <hr>

                    <label for="e-provincia">Provincia</label>
                    <select id="e-provincia" required>
                        <option value="">Seleccione...</option>
                        <option>San José</option>
                        <option>Alajuela</option>
                        <option>Cartago</option>
                        <option>Heredia</option>
                        <option>Guanacaste</option>
                        <option>Puntarenas</option>
                        <option>Limón</option>
                    </select>

                    <label for="e-canton">Cantón</label>
                    <input type="text" id="e-canton" required>

                    <label for="e-distrito">Distrito</label>
                    <input type="text" id="e-distrito" required>

                    <label for="e-direccion">Dirección exacta</label>
                    <input type="text" id="e-direccion" required>

                    <label for="e-referencia">Punto de referencia</label>
                    <input type="text" id="e-referencia">

                    <button type="submit">Guardar Cambios</button>
                </form>
            </div>

        </section>

    </main>

    <script src="js/app.js"></script>
</body>
</html>