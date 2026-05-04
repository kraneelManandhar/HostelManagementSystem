(function () {
    // ── Config ────────────────────────────────────────────────────────────────
    const baseUrl = window.BASE_URL || '/HostelManagementSystem/';
    const API_URL = baseUrl + 'index.php?action=chatbot';

    // ── Build DOM ─────────────────────────────────────────────────────────────
    document.body.insertAdjacentHTML('beforeend', `
        <!-- Floating toggle button -->
        <button id="chat-toggle" title="Chat with us">💬</button>

        <!-- Chat window -->
        <div id="chat-window" class="hidden">
            <div id="chat-header">
                <span class="dot"></span> Hostel Assistant
            </div>
            <div id="chat-messages">
                <div class="chat-msg bot">👋 Hi! I'm your hostel assistant. Ask me anything about facilities, rooms, fees, or staff!</div>
            </div>
            <div id="chat-input-row">
                <input id="chat-input" type="text" placeholder="Type a message…" autocomplete="off" />
                <button id="chat-send">Send</button>
            </div>
        </div>
    `);

    // ── Elements ──────────────────────────────────────────────────────────────
    const toggle   = document.getElementById('chat-toggle');
    const window_  = document.getElementById('chat-window');
    const messages = document.getElementById('chat-messages');
    const input    = document.getElementById('chat-input');
    const sendBtn  = document.getElementById('chat-send');

    // ── Toggle open/close ─────────────────────────────────────────────────────
    toggle.addEventListener('click', () => {
        window_.classList.toggle('hidden');
        toggle.textContent = window_.classList.contains('hidden') ? '💬' : '✖';
        if (!window_.classList.contains('hidden')) input.focus();
    });

    // ── Send on Enter ─────────────────────────────────────────────────────────
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
    });
    sendBtn.addEventListener('click', sendMessage);

    // ── Core send function ────────────────────────────────────────────────────
    async function sendMessage() {
        const text = input.value.trim();
        if (!text) return;

        appendMessage(text, 'user');
        input.value = '';
        sendBtn.disabled = true;

        // Typing indicator
        const typingEl = appendMessage('Typing…', 'typing');

        try {
            const res  = await fetch(API_URL, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ message: text })
            });
            const data = await res.json();

            typingEl.remove();

            if (data.reply) {
                appendMessage(data.reply, 'bot');
            } else if (data.error) {
                appendMessage('⚠️ ' + data.error, 'bot');
            } else {
                appendMessage('⚠️ Unexpected response. Please try again.', 'bot');
            }
        } catch (err) {
            typingEl.remove();
            appendMessage('⚠️ Could not connect. Check your internet and try again.', 'bot');
        }

        sendBtn.disabled = false;
        input.focus();
    }

    // ── Helper: add a message bubble ─────────────────────────────────────────
    function appendMessage(text, type) {
        const el = document.createElement('div');
        el.className = 'chat-msg ' + type;
        el.textContent = text;
        messages.appendChild(el);
        messages.scrollTop = messages.scrollHeight;
        return el;
    }
})();
