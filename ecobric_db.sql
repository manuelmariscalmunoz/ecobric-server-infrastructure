-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-03-2026 a las 16:22:12
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ecobric_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Herramientas', 'Herramientas de bricolaje y eléctricas'),
(2, 'Merchandising Sostenible', 'Productos ecológicos y sostenibles'),
(3, 'Pinturas Naturales', 'Pinturas ecológicas y revestimientos naturales'),
(4, 'Maderas y Tableros', 'Maderas certificadas, tableros y soluciones para mobiliario');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_pedido`
--

CREATE TABLE `detalles_pedido` (
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalles_pedido`
--

INSERT INTO `detalles_pedido` (`pedido_id`, `producto_id`, `cantidad`, `precio_unitario`) VALUES
(1, 16, 1, 50.75),
(2, 15, 4, 33.15),
(2, 16, 1, 50.75),
(3, 20, 2, 100.52),
(4, 17, 5, 148.13),
(5, 18, 1, 60.79),
(6, 18, 1, 60.79),
(7, 17, 1, 148.13),
(8, 16, 2, 50.75),
(8, 17, 1, 148.13),
(8, 18, 1, 60.79);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_inventario`
--

CREATE TABLE `movimientos_inventario` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `tipo_movimiento` enum('ENTRADA','SALIDA') NOT NULL,
  `cantidad` int(11) NOT NULL,
  `fecha_movimiento` timestamp NOT NULL DEFAULT current_timestamp(),
  `notas` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `movimientos_inventario`
--

INSERT INTO `movimientos_inventario` (`id`, `producto_id`, `tipo_movimiento`, `cantidad`, `fecha_movimiento`, `notas`) VALUES
(1, 8, 'ENTRADA', 10, '2026-03-04 18:30:36', 'Compra a Pveedor - Costo: 1664.7€'),
(2, 17, 'ENTRADA', 25, '2026-03-05 15:07:56', 'Compra a Pveedor - Costo: 2962.5€');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `monto_total` decimal(10,2) NOT NULL,
  `metodo_pago` varchar(50) DEFAULT 'Tarjeta',
  `direccion_envio` varchar(255) NOT NULL,
  `estado` enum('PENDIENTE','PAGADO','ENVIADO','CANCELADO') DEFAULT 'PENDIENTE',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `usuario_id`, `monto_total`, `metodo_pago`, `direccion_envio`, `estado`, `creado_en`) VALUES
(1, 2, 75.75, 'Tarjeta de Crédito', 'Desconocida', 'PAGADO', '2026-03-04 18:07:46'),
(2, 2, 208.35, 'Tarjeta de Crédito', 'Desconocida', 'PAGADO', '2026-03-04 18:30:00'),
(3, 2, 226.04, 'Tarjeta de Crédito', 'Desconocida', 'PAGADO', '2026-03-04 18:30:08'),
(4, 2, 765.65, 'Tarjeta de Crédito', 'Desconocida', 'PAGADO', '2026-03-04 18:50:45'),
(5, 2, 85.79, 'Tarjeta de Crédito', 'Desconocida', 'PAGADO', '2026-03-04 20:11:08'),
(6, 2, 85.79, 'Tarjeta de Crédito', 'Desconocida', 'PAGADO', '2026-03-04 20:13:10'),
(7, 2, 173.13, 'Tarjeta de Crédito', 'Desconocida', 'PAGADO', '2026-03-04 20:15:48'),
(8, 3, 335.42, 'Tarjeta de Crédito', 'Desconocida', 'PAGADO', '2026-03-04 21:27:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `es_calculable_volumen` tinyint(1) DEFAULT 0,
  `rendimiento_por_m3` decimal(10,2) DEFAULT NULL COMMENT 'Rendimiento o unidades por m3, usado en la calculadora de volumen',
  `url_imagen` varchar(255) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `categoria_id`, `nombre`, `descripcion`, `precio`, `stock`, `es_calculable_volumen`, `rendimiento_por_m3`, `url_imagen`, `creado_en`) VALUES
(1, 1, 'Taladro atornillador 12V REVDD12C-QW', 'Taladro atornillador compacto con batería 12V de alta eficiencia\r\n\r\nCarcasa fabricada con Tritan Renew 50% de material reciclado certificado \r\nFabricado a partir de 8 botellas de plástico de un solo uso (Contiene una cantidad de plástico reciclado equivalente al peso de 8 botellas de plástico de 0,5L (10,3g) de un solo uso)', 57.61, 20, 0, NULL, 'https://www.blackanddecker.es/EMEA/PRODUCT/IMAGES/HIRES/Ecomm_Large-REVDD12C_1.jpg?resize=530x530', '2026-03-04 17:56:50'),
(2, 1, 'Taladro percutor 12V REVHD12C-QW', 'Taladro percutor de alto rendimiento con un par máximo de 40 Nm para perforación en mampostería y hormigón.\r\n\r\nDiseño ergonómico, compacto y ligero que permite trabajar en espacios reducidos y reduce la fatiga durante el uso diario.\r\n\r\nGatillo de velocidad variable para un máximo control. Para cubrir la mayoría de las aplicaciones diarias de bricolaje en el hogar y al aire libre', 49.05, 20, 0, NULL, 'https://www.blackanddecker.es/EMEA/PRODUCT/IMAGES/HIRES/Ecomm_Large-REVHD12C_4.jpg?resize=530x530', '2026-03-04 17:56:50'),
(3, 1, 'Sierra de calar 12V REVJ12C-QW', 'Esta sierra de calar sin cable 12V tiene la potencia y la versatilidad que necesita para cortar perfiles, curvas y esquinas con facilidad y precisión. Diseño compacto y ergonómico, ideal para realizar tareas de corte precisas.\r\n\r\nCarcasa fabricada con Tritan Renew 50% de material reciclado certificado \r\nFabricado a partir de 8 botellas de plástico de un solo uso (Contiene una cantidad de plástico reciclado equivalente al peso de 8 botellas de plástico de 0,5L (10,3g) de un solo uso)', 30.25, 15, 0, NULL, 'https://www.blackanddecker.es/EMEA/PRODUCT/IMAGES/HIRES/Ecomm_Large-REVJ12C_1.jpg?resize=530x530', '2026-03-04 17:56:50'),
(4, 2, 'Radio de emergencia solar RescueWave', 'Prepárate para cualquier situación con RescueWave, la radio de emergencia versátil y sostenible diseñada para mantenerte conectado, iluminado y con energía en momentos clave. Fabricada con plástico ABS reciclado certificado RCS (Recycled Claim Standard), este dispositivo combina innovación, seguridad y compromiso ambiental.', 26.74, 20, 0, NULL, 'https://firstgreen.es/cdn/shop/files/RadiodeEmergenciaconLinternayPowerBankdeCargaSolaryManivelaSostenibledeABSRecicladoconCertificadoRCSPersonalizableRescueWave_1.jpg?v=1758013678', '2026-03-04 17:56:50'),
(5, 3, 'Graphenstone Ecosphere Premium Blanco 15L', 'Pintura de interior de base cal, Graphenstone Ecosphere, en color blanco con acabado mate. \r\n\r\nPintura mineral natural muy lavable, gran blancura y altamente transpirable, ayudando a reducir problemas de humedad. \r\n\r\nAbsorbe CO₂ durante su proceso de curación y gracias a su alto pH inhibe las bacterias y los virus evitando que se adhieran a las paredes, reduciendo además los problemas de alergias por ácaros, moho, etc.', 104.49, 35, 1, 2.50, 'https://media.adeo.com/media/2139771/media.jpg?width=650&height=650&format=jpg&quality=80&fit=bounds', '2026-03-04 17:56:50'),
(6, 3, 'Graphenstone Biosphere Premium 15L', 'Pintura mineral ecológica especialmente indicada para fachadas con acabado mate. Gracias a su fórmula basada en cal 100% artesanal, el producto absorbe CO2 y presenta una excelente transpirabilidad evitando condensaciones.\r\n\r\nCon tecnología Graphenstone para una mayor resistencia y durabilidad. Es ideal para construcción, restauración y repintados. \r\n\r\nDispone de la etiqueta Ecolabel, que certifica que es un producto respetuoso con la salud de las personas y el medioambiente. Contenido: 15 litros.\r\n\r\nPor su fórmula en base cal artesanal con tecnología de grafeno es transpirable, evita las condensaciones, no amarillea y absorbe CO2.', 113.14, 35, 1, 2.50, 'https://media.adeo.com/media/2139979/media.jpeg?width=650&height=650&format=jpg&quality=80&fit=bounds', '2026-03-04 17:56:50'),
(7, 3, 'Graphenstone AmbientPro+ Premium 15L', 'Pintura fotocatalítica, Graphenstone AmbientPro+, en color blanco con acabado mate para uso interior y exterior. Descompone compuestos orgánicos y gases inorgánicos por la incidencia de la luz, ya sea natural o artificial. \r\n\r\nFavorece la eliminación de olores de animales domésticos, tabaco, etc.\r\n\r\nMuy lavable, absorbe CO₂ durante su proceso de curación y gracias a su alto pH inhibe las bacterias y los virus evitando que se adhieran a las paredes, reduciendo además los problemas de alergias por ácaros, moho, etc. ', 122.82, 30, 1, 2.50, 'https://media.adeo.com/media/2125923/media.jpg?width=650&height=650&format=jpg&quality=80&fit=bounds', '2026-03-04 17:56:50'),
(8, 3, 'Graphenstone GrafClean Premium 15L', 'Pintura Blanca Ecológica Graphenstone GrafClean Premium es una pintura mate de interior y exterior base agua que no contiene sustancias tóxicas y es transpirable. Libre de COVs y certificada* para el control de la humedad y Con gran resistencia y durabilidad.\r\n\r\nEs ideal para renovar para interiores y exteriores no expuestos. Se recomienda 2 manos sobre una pared ya blanca. Te recomendamos los utensilios necesarios para su aplicación.', 208.09, 20, 1, 2.50, 'https://pinturas-andalucia.com/1170-large_default/pintura-blanca-ecologica-graphenstone-grafclean-premium.jpg', '2026-03-04 17:56:50'),
(9, 3, 'KEIM Soldalit-Grob 5kg', 'Pintura de sol-silicato con ligero efecto de relleno, para manos de fondo e intermedias en el sistema KEIM Soldalit.\r\n\r\nPara igualar diferencias de textura y para rellenar pequeñas fisuras capilares en la renovación o la nueva aplicación en soportes ligados con resinas o siliconas, así como en soportes minerales.\r\n\r\nKEIM Soldalit-Grob no es adecuada para la mano de acabado. \r\n\r\nKEIM Soldalit-Grob está certificado Cradle to Cradle Certified® Silver y C2C Certified Material Health Certificate™ Gold.', 82.26, 20, 1, 4.00, 'https://www.keim.com/data/_processed_/c/d/csm_PA_EI_Soldalit-Grob_18kg_06_b930eea792.png', '2026-03-04 17:56:50'),
(11, 3, 'KEIM Soldalit-Fixativ', 'Imprimación y diluyente a base de sol-silicato (combinación de sol de sílice y silicato potásico). \r\n\r\nSe emplea como imprimación en soportes o zonas parciales muy absorbentes, o para diluir KEIM Soldalit® y KEIM Soldalit-Grob en la mano de fondo en soportes muy absorbentes.\r\n\r\nKEIM Soldalit-Fixativ está certificado Cradle to Cradle Certified® Silver y C2C Certified Material Health Certificate™ Gold.', 17.31, 20, 1, 5.00, 'https://www.keim.com/data/_processed_/2/5/csm_PA_KA_Soldalit-Fixativ_20l_06_f43b5c056b.png', '2026-03-04 17:56:50'),
(12, 3, 'KEIM Soldalit-Coolit 2.5kg', 'Pintura innovadora de sol-silicato para reducir el recalentamiento solar en colores intensos.\r\n\r\nKEIM Soldalit-Coolit es hidrófuga, estable a la luz, y altamente resistente a la intemperie.\r\n\r\nContenido orgánico < 5% KEIM Soldalit-Coolit está certificado Cradle to Cradle Certified® Silver y C2C Certified Material Health Certificate™ Gold.', 58.36, 25, 1, 4.00, 'https://www.keim.com/data/_processed_/7/a/csm_PA_EI_Soldalit-Coolit_18kg_4c_rgb_9dd47af1d2.png', '2026-03-04 17:56:50'),
(13, 3, 'Oropal Orokril 156 15L', 'Revestimiento mate agua a base de emulsión estireno acrílica para la decoración de interiores y exteriores por su buena impermeabilidad al agua y resistencia a los agentes atmosféricos.\r\n\r\nTranspirable al vapor de agua y CO2. Contiene conservantes que dotan a la pintura de protección fungicida y anti-moho. Con certificado B-s1,d0 de reacción frente al fuego.', 65.95, 40, 1, 2.00, 'https://www.oropal.com/wp-content/uploads/orokril-156.png', '2026-03-04 17:56:50'),
(14, 3, 'Oropal Esmalte Oroxite 230 750ml', 'Esmalte acrílico directo al óxido de elevado poder antioxidante.\r\nLa excelente adherencia e impermeabilidad de este esmalte al agua permiten su aplicación sobre gran cantidad de superficies protegiéndolas frente a la oxidación sin necesidad de imprimación previa. Formulado a base de resinas no amarilleantes.\r\n\r\nSu película seca es capaz de bloquear el óxido y ofrecer una excelente protección que supera los ensayos exigidos por la norma UNE EN ISO 12944-6:2018 para ambientes altamente corrosivos C5 con una duración de hasta 15 años, según informe de validación realizado por CIDETEC (Centro Tecnológico Homologado).\r\n\r\nPermite la aplicación de 2 manos en un solo día garantizando una resistencia a largo plazo', 21.16, 25, 1, 8.00, 'https://www.oropal.com/wp-content/uploads/oroxite-230.png', '2026-03-04 17:56:50'),
(15, 4, 'Finsa SuperPan EZ 2440x1220x19mm', 'El SuperPan® EZ es un tablero compuesto por caras de fibra de madera e interior de partículas apto para utilización general en ambiente seco.\n\nPresenta una superficie lisa y compacta de fibras, adecuada para una gran amplitud de revestimientos decorativos, con todas las ventajas de los tableros SuperPan®.', 33.15, 30, 1, 12.00, 'https://www.finsa.com/documents/d/guest/superpan-_19mm-1-.jpeg', '2026-03-04 17:56:50'),
(16, 4, 'Finsa SuperPan Top 19mm 2440x1220', 'El SuperPan® Top es un tablero compuesto por caras de fibra de madera de hasta 4 mm de espesor e interior de partículas apto para utilización general en ambiente seco. \r\n\r\nPresenta una superficie lisa y compacta de fibras, adecuada para una gran amplitud de revestimientos decorativos, con todas las ventajas de los tableros SuperPan®.\r\n\r\nSu capa de fibras de 4mm de espesor permite mecanizados más profundos en caras.', 50.75, 35, 1, 1.00, 'https://www.finsa.com/documents/d/guest/superpan-star-top_35mm-1-.jpeg', '2026-03-04 17:56:50'),
(17, 4, 'Finsa SuperPan EZ 30mm 2850x2100', 'El SuperPan® EZ es un tablero compuesto por caras de fibra de madera e interior de partículas apto para utilización general en ambiente seco.\n\nPresenta una superficie lisa y compacta de fibras, adecuada para una gran amplitud de revestimientos decorativos, con todas las ventajas de los tableros SuperPan®.', 148.13, 20, 1, 1.00, 'https://www.finsa.com/documents/d/guest/superpan-_19mm-1-.jpeg', '2026-03-04 17:56:50'),
(18, 4, 'Finsa SuperPan A13 Haya blanca 2440x1220x10mm', 'Chapa de haya blanca que presenta un color muy claro y uniforme, tendiendo hacia un beige muy pálido, casi crema, con un ligero matiz rosado o amarillento apenas perceptible. El grano de la madera es predominantemente fino y recto, con una textura muy sutil y consistente. Se aprecian algunas variaciones tonales muy suaves que le dan un aspecto natural. La uniformidad general es notable.', 60.79, 35, 1, 14.00, 'https://www.finsa.com/documents/d/guest/haya-blanca-033-2440x1220_crop.jpg', '2026-03-04 17:56:50'),
(19, 4, 'Finsa SuperPan Haya Blanca 2440x1220x16mm', 'Chapa de haya blanca que presenta un color muy claro y uniforme, tendiendo hacia un beige muy pálido, casi crema, con un ligero matiz rosado o amarillento apenas perceptible. El grano de la madera es predominantemente fino y recto, con una textura muy sutil y consistente. Se aprecian algunas variaciones tonales muy suaves que le dan un aspecto natural. La uniformidad general es notable.', 66.77, 35, 1, 13.00, 'https://www.finsa.com/documents/d/guest/haya-blanca-033-2440x1220_crop.jpg', '2026-03-04 17:56:50'),
(20, 4, 'Finsa SuperPan Haya blanca 2440x1220x30mm', 'Chapa de haya blanca que presenta un color muy claro y uniforme, tendiendo hacia un beige muy pálido, casi crema, con un ligero matiz rosado o amarillento apenas perceptible. El grano de la madera es predominantemente fino y recto, con una textura muy sutil y consistente. Se aprecian algunas variaciones tonales muy suaves que le dan un aspecto natural. La uniformidad general es notable.', 100.52, 25, 1, 8.00, 'https://www.finsa.com/documents/d/guest/haya-blanca-033-2440x1220_crop.jpg', '2026-03-04 17:56:50'),
(21, 3, 'KEIM Soldalit-Grob', 'Pintura de sol-silicato con ligero efecto de relleno, para manos de fondo e intermedias en el sistema KEIM Soldalit.\r\n\r\nPara igualar diferencias de textura y para rellenar pequeñas fisuras capilares en la renovación o la nueva aplicación en soportes ligados con resinas o siliconas, así como en soportes minerales.\r\n\r\nKEIM Soldalit-Grob no es adecuada para la mano de acabado.\r\n\r\nKEIM Soldalit-Grob está certificado Cradle to Cradle Certified® Silver y C2C Certified Material Health Certificate™ Gold.', 18.15, 20, 1, 4.00, 'https://www.keim.com/data/_processed_/c/d/csm_PA_EI_Soldalit-Grob_18kg_06_b930eea792.png', '2026-03-04 19:26:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_proveedor`
--

