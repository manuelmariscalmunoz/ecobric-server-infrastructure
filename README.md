<div align="center">
  <img src="src/img/logo.png" alt="Ecobric Logo" width="300" />

  <h1>🌿 Ecobric - Infraestructura de Servidores y Plataforma Web ERP</h1>

  <p>
    <strong>Plataforma integral eCommerce B2B/B2C con ERP automatizado, desplegada sobre una infraestructura de alta disponibilidad.</strong>
  </p>

  <!-- Badges -->
  <p>
    <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP" />
    <img src="https://img.shields.io/badge/Python-3776AB?style=for-the-badge&logo=python&logoColor=white" alt="Python" />
    <img src="https://img.shields.io/badge/MariaDB-003545?style=for-the-badge&logo=mariadb&logoColor=white" alt="MariaDB" />
    <img src="https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker" />
    <img src="https://img.shields.io/badge/Ubuntu-E95420?style=for-the-badge&logo=ubuntu&logoColor=white" alt="Ubuntu" />
    <img src="https://img.shields.io/badge/NGINX-009639?style=for-the-badge&logo=nginx&logoColor=white" alt="NGINX" />
    <img src="https://img.shields.io/badge/Keepalived-000000?style=for-the-badge&logo=linux&logoColor=white" alt="Keepalived" />
    <img src="https://img.shields.io/badge/Gemini_AI-8E75B2?style=for-the-badge&logo=google-gemini&logoColor=white" alt="Gemini AI" />
  </p>
</div>

---

## 📖 Resumen del Proyecto

**Ecobric** es una solución tecnológica completa diseñada para la venta de productos y materiales de construcción ecológicos y sostenibles. Este proyecto abarca desde el diseño y configuración de una **infraestructura de red segura de alta disponibilidad (HA)** hasta el desarrollo Full-Stack de un **eCommerce con un panel ERP administrativo**.

El núcleo del sistema físico se sitúa en unas oficinas protegidas con sistemas SAI y biometría, mientras que el entorno lógico está segmentado por VLANs y firewalls estrictos para aislar los servidores críticos de los equipos de almacén e invitados.

---

## 🚀 Despliegue e Instalación

El repositorio está diseñado para poder montarse tanto a nivel de código de desarrollo como a nivel de arquitectura física.

### Opción A: Despliegue Ligero (Solo Web + Base de Datos)
Ideal si quieres desplegar únicamente el código de la tienda web y el panel de administración ERP sin emular los servidores Ubuntu o Windows.

1.  **Levantar el Servidor Web**: Instala un entorno LAMP/WAMP (como XAMPP) o utiliza un contenedor de Docker para PHP/MariaDB.
2.  **Base de Datos**: Crea una base de datos vacía llamada `ecobric` en tu gestor (ej. phpMyAdmin) e importa el archivo `ecobric_db.sql` que se encuentra en la raíz de este repositorio.
3.  **Código Fuente**: Copia el contenido de la carpeta `src/` al directorio público de tu servidor web (ej. `htdocs` en XAMPP o `/var/www/html` en Apache).
4.  **Conexión**: Edita el archivo `src/config/db.php` para ajustar las credenciales (`DB_USER`, `DB_PASS`) según la configuración de tu entorno local.
5.  *(Opcional)* **Inteligencia Artificial**: Si quieres usar a **EcoBot** (el chatbot de la tienda), necesitas registrarte en Google AI Studio, conseguir una API Key gratuita y pegarla en la variable `$apiKey` dentro del archivo `src/api/chatbot.php`.

### Opción B: Despliegue Completo (Infraestructura de Red Completa - OVA)
El proyecto cuenta con un entorno empresarial completo ya preconfigurado y empaquetado en máquinas virtuales para VirtualBox o VMware.

