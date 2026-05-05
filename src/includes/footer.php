</main> <!-- Cierra .main-content abierto en header.php -->

<footer class="main-footer">
    <div class="container footer-grid">
        <div class="footer-col brand-col">
            <h3 style="display: flex; align-items: center; gap: 10px; margin-bottom: 1rem;">
                <img src="<?php echo $base_path ?? ''; ?>img/logo.png" alt="Ecobric" style="height: 30px;">
                Ecobric
            </h3>
            <p>Tu aliado en construcción sostenible. Materiales ecológicos de primer nivel para reducir el impacto
                ambiental sin sacrificar calidad.</p>
            <div class="social-links">
                <a href="#"><i class="fa-brands fa-instagram"></i></a>
                <a href="#"><i class="fa-brands fa-facebook"></i></a>
                <a href="#"><i class="fa-brands fa-twitter"></i></a>
            </div>
        </div>
        <div class="footer-col links-col">
            <h4>Enlaces Rápidos</h4>
            <ul>
                <li><a href="<?php echo $base_path ?? ''; ?>paginas/catalogo.php">Catálogo de Productos</a></li>
                <li><a href="<?php echo $base_path ?? ''; ?>paginas/calculadora.php">Calculadora de Volúmenes</a></li>
                <li><a href="<?php echo $base_path ?? ''; ?>paginas/nosotros.php">Nuestra Filosofía</a></li>
            </ul>
        </div>
        <div class="footer-col contact-col">
            <h4>Contacto</h4>
            <ul class="contact-info">
                <li><i class="fa-solid fa-map-marker-alt"></i> Tres Olivos - La Piedad,Toledo, 45600, Talavera de la
                    Reina</li>
                <li><i class="fa-solid fa-phone"></i> +34 666 666 666</li>
                <li><i class="fa-solid fa-envelope"></i> ecobricsoporte@gmail.com</li>
            </ul>
        </div>
        <div class="footer-col newsletter-col">
            <h4>Boletín Verde</h4>
            <p>Suscríbete y recibe nuestros tips de bioconstrucción.</p>
            <form action="#" class="newsletter-form">
                <input type="email" placeholder="Tu correo electrónico" required>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i></button>
            </form>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <p>&copy;
                <?php echo date('Y'); ?> Ecobric. Proyecto ASIR. Todos los derechos reservados.
            </p>
        </div>
    </div>
</footer>
<script src="<?php echo $base_path ?? ''; ?>js/main.js"></script>
<script>
    // Configuración base_path en JS
    const basePath = '<?php echo $base_path ?? ''; ?>';

    // Funcionalidad AJAX para añadir al carrito
    document.addEventListener('DOMContentLoaded', () => {
        const btns = document.querySelectorAll('.add-to-cart-btn');
        btns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const productId = btn.getAttribute('data-id');
                let cantidadInput = document.getElementById('cantidad');
                let qty = cantidadInput ? cantidadInput.value : 1;

                fetch(basePath + 'paginas/add_to_cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${productId}&qty=${qty}`
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            mostrarToast('Producto añadido al carrito con éxito', 'success');
                            // Incrementar contador en el frontend
                            let badge = document.querySelector('.cart-count');
                            if (badge) {
                                badge.textContent = data.total_items;
                            } else {
                                // Crear el badge si no existía (estaba en 0)
                                const cartIcon = document.querySelector('.cart-icon');
                                if (cartIcon) {
                                    cartIcon.innerHTML += `<span class="cart-count">${data.total_items}</span>`;
                                }
                            }
                        } else {
                            mostrarToast(data.message || 'Error al añadir al carrito', 'error');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        mostrarToast('Error de red al añadir al carrito', 'error');
                    });
            });
        });
    });

    // Función para mostrar notificaciones tipo Toast
    function mostrarToast(mensaje, tipo = 'success') {
        const toast = document.createElement('div');
        toast.className = 'toast-notification ' + tipo;
        toast.textContent = mensaje;
        toast.style.position = 'fixed';
        toast.style.bottom = '20px';
        toast.style.right = '20px';
        toast.style.padding = '15px 25px';
        toast.style.color = 'white';
        toast.style.backgroundColor = tipo === 'success' ? '#2e7d32' : '#d32f2f';
        toast.style.borderRadius = '8px';
        toast.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        toast.style.zIndex = '9999';
        toast.style.transition = 'opacity 0.3s, transform 0.3s';
        toast.style.transform = 'translateY(20px)';
        toast.style.opacity = '0';
        toast.style.fontWeight = 'bold';

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.transform = 'translateY(0)';
            toast.style.opacity = '1';
        }, 10);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(20px)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    // Funcionalidad Chatbot
    function toggleChat(forceOpen = null) {
        const chatWindow = document.getElementById('chat-window');
        if (forceOpen === true) {
            chatWindow.style.display = 'flex';
        } else if (forceOpen === false) {
            chatWindow.style.display = 'none';
        } else {
            chatWindow.style.display = chatWindow.style.display === 'flex' ? 'none' : 'flex';
        }
    }

    async function sendChatMessage() {
        const input = document.getElementById('chat-input-text');
        const text = input.value.trim();
        if (!text) return;

        // Render User Message
        const chatBody = document.getElementById('chat-body');
        chatBody.innerHTML += `<div class="chat-bubble chat-user">${text}</div>`;
        input.value = '';
        chatBody.scrollTop = chatBody.scrollHeight;

        // Loading Indicator
        const loaderId = 'loader-' + Date.now();
        chatBody.innerHTML += `<div id="${loaderId}" class="chat-bubble chat-bot"><i class="fa-solid fa-spinner fa-spin"></i> Escribiendo...</div>`;
        chatBody.scrollTop = chatBody.scrollHeight;

        try {
            const response = await fetch(basePath + 'api/chatbot.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: text })
            });

            const data = await response.json();

            // Remove Loading
            document.getElementById(loaderId).remove();

            const replyWrapperId = 'reply-' + Date.now();
            chatBody.innerHTML += `<div id="${replyWrapperId}" class="chat-bubble chat-bot"></div>`;
            chatBody.scrollTop = chatBody.scrollHeight;

            const replyDiv = document.getElementById(replyWrapperId);

            if (data.success) {
                // Typewriter effect (Safe for Unicode/Emojis and XSS)
                let i = 0;
                const textToType = data.reply;
                const chars = [...textToType]; // Array of chars (handles emojis correctly)
                const speed = 15; // ms per char

                function typeWriter() {
                    if (i < chars.length) {
                        if (chars[i] === '\n') {
                            replyDiv.appendChild(document.createElement('br'));
                        } else {
                            replyDiv.appendChild(document.createTextNode(chars[i]));
                        }
                        i++;
                        chatBody.scrollTop = chatBody.scrollHeight;
                        setTimeout(typeWriter, speed);
                    }
                }
                typeWriter();
            } else {
                replyDiv.innerHTML = `<em>Error: ${data.message}</em>`;
            }
        } catch (err) {
            document.getElementById(loaderId).remove();
            // ... rest (catch error)
            chatBody.innerHTML += `<div class="chat-bubble chat-bot" style="color: red;">Error de red al conectar con la IA.</div>`;
        }
    }