CREATE TABLE `producto_proveedor` (
  `producto_id` int(11) NOT NULL,
  `proveedor_id` int(11) NOT NULL,
  `precio_suministro` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `producto_proveedor`
--

INSERT INTO `producto_proveedor` (`producto_id`, `proveedor_id`, `precio_suministro`) VALUES
(1, 1, 46.09),
(2, 1, 39.24),
(3, 1, 24.20),
(4, 2, 21.39),
(5, 3, 83.59),
(6, 3, 90.51),
(7, 3, 98.26),
(8, 3, 166.47),
(9, 4, 65.81),
(11, 4, 13.85),
(12, 4, 46.69),
(13, 5, 52.76),
(14, 5, 16.93),
(15, 6, 26.52),
(16, 6, 40.60),
(17, 6, 118.50),
(18, 6, 48.63),
(19, 6, 53.42),
(20, 6, 80.42);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `nombre_empresa` varchar(150) NOT NULL,
  `nombre_contacto` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id`, `nombre_empresa`, `nombre_contacto`, `email`, `telefono`) VALUES
(1, 'Black+Decker', 'Soporte EU', 'soporte@blackanddecker.eu', '+34911223344'),
(2, 'FirstGreen', 'Ventas FirstGreen', 'ventas@firstgreen.es', '+34911225566'),
(3, 'Graphenstone', 'Departamento Ventas Graphenstone', 'ventas@graphenstone.com', '+34911223355'),
(4, 'KEIM', 'Ventas KEIM Iberia', 'info@keim.com', '+34911224455'),
(5, 'Oropal', 'Sales Oropal', 'ventas@oropal.es', '+34911225577'),
(6, 'Finsa', 'Departamento Ventas Finsa', 'ventas@finsa.com', '+34911226688');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`) VALUES
(1, 'Admin'),
(2, 'Cliente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `token_verificacion` varchar(255) DEFAULT NULL,
  `esta_verificado` tinyint(1) DEFAULT 0,
  `direccion_predeterminada` varchar(255) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `rol_id`, `nombre`, `email`, `contrasena`, `token_verificacion`, `esta_verificado`, `direccion_predeterminada`, `creado_en`) VALUES
