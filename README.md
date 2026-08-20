# JJ Tienda Deportiva

Sistema de gestión de inventario, ventas y clientes desarrollado como proyecto de grado — Ingeniería de Sistemas, UNAD.

## 🔗 Enlaces

- **Repositorio:** https://github.com/JhonAlex2003/jj_tienda_deportiva
- **Sistema en línea:** http://jjtiendadeportiva.infinityfreeapp.com/views/login.php
  - Usuario: `admin`
  - Contraseña: `admin123`

## 📋 Descripción del proyecto

Este proyecto es un prototipo funcional de sistema web que permite a JJ Tienda Deportiva gestionar su inventario, ventas y clientes de forma digital, sustituyendo el control manual que se llevaba anteriormente.

## ✨ Funcionalidades

- **Productos:** registro, edición y eliminación con control de stock y stock mínimo.
- **Categorías:** gestión de categorías de productos.
- **Clientes:** registro y consulta de clientes.
- **Ventas:** punto de venta con autocompletado de productos, cálculo automático de totales y estado de pago (pagado, abono, pendiente).
- **Abonos:** control de pagos parciales y saldos pendientes por cliente.
- **Historial de inventario:** trazabilidad de entradas, salidas y ajustes de stock.
- **Historial de ventas:** consulta de ventas con filtros por cliente y fecha.
- **Comprobante de venta:** generación de comprobante imprimible/descargable en PDF.
- **Reportes:** filtrado de ventas por rango de fechas con exportación a Excel.
- **Dashboard:** indicadores clave, alertas de stock bajo y ranking de productos más vendidos.
- **Perfil de usuario:** edición de nombre, foto de perfil y contraseña.
- **Recuperación de contraseña:** mediante pregunta de seguridad.
- **Respaldo de base de datos:** exportación descargable en formato SQL.
- **Manual de ayuda:** guía de uso integrada en el sistema.

## 🛠️ Tecnologías utilizadas

- **PHP** — lógica del backend, patrón MVC.
- **MySQL / MariaDB** — base de datos relacional.
- **HTML / CSS / Bootstrap 5** — interfaz de usuario.
- **JavaScript / Chart.js** — gráficas dinámicas e interactividad.
- **Git / GitHub** — control de versiones.

## 📂 Estructura del proyecto

```
├── assets/          # CSS, JS y recursos estáticos
├── config/          # Configuración de conexión a la base de datos
├── controllers/     # Controladores PHP (patrón MVC)
├── includes/        # Layout, header y footer reutilizables
├── models/          # Clases de acceso a datos
├── views/           # Vistas del sistema, organizadas por módulo
│   ├── productos/
│   ├── categorias/
│   ├── clientes/
│   ├── ventas/
│   ├── inventario/
│   ├── reportes/
│   ├── perfil/
│   ├── backup/
│   └── ayuda/
├── jj_tienda_db.sql # Script de creación e importación de la base de datos
└── README.md
```

## 🚀 Instalación local

### 1. Clonar el repositorio

```bash
git clone https://github.com/JhonAlex2003/jj_tienda_deportiva.git
```

### 2. Configurar el servidor local

- Copia el proyecto dentro de la carpeta `htdocs` de XAMPP.
- Configura la base de datos en `config/db.php`:
  - Host: `127.0.0.1`
  - Puerto: `3307` (o el que use tu instalación de XAMPP)
  - Base de datos: `jj_tienda_db`
  - Usuario: `root`
  - Contraseña: (vacía por defecto)

### 3. Importar la base de datos

Desde phpMyAdmin, crea la base de datos `jj_tienda_db` e importa el archivo `jj_tienda_db.sql`.

### 4. Ejecutar el sistema

Abre en el navegador:

```
http://localhost/jj_tienda_deportiva/views/login.php
```

## 👤 Uso

- Inicia sesión con usuario y contraseña.
- Desde el dashboard puedes navegar a todos los módulos: productos, categorías, clientes, ventas, abonos, inventario, reportes, perfil y respaldo.
- Consulta el **Manual de ayuda** dentro del sistema (menú de usuario) para una guía paso a paso de cada módulo.

## 📝 Notas

- Este es un prototipo funcional desarrollado con fines académicos, ejecutado en entorno local (XAMPP) y desplegado en un hosting gratuito para fines de demostración.
- No incluye pasarelas de pago, facturación electrónica oficial ni sincronización en la nube en tiempo real.

## 👨‍💻 Autor

**Jhon Alexander Barona Cuellar**
Proyecto de Grado — Ingeniería de Sistemas — UNAD
