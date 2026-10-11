@auth
<div id="ai-assistant-widget" class="ai-widget-container">
    <!-- Floating Launcher Button -->
    <button id="ai-assistant-toggle" class="ai-launcher-btn" aria-label="Open AI HR Assistant" title="AI HR Assistant">
        <span class="ai-launcher-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2 2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/>
                <rect x="4" y="8" width="16" height="12" rx="4"/>
                <path d="M9 13v2"/>
                <path d="M15 13v2"/>
                <path d="M2 14h2"/>
                <path d="M20 14h2"/>
            </svg>
        </span>
        <span class="ai-launcher-pulse"></span>
        <span class="ai-launcher-label">HR AI</span>
    </button>

    <!-- Chat Dialog Panel -->
    <div id="ai-assistant-modal" class="ai-chat-window" style="display: none;">
        <!-- Header -->
        <div class="ai-chat-header">
            <div class="ai-header-info">
                <div class="ai-avatar">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="4" y="8" width="16" height="12" rx="4"/>
                        <path d="M9 13v2"/>
                        <path d="M15 13v2"/>
                        <path d="M12 2v6"/>
                    </svg>
                    <span class="ai-status-dot"></span>
                </div>
                <div>
                    <div class="ai-header-title">HR Assistant</div>
                    <div class="ai-header-status">Live AI Assistant</div>
                </div>
            </div>
            <div class="ai-header-actions">
                <button id="ai-chat-clear" class="ai-icon-btn" title="Clear Chat" aria-label="Clear chat">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 6h18"/>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    </svg>
                </button>
                <button id="ai-chat-close" class="ai-icon-btn" title="Close" aria-label="Close assistant">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Privacy Shield Indicator -->
        <div class="ai-privacy-pill">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <span>Scoped to your profile &bull; Protected &amp; Private</span>
        </div>

        <!-- Messages Area -->
        <div id="ai-chat-messages" class="ai-messages-container">
            <div class="ai-msg ai-msg-bot">
                <div class="ai-bubble">
                    👋 Hello <strong>{{ Auth::user()->name }}</strong>! I am your AI HR Assistant.
                    <br><br>
                    You can ask me about your <strong>leave balance</strong>, <strong>payslip</strong>, <strong>attendance record</strong>, or <strong>upcoming holidays</strong>!
                </div>
            </div>

            <!-- Quick Suggestion Chips -->
            <div id="ai-quick-chips" class="ai-chips-wrapper">
                <button class="ai-chip" data-prompt="What is my leave balance?">🌴 Leave Balance</button>
                <button class="ai-chip" data-prompt="Show my latest payslip breakdown">💰 Payslip Breakdown</button>
                <button class="ai-chip" data-prompt="What is my attendance summary this month?">⏱️ Attendance</button>
                <button class="ai-chip" data-prompt="Check my Provident Fund status">🏛️ PF Status</button>
                <button class="ai-chip" data-prompt="What are the upcoming company holidays?">🎉 Holidays</button>
            </div>
        </div>

        <!-- Typing Indicator -->
        <div id="ai-typing-indicator" class="ai-typing-wrapper" style="display: none;">
            <div class="ai-typing-dot"></div>
            <div class="ai-typing-dot"></div>
            <div class="ai-typing-dot"></div>
            <span class="ai-typing-text">Analyzing your HR records...</span>
        </div>

        <!-- Footer / Input Form -->
        <form id="ai-chat-form" class="ai-input-form" onsubmit="return false;">
            <input
                type="text"
                id="ai-user-input"
                class="ai-chat-input"
                placeholder="Ask about leaves, salary, attendance..."
                maxlength="1000"
                autocomplete="off"
            />
            <button type="submit" id="ai-send-btn" class="ai-send-btn" aria-label="Send message">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"/>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
            </button>
        </form>
    </div>
</div>

