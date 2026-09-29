<?php
require_once __DIR__ . '/../../Configuracion/BaseDatos.php';
echo date_default_timezone_get() . ' | ' . (new DateTime())->format('Y-m-d H:i:s');