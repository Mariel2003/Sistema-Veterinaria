-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-07-2025 a las 01:27:40
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `veterinaria_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `id_carrito` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `id_producto` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT 1,
  `fecha_agregado` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carrito`
--

INSERT INTO `carrito` (`id_carrito`, `id_usuario`, `id_producto`, `cantidad`, `fecha_agregado`) VALUES
(3, 1, 7, 1, '2025-07-25 18:25:52'),
(4, 1, 13, 1, '2025-07-25 18:25:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consultas`
--

CREATE TABLE `consultas` (
  `id_consulta` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `motivo` text DEFAULT NULL,
  `foto` varchar(255) NOT NULL,
  `estado` enum('pendiente','en progreso','finalizada','cancelada') DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `consultas`
--

INSERT INTO `consultas` (`id_consulta`, `id_usuario`, `fecha`, `motivo`, `foto`, `estado`) VALUES
(4, 1, '2025-07-25 16:11:56', 'Mascota: Maria (gato, 2)\nFecha preferida: 2025-07-27 16:00\nMotivo: Le duele su piecito', '6883e50ce3d4b_1753474316.png', 'pendiente'),
(5, 1, '2025-07-25 16:13:48', 'Mascota: Barbie (gato, 1)\nFecha preferida: 2025-07-26 11:00\nMotivo: Es muy bonita', '6883e57cd6547_1753474428.jpg', 'cancelada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_ventas`
--

CREATE TABLE `detalles_ventas` (
  `id_detalle` int(11) NOT NULL,
  `id_venta` int(11) DEFAULT NULL,
  `id_producto` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `precio_unitario` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedidos`
--

CREATE TABLE `detalle_pedidos` (
  `id_detalle` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_pedidos`
--

INSERT INTO `detalle_pedidos` (`id_detalle`, `id_pedido`, `id_producto`, `cantidad`, `precio_unitario`) VALUES
(1, 1, 4, 8, 10.00),
(2, 2, 2, 3, 12.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedido` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `estado` enum('pendiente','procesando','enviado','entregado','cancelado') DEFAULT 'pendiente',
  `metodo_pago` varchar(50) NOT NULL,
  `nombre_cliente` varchar(100) NOT NULL,
  `apellido_cliente` varchar(100) NOT NULL,
  `email_cliente` varchar(150) NOT NULL,
  `telefono_cliente` varchar(20) NOT NULL,
  `direccion_envio` text NOT NULL,
  `ciudad_envio` varchar(100) NOT NULL,
  `codigo_postal_envio` varchar(10) NOT NULL,
  `notas` text DEFAULT NULL,
  `fecha_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id_pedido`, `id_usuario`, `total`, `estado`, `metodo_pago`, `nombre_cliente`, `apellido_cliente`, `email_cliente`, `telefono_cliente`, `direccion_envio`, `ciudad_envio`, `codigo_postal_envio`, `notas`, `fecha_pedido`, `fecha_actualizacion`) VALUES
(1, 1, 80.00, 'enviado', 'efectivo', 'liz', 'laime', 'liz@gmail.com', '72960477', 'torrecillas', 'Tarija', '000', 'entrega', '2025-07-20 16:03:17', '2025-07-20 16:27:57'),
(2, 1, 36.00, 'pendiente', 'tarjeta_debito', 'Juan', 'perez', 'juan@gmail.com', '54987', 'torrecillas', 'Tarija', '000', 'entrega', '2025-07-20 17:28:50', '2025-07-25 22:34:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `categoria` varchar(50) DEFAULT NULL,
  `imagen_url` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `nombre`, `descripcion`, `precio`, `stock`, `categoria`, `imagen_url`, `activo`) VALUES
(2, 'correa', 'Suelen tener una longitud de 1 o 2 metros y el material más aconsejable es el nylon, ya que no pesa, es bastante resistente, fácil de lavar y resulta suave en la piel del animal', 12.00, 119, 'Accesorios', 'https://i.pinimg.com/1200x/d0/d3/a2/d0d3a29de6a7919969a6b795938c3544.jpg', 1),
(3, 'pelota', 'juguete', 13.00, 100, 'juguete', 'https://i.pinimg.com/1200x/af/0e/7b/af0e7b8fc433670026d7384d97ffbaae.jpg', 1),
(4, 'juguete de hueso', 'sonido', 10.00, 25, 'juguete', 'https://i.pinimg.com/1200x/ad/fe/c9/adfec91377c353e459e2a77993b815a9.jpg', 1),
(6, 'Collar', 'Collar de nylon resistente con cierre de seguridad y hebilla metálica. Disponible en varios colores y tallas.', 50.00, 150, 'Accesorios', 'https://i.pinimg.com/1200x/7e/ad/71/7ead71331a300932fe11bb0df15410f4.jpg', 1),
(7, 'Cama suave tipo cueva', 'Cama acolchada con forma de cueva, ideal para mascotas pequeñas que buscan calidez y privacidad.', 190.00, 56, 'Accesorios', 'https://i.pinimg.com/736x/b0/d2/e3/b0d2e39fbc3f60f77ddc0f90935ab148.jpg', 1),
(8, 'Transportadora plegable para Gato', 'Caja transportadora ventilada y resistente, fácil de limpiar y con asa para llevar mascotas pequeñas.', 295.00, 37, 'Accesorios', 'https://i.pinimg.com/1200x/c8/2b/5f/c82b5fc0a650a6b33f9bc7c626d708d3.jpg', 1),
(9, 'Transportadora plegable de perros', 'Caja transportadora ventilada y resistente, fácil de limpiar y con asa para llevar mascotas pequeñas.', 300.00, 98, 'Accesorios', 'https://i.pinimg.com/1200x/89/96/e4/8996e422fb691c82bfb032b17cf6b2ce.jpg', 1),
(10, 'Bebedero automático para Hamster', 'Dispensador de agua que se rellena automáticamente para mantener a tu mascota hidratada, de 80 ml', 167.00, 45, 'Accesorios', 'https://i.pinimg.com/1200x/5b/ca/18/5bca186848622d0c83e69b30616f2449.jpg', 1),
(11, 'Jaula para Aves', 'Jaula con gran espacio y comoda para tu ave', 349.00, 34, 'Accesorios', 'https://i.pinimg.com/1200x/aa/76/64/aa76640134c71fc2ee27d3d71a92a1cc.jpg', 1),
(12, 'Pelota de goma con sonido para gato', 'Pelota con textura interno para estimular el juego y la actividad física del Gato', 250.00, 56, 'Juguete', 'https://i.pinimg.com/1200x/15/cd/1f/15cd1fde59a18b3b3302ad4a0804cd28.jpg', 1),
(13, 'Caña con plumas para gato', 'Juguete interactivo con plumas y campanita para fomentar el instinto de caza del gato.', 100.00, 34, 'Juguete', 'https://i.pinimg.com/1200x/0b/15/b4/0b15b429b648a0193186c8df1db1a3f4.jpg', 1),
(14, 'Hueso mordedor de caucho', 'Resistente al desgaste, ideal para aliviar el estrés y mantener los dientes limpios.', 99.00, 23, 'Juguete', 'https://i.pinimg.com/736x/40/39/62/403962b60cb99a20fc1f3f9cf37e9d00.jpg', 1),
(15, 'Pelota dispensadora de premios', 'Juguete que libera pelotas para rodar, ideal para ejercitar cuerpo y mente.', 50.00, 34, 'Juguete', 'https://i.pinimg.com/1200x/d9/56/6e/d9566e978226385f7478df0ab95e4843.jpg', 1),
(16, 'Croquetas para perro adulto', 'Alimento balanceado con proteína de pollo, omega 3 y 6, y sin colorantes artificiales.', 296.00, 45, 'Alimento', 'https://i.pinimg.com/736x/04/b3/f8/04b3f8b5965e95cb5fb448eef85b8dde.jpg', 1),
(17, 'Croquetas para gato', 'Fórmula especial para controlar el peso, con proteínas de pescado y prebióticos.', 255.00, 23, 'Alimento', 'https://i.pinimg.com/736x/d3/7b/95/d37b95720172e4682e8a1221848121ab.jpg', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `rol` enum('cliente','admin') DEFAULT 'cliente',
  `activo` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `email`, `contraseña`, `telefono`, `direccion`, `rol`, `activo`, `fecha_registro`) VALUES
(1, 'liz', 'liz@gmail.com', '72960477', '72960477', 'torrecillas', 'cliente', 0, '2025-07-19 23:55:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id_venta` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `total` decimal(10,2) DEFAULT NULL,
  `estado` enum('pendiente','pagado','cancelado') DEFAULT 'pendiente',
  `metodo_pago` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id_carrito`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `consultas`
--
ALTER TABLE `consultas`
  ADD PRIMARY KEY (`id_consulta`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `detalles_ventas`
--
ALTER TABLE `detalles_ventas`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_venta` (`id_venta`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `idx_detalle_pedido` (`id_pedido`),
  ADD KEY `idx_detalle_producto` (`id_producto`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `idx_pedidos_usuario` (`id_usuario`),
  ADD KEY `idx_pedidos_estado` (`estado`),
  ADD KEY `idx_pedidos_fecha` (`fecha_pedido`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id_venta`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id_carrito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `consultas`
--
ALTER TABLE `consultas`
  MODIFY `id_consulta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `detalles_ventas`
--
ALTER TABLE `detalles_ventas`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id_venta` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `carrito_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

--
-- Filtros para la tabla `consultas`
--
ALTER TABLE `consultas`
  ADD CONSTRAINT `consultas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `detalles_ventas`
--
ALTER TABLE `detalles_ventas`
  ADD CONSTRAINT `detalles_ventas_ibfk_1` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id_venta`),
  ADD CONSTRAINT `detalles_ventas_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

--
-- Filtros para la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  ADD CONSTRAINT `detalle_pedidos_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_pedidos_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