👉 **[Descargar Archivos OVA desde Google Drive](https://drive.google.com/drive/folders/1DI0l7FpfX5Dk2sb77s3mNrPqgFvYtv3O?usp=sharing)** *(Nota: Requiere ~16GB de RAM libres).*

⚠️ **Orden de Arranque Crítico:**
1.  ▶️ **Ecobric_DC1** (Windows Server 2022 - Esperar a que levante el controlador de Active Directory y el DNS local).
2.  ▶️ **Ecobric_DC2** (Servidor de respaldo).
3.  ▶️ **Ubuntu Server Master y Esclavo** (Linux Server sin interfaz gráfica. Esperar unos minutos a que arranquen los contenedores de Docker).
4.  ▶️ **Cliente_Windows10** (Para acceder al panel de administración y operar la plataforma como empleado).

---

## 🏗️ Infraestructura y Tolerancia a Fallos (HA)

La red ha sido diseñada para garantizar una resiliencia total y un **Uptime del 99.99%** mediante un clúster de servidores redundantes frente a desastres físicos.

![Esquema de Red](src/img/esquema_red.png)

### Arquitectura Maestro-Esclavo y Failover
Disponemos de dos servidores **Ubuntu Server** (Master y Esclavo) aislados del resto de la red corporativa. Toda la plataforma web (NGINX Proxy Manager, MariaDB, PHP-FPM, Prometheus, Grafana) está "dockerizada". Para resolver el problema de la tolerancia a fallos, se implementó el siguiente flujo:

1.  **Aislamiento de Volúmenes**: Los contenedores de Docker no almacenan la persistencia en la misma ruta del sistema operativo. Los volúmenes críticos (webs, bases de datos y configuraciones) residen en un disco virtual aparte (`/mnt/data`).
2.  **Sincronización (`rsync` + Cron)**: Un script automatizado (`sincronizar.sh`) corre en un cronjob del servidor Maestro cada **30 segundos**. Este comando hace un volcado seguro de la base de datos MariaDB y envía los deltas del disco persistente hacia el servidor Esclavo (Standby Frío) mediante SSH usando `rsync`, excluyendo cachés temporales para evitar fallos de permisos.
3.  **Virtual IP (`keepalived`)**: Ambos nodos tienen el demonio `keepalived` instalado. Comparten una **IP Virtual**. Si el servidor Maestro se apaga, pierde conectividad o colapsa el servicio, `keepalived` detecta la anomalía y transfiere la IP Virtual al servidor Esclavo en cuestión de segundos. Como el Esclavo ya tiene los últimos datos (sincronizados hace máximo 30s), la página web sigue funcionando de manera transparente para el usuario final.

---

## 🧠 Módulos Clave del Sistema (Deep-Dive Técnico)

La web no es un gestor de contenidos prefabricado (CMS); ha sido desarrollada completamente a medida. A continuación se detallan las 4 funciones más interesantes a nivel técnico:

### 1. 🤖 Asistente de IA "EcoBot" (`src/api/chatbot.php`)
Se integró la API de Google Gemini (v2.5) para tener un experto en materiales en la tienda.
*   **Contextualización Dinámica (RAG):** El script PHP no solo envía la pregunta del usuario. Primero, se conecta a MariaDB mediante PDO, hace una query selectiva del catálogo de productos (`SELECT nombre, precio, stock, es_calculable_volumen`) y concatena toda esa información formando un inmenso System Prompt. De esta manera, Gemini responde a las dudas de los usuarios sabiendo si hay stock de un ladrillo, a qué precio está, o cuáles son los costes fijos de envío, **sin inventarse productos** y recomendando artículos del catálogo real de Ecobric.

### 2. 🧪 Seeding de Pruebas de Estrés (`dev_tools/generar_datos.py`)
Para probar los cálculos de contabilidad del panel ERP, necesitábamos meses de histórico de compras y reabastecimientos. Se creó un script en **Python** para generar big data simulado.
*   **Lógica Relacional Real:** En lugar de inyectar filas sueltas, el script respeta las claves foráneas (Foreign Keys). Genera cientos de usuarios y les crea pedidos con carritos aleatorios. Para cada carrito escoge entre 1 y 4 productos, calcula el subtotal multiplicando por su precio exacto de base de datos, inserta en `detalles_pedido` y guarda el total en `pedidos`. También simula las inversiones de la empresa generando movimientos de `ENTRADA` en el inventario interactuando con la tabla pivot `producto_proveedor`.

### 3. 📊 Exportación Financiera Dinámica XLS (`src/paginas/api_export_csv.php`)
El panel de administración incluye una pestaña para descargar los balances contables de la empresa según el mes y el año.
*   **Cálculo Financiero Complejo:** El código PHP hace dos grandes sumatorios interconectados. Primero, suma los ingresos filtrando por pedidos en estado `PAGADO` en el mes indicado. Después, calcula los gastos multiplicando la cantidad de productos reabastecidos (`movimientos_inventario`) por el `precio_suministro` negociado con el proveedor específico de ese artículo. Tras restar ambos valores para hallar el balance neto, **fuerza la descarga** como un archivo de Microsoft Excel.
*   **Ingeniería de Cabeceras HTTP:** A diferencia de una exportación CSV plana, el script altera los *Headers* HTTP (`Content-Type: application/vnd.ms-excel`) e inyecta una estructura HTML y estilos CSS con clases para colores y formato de moneda europeo. Al abrirlo, el PC del administrador asume que es una hoja de cálculo formateada lista para imprimir.

### 4. 📐 Calculadora Volumétrica (`src/paginas/calculadora.php`)
Un cliente que reforma una casa sabe las dimensiones de su pared, pero no cuántos "sacos" de mortero o ladrillos sueltos necesita comprar.
*   **Matemática de Rendimiento:** Esta herramienta interactiva recoge en el frontend (HTML) el Alto, Ancho y Profundidad introducidos por el cliente y genera el volumen (m³). El backend (PHP) alimenta un menú desplegable ocultando el `rendimiento_por_m3` y el precio de cada material.
*   **JavaScript Reactivo y AJAX:** Cuando el usuario pulsa calcular, JS multiplica el volumen por el rendimiento y usa `Math.ceil()` para redondear al alza los ladrillos necesarios. Una vez mostrado el presupuesto, si el usuario pulsa añadir al carrito, la función JavaScript lanza un `fetch()` en segundo plano (AJAX) hacia `add_to_cart.php`, metiendo todas esas unidades al carrito e incrementando el contador rojo de la insignia superior (badge) sin necesidad de recargar la página web en ningún momento.

---

## 🗄️ Arquitectura de la Base de Datos

Todo el sistema eCommerce, logística de envíos y autenticación de usuarios está respaldado por una base de datos relacional robusta en MariaDB compuesta de 9 tablas principales normalizadas:

![Esquema de la Base de Datos](src/img/esquema_bd.png)

---

## 📚 Documentación Técnica Completa
Para una inmersión profunda en el código fuente, librerías, dependencias de contenedores y guías administrativas paso a paso, visita la Wiki del repositorio:

👉 **[Ver Documentación Técnica en DeepWiki](https://deepwiki.com/manuelmariscalmunoz/ecobric-server-infrastructure)**

---
*Desarrollado por [Manuel Mariscal Muñoz] - I.E.S. Ribera del Tajo*