</script>

<!-- STYLES CHATBOT -->
<style>
    .chatbot-widget {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 999999;
        font-family: 'Inter', sans-serif;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }

    @keyframes pulse-bot {
        0% {
            box-shadow: 0 0 0 0 rgba(46, 125, 50, 0.7);
            transform: scale(1);
        }

        70% {
            box-shadow: 0 0 0 15px rgba(46, 125, 50, 0);
            transform: scale(1.05);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(46, 125, 50, 0);
            transform: scale(1);
        }
    }

    .chatbot-toggle {
        width: 65px;
        height: 65px;
        background: linear-gradient(135deg, #2e7d32, #60ad5e);
        color: white;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 2rem;
        cursor: pointer;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
        animation: pulse-bot 2.5s infinite;
        border: 3px solid white;
        margin-top: 15px;
    }

    .chatbot-toggle:hover {
        transform: scale(1.1) rotate(10deg);
        animation: none;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
    }

    .chatbot-window {
        display: none;
        width: 350px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        overflow: hidden;
        flex-direction: column;
        border: 1px solid #e0e0e0;
        transform-origin: bottom right;
        animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        margin-bottom: 20px;
    }

    @keyframes popIn {
        0% {
            opacity: 0;
            transform: scale(0.5);
        }

        100% {
            opacity: 1;
            transform: scale(1);
        }
    }

    .chatbot-header {
        background: linear-gradient(135deg, #2e7d32, #1b5e20);
        color: white;
        padding: 1.2rem;
        font-weight: bold;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .chatbot-header span.close-bot {
        cursor: pointer;
        font-size: 1.5rem;
        transition: transform 0.2s;
    }

    .chatbot-header span.close-bot:hover {
        transform: scale(1.2);
    }

    .chatbot-messages {
        height: 350px;
        padding: 1.2rem;
        overflow-y: auto;
        background: #f4f7f6;
        display: flex;
        flex-direction: column;
        gap: 12px;
        scroll-behavior: smooth;
    }

    .chatbot-messages::-webkit-scrollbar {
        width: 6px;
    }

    .chatbot-messages::-webkit-scrollbar-thumb {
        background-color: #ccc;
        border-radius: 10px;
    }

    .chat-bubble {
        max-width: 85%;
        padding: 12px 16px;
        border-radius: 18px;
        font-size: 0.95rem;
        line-height: 1.5;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .chat-bot {
        background: white;
        color: #333;
        align-self: flex-start;
        border-bottom-left-radius: 4px;
        border: 1px solid #eef;
    }

    .chat-user {
        background: #2e7d32;
        color: white;
        align-self: flex-end;
        border-bottom-right-radius: 4px;
    }

    .chatbot-input {
        display: flex;
        border-top: 1px solid #eee;
        background: white;
        padding: 10px;
    }

    .chatbot-input input {
        flex: 1;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 20px;
        outline: none;
        font-size: 0.95rem;
        transition: border 0.3s;
    }

    .chatbot-input input:focus {
        border-color: #2e7d32;
    }

    .chatbot-input button {
        background: #2e7d32;
        color: white;
        border: none;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        margin-left: 10px;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .chatbot-input button:hover {
        background: #1b5e20;
        transform: scale(1.05);
    }
</style>

<!-- WIDGET CHATBOT -->
<div class="chatbot-widget">
    <div class="chatbot-window" id="chat-window">
        <div class="chatbot-header">
            <span><i class="fa-solid fa-robot"></i> EcoBot AI</span>
            <span class="close-bot" onclick="toggleChat(false)">&times;</span>
        </div>
        <div class="chatbot-messages" id="chat-body">
            <div class="chat-bubble chat-bot">¡Hola! Soy EcoBot, asistente virtual con IA. ¿Qué proyecto de
                bioconstrucción tienes en mente?
            </div>
        </div>
        <div class="chatbot-input">
            <input type="text" id="chat-input-text" placeholder="Escribe tu consulta..."
                onkeypress="if(event.key === 'Enter') sendChatMessage()">
            <button onclick="sendChatMessage()"><i class="fa-solid fa-paper-plane"></i></button>
        </div>
    </div>

    <div class="chatbot-toggle" onclick="toggleChat()">
        <i class="fa-solid fa-robot"></i>
    </div>
</div>

</body>

</html>