(2, 1, 'Administrador', 'manuel.mariscalmunoz@riberadeltajo.es', '$2y$10$MPKqdCNMbXDECFDK090b2uA8LeObxkFR7tkLl3j1V.YLGOe2tqCza', NULL, 1, NULL, '2026-03-04 18:04:10'),
(3, 2, 'Guillermo Etayo', 'mateoruinas@gmail.com', '$2y$10$3Aiqj7nxvaTcOYcMVof1BugaluOIPeLUj/gEHiPcKiLJCjzVKqqS.', NULL, 1, NULL, '2026-03-04 21:26:38'),
(4, 1, 'Admin', 'admin@talavera.es', '$2y$10$V2sgFQAcqAMn7mnSgm/x8.CjSp0nHB5oRCB2bDXkoLrYqPLPGzE9O', NULL, 1, NULL, '2026-03-05 15:16:07');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `detalles_pedido`
--
ALTER TABLE `detalles_pedido`
  ADD PRIMARY KEY (`pedido_id`,`producto_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `producto_proveedor`
--
ALTER TABLE `producto_proveedor`
  ADD PRIMARY KEY (`producto_id`,`proveedor_id`),
  ADD KEY `proveedor_id` (`proveedor_id`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `rol_id` (`rol_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalles_pedido`
--
ALTER TABLE `detalles_pedido`
  ADD CONSTRAINT `detalles_pedido_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalles_pedido_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD CONSTRAINT `movimientos_inventario_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);

--
-- Filtros para la tabla `producto_proveedor`
--
ALTER TABLE `producto_proveedor`
  ADD CONSTRAINT `producto_proveedor_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `producto_proveedor_ibfk_2` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