<style>
    /* Floating Launcher */
    .ai-widget-container {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 99999;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    .ai-launcher-btn {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        color: #ffffff;
        border: none;
        border-radius: 50px;
        padding: 12px 18px 12px 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        box-shadow: 0 8px 24px rgba(79, 70, 229, 0.4);
        position: relative;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .ai-launcher-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(79, 70, 229, 0.55);
    }

    .ai-launcher-label {
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    .ai-launcher-pulse {
        position: absolute;
        top: 2px;
        right: 2px;
        width: 10px;
        height: 10px;
        background: #10b981;
        border: 2px solid #ffffff;
        border-radius: 50%;
        animation: ai-pulse 1.8s infinite;
    }

    @keyframes ai-pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.3); }
    }

    /* Chat Window */
    .ai-chat-window {
        position: absolute;
        bottom: 65px;
        right: 0;
        width: 380px;
        max-width: calc(100vw - 32px);
        height: 540px;
        max-height: calc(100vh - 100px);
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.22), 0 0 0 1px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        animation: ai-slide-up 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes ai-slide-up {
        from { opacity: 0; transform: translateY(16px) scale(0.96); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* Header */
    .ai-chat-header {
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
        color: #ffffff;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .ai-header-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .ai-avatar {
        width: 36px;
        height: 36px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .ai-status-dot {
        position: absolute;
        bottom: -1px;
        right: -1px;
        width: 9px;
        height: 9px;
        background: #10b981;
        border: 2px solid #1e1b4b;
        border-radius: 50%;
    }

    .ai-header-title {
        font-size: 15px;
        font-weight: 700;
        line-height: 1.2;
    }

    .ai-header-status {
        font-size: 11px;
        color: #a5b4fc;
    }

    .ai-header-actions {
        display: flex;
        gap: 6px;
    }

    .ai-icon-btn {
        background: rgba(255, 255, 255, 0.1);
        border: none;
        color: #ffffff;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.15s ease;
    }

    .ai-icon-btn:hover {
        background: rgba(255, 255, 255, 0.22);
    }

    /* Privacy Pill */
    .ai-privacy-pill {
        background: #f1f5f9;
        color: #475569;
        font-size: 11px;
        padding: 6px 12px;
        display: flex;
        align-items: center;
        gap: 6px;
        border-bottom: 1px solid #e2e8f0;
    }

    /* Messages Container */
    .ai-messages-container {
        flex: 1;
        overflow-y: auto;
        padding: 14px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        background: #f8fafc;
    }

    .ai-msg {
        display: flex;
        flex-direction: column;
        max-width: 86%;
        animation: ai-msg-fade 0.2s ease;
    }

    @keyframes ai-msg-fade {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .ai-msg-bot {
        align-self: flex-start;
    }

    .ai-msg-user {
        align-self: flex-end;
    }

    .ai-bubble {
        padding: 10px 14px;
        border-radius: 12px;
        font-size: 13px;
        line-height: 1.5;
        word-break: break-word;
    }

    .ai-msg-bot .ai-bubble {
        background: #ffffff;
        color: #1e293b;
        border: 1px solid #e2e8f0;
        border-top-left-radius: 2px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }

    .ai-msg-user .ai-bubble {
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        color: #ffffff;
        border-top-right-radius: 2px;
        box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25);
    }

    /* Suggestion Chips */
    .ai-chips-wrapper {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 4px;
    }

    .ai-chip {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: #334155;
        padding: 6px 10px;
        border-radius: 16px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .ai-chip:hover {
        background: #eef2ff;
        border-color: #6366f1;
        color: #4338ca;
        transform: translateY(-1px);
    }

    /* Typing Indicator */
    .ai-typing-wrapper {
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 8px 14px;
        background: #ffffff;
        border-top: 1px solid #f1f5f9;
    }

    .ai-typing-dot {
        width: 6px;
        height: 6px;
        background: #6366f1;
        border-radius: 50%;
        animation: ai-bounce 1.2s infinite ease-in-out;
    }

    .ai-typing-dot:nth-child(2) { animation-delay: 0.2s; }
    .ai-typing-dot:nth-child(3) { animation-delay: 0.4s; }

    @keyframes ai-bounce {
        0%, 80%, 100% { transform: translateY(0); opacity: 0.4; }
        40% { transform: translateY(-4px); opacity: 1; }
    }

    .ai-typing-text {
        font-size: 11px;
        color: #64748b;
        margin-left: 6px;
    }

    /* Footer / Input */
    .ai-input-form {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        background: #ffffff;
        border-top: 1px solid #e2e8f0;
    }

    .ai-chat-input {
        flex: 1;
        border: 1px solid #cbd5e1;
        border-radius: 20px;
        padding: 10px 14px;
        font-size: 13px;
        outline: none;
        transition: border 0.15s ease;
    }

    .ai-chat-input:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.15);
    }

    .ai-send-btn {
        background: #4f46e5;
        border: none;
        color: #ffffff;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.15s ease, transform 0.15s ease;
        flex-shrink: 0;
    }

    .ai-send-btn:hover {
        background: #4338ca;
        transform: scale(1.05);
    }

    .ai-send-btn:disabled {
        background: #cbd5e1;
        cursor: not-allowed;
        transform: none;
    }

    /* Responsive */
    @media (max-width: 480px) {
        .ai-chat-window {
            width: calc(100vw - 32px);
            right: 0;
            bottom: 70px;
            height: 480px;
        }
        .ai-launcher-label {
            display: none;
        }
        .ai-launcher-btn {
            padding: 12px;
            border-radius: 50%;
        }
    }
</style>

<script>
    (function() {
        const toggleBtn = document.getElementById('ai-assistant-toggle');
        const modal = document.getElementById('ai-assistant-modal');
        const closeBtn = document.getElementById('ai-chat-close');
        const clearBtn = document.getElementById('ai-chat-clear');
        const chatForm = document.getElementById('ai-chat-form');
        const chatInput = document.getElementById('ai-user-input');
        const sendBtn = document.getElementById('ai-send-btn');
        const messagesBox = document.getElementById('ai-chat-messages');
        const typingIndicator = document.getElementById('ai-typing-indicator');

        if (!toggleBtn || !modal) return;

        // Toggle chat modal
        toggleBtn.addEventListener('click', function() {
            const isHidden = modal.style.display === 'none';
            modal.style.display = isHidden ? 'flex' : 'none';
            if (isHidden) {
                setTimeout(() => chatInput && chatInput.focus(), 100);
            }
        });

        // Close button
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        }

        // Clear chat
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                messagesBox.innerHTML = `
                    <div class="ai-msg ai-msg-bot">
                        <div class="ai-bubble">
                            Chat cleared. How else may I assist you today?
                        </div>
                    </div>
                    <div id="ai-quick-chips" class="ai-chips-wrapper">
                        <button class="ai-chip" data-prompt="What is my leave balance?">🌴 Leave Balance</button>
                        <button class="ai-chip" data-prompt="Show my latest payslip breakdown">💰 Payslip Breakdown</button>
                        <button class="ai-chip" data-prompt="What is my attendance summary this month?">⏱️ Attendance</button>
                        <button class="ai-chip" data-prompt="Check my Provident Fund status">🏛️ PF Status</button>
                        <button class="ai-chip" data-prompt="What are the upcoming company holidays?">🎉 Holidays</button>
                    </div>
                `;
                bindChipEvents();
            });
        }

        // Quick chips delegation
        function bindChipEvents() {
            document.querySelectorAll('.ai-chip').forEach(chip => {
                chip.onclick = function() {
                    const prompt = this.getAttribute('data-prompt');
                    if (prompt) {
                        chatInput.value = prompt;
                        submitMessage();
                    }
                };
            });
        }
        bindChipEvents();

        // Send message form submit
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitMessage();
        });

        async function submitMessage() {
            const message = chatInput.value.trim();
            if (!message) return;

            // Append User Message Bubble
            appendMessage(escapeHtml(message), 'user');
            chatInput.value = '';
            setLoading(true);

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const response = await fetch("{{ route('ai-assistant.chat') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message: message })
                });

                const data = await response.json();
                if (data.success && data.reply) {
                    appendMessage(formatMarkdown(data.reply), 'bot');
                } else {
                    appendMessage("⚠️ " + (data.message || "Unable to get response. Please try again."), 'bot');
                }
            } catch (err) {
                appendMessage("⚠️ Network error while connecting to HR Assistant.", 'bot');
            } finally {
                setLoading(false);
            }
        }

        function appendMessage(formattedHtml, sender) {
            const msgEl = document.createElement('div');
            msgEl.className = 'ai-msg ai-msg-' + sender;
            msgEl.innerHTML = `<div class="ai-bubble">${formattedHtml}</div>`;
            messagesBox.appendChild(msgEl);
            messagesBox.scrollTop = messagesBox.scrollHeight;
        }

        function setLoading(isLoading) {
            typingIndicator.style.display = isLoading ? 'flex' : 'none';
            sendBtn.disabled = isLoading;
            chatInput.disabled = isLoading;
            if (!isLoading) {
                chatInput.focus();
            }
            messagesBox.scrollTop = messagesBox.scrollHeight;
        }

        function escapeHtml(str) {
            return str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatMarkdown(text) {
            if (!text) return '';
            let html = escapeHtml(text);

            // Bold **text**
            html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

            // Italic *text*
            html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');

            // Inline code `code`
            html = html.replace(/`(.*?)`/g, '<code style="background:#e2e8f0;padding:2px 4px;border-radius:4px;font-size:11px;">$1</code>');

            // Bullets • or -
            html = html.replace(/^[•\-\*]\s+(.*)$/gm, '<div style="display:flex;gap:6px;margin:2px 0;"><span>&bull;</span><span>$1</span></div>');

            // Newlines
            html = html.replace(/\n\n/g, '<br><br>').replace(/\n/g, '<br>');

            return html;
        }
    })();
</script>
@endauth
