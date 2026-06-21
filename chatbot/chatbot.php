<?php
$chatbotBaseUrl = $baseUrl ?? '/booknest/';
?>

<style>
.ai-chatbot-widget {
    position: fixed;
    right: 24px;
    bottom: 24px;
    z-index: 9999;
    font-family: Arial, sans-serif;
}

.ai-chatbot-toggle {
    width: 62px;
    height: 62px;
    border-radius: 50%;
    border: none;
    background: #7a3e1d;
    color: white;
    font-size: 1.6rem;
    cursor: pointer;
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.18);
}

.ai-chatbot-box {
    width: 350px;
    height: 470px;
    background: #fffdf8;
    border: 1px solid #e7d8c8;
    border-radius: 22px;
    display: none;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 18px 40px rgba(0, 0, 0, 0.22);
}

.ai-chatbot-header {
    background: #7a3e1d;
    color: white;
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.ai-chatbot-header strong {
    font-size: 1rem;
}

.ai-chatbot-header p {
    margin: 0.25rem 0 0;
    font-size: 0.85rem;
    opacity: 0.9;
}

.ai-chatbot-header button {
    background: transparent;
    border: none;
    color: white;
    font-size: 1.7rem;
    line-height: 1;
    cursor: pointer;
}

.ai-chatbot-messages {
    flex: 1;
    padding: 1rem;
    overflow-y: auto;
    background: #fffaf3;
}

.ai-bot-message,
.ai-user-message {
    max-width: 85%;
    padding: 0.75rem 0.9rem;
    margin-bottom: 0.75rem;
    border-radius: 16px;
    font-size: 0.9rem;
    line-height: 1.4;
}

.ai-bot-message {
    background: #f0e4d7;
    color: #332116;
    border-bottom-left-radius: 4px;
}

.ai-user-message {
    background: #7a3e1d;
    color: white;
    margin-left: auto;
    border-bottom-right-radius: 4px;
}

.ai-chatbot-form {
    display: flex;
    gap: 0.5rem;
    padding: 0.8rem;
    border-top: 1px solid #e7d8c8;
    background: white;
}

.ai-chatbot-form input {
    flex: 1;
    border: 1px solid #d8c4b0;
    border-radius: 999px;
    padding: 0.7rem 0.9rem;
    outline: none;
    font-size: 0.9rem;
}

.ai-chatbot-form button {
    border: none;
    border-radius: 999px;
    padding: 0.7rem 1rem;
    background: #7a3e1d;
    color: white;
    font-weight: 700;
    cursor: pointer;
}

@media (max-width: 480px) {
    .ai-chatbot-box {
        width: calc(100vw - 32px);
        height: 430px;
    }

    .ai-chatbot-widget {
        right: 16px;
        bottom: 16px;
    }
}
</style>

<div class="ai-chatbot-widget">
    <button class="ai-chatbot-toggle" id="aiChatbotToggle">
        💬
    </button>

    <div class="ai-chatbot-box" id="aiChatbotBox">
        <div class="ai-chatbot-header">
            <div>
                <strong>BookNest AI Assistant</strong>
                <p>Ask about books, cart, checkout, payment, or orders.</p>
            </div>

            <button type="button" id="aiChatbotClose">
                ×
            </button>
        </div>

        <div class="ai-chatbot-messages" id="aiChatbotMessages">
            <div class="ai-bot-message">
                Hi! I am BookNest AI Assistant. How can I help you today?
            </div>
        </div>

        <form class="ai-chatbot-form" id="aiChatbotForm">
            <input 
                type="text" 
                id="aiChatbotInput" 
                placeholder="Type your question..." 
                autocomplete="off"
                required
            >

            <button type="submit">
                Send
            </button>
        </form>
    </div>
</div>

<script>
(function () {
    const chatbotToggle = document.getElementById("aiChatbotToggle");
    const chatbotBox = document.getElementById("aiChatbotBox");
    const chatbotClose = document.getElementById("aiChatbotClose");
    const chatbotForm = document.getElementById("aiChatbotForm");
    const chatbotInput = document.getElementById("aiChatbotInput");
    const chatbotMessages = document.getElementById("aiChatbotMessages");

    if (!chatbotToggle || !chatbotBox || !chatbotClose || !chatbotForm || !chatbotInput || !chatbotMessages) {
        return;
    }

    chatbotToggle.addEventListener("click", function () {
        chatbotBox.style.display = "flex";
        chatbotToggle.style.display = "none";
    });

    chatbotClose.addEventListener("click", function () {
        chatbotBox.style.display = "none";
        chatbotToggle.style.display = "block";
    });

    chatbotForm.addEventListener("submit", function (e) {
        e.preventDefault();

        const userMessage = chatbotInput.value.trim();

        if (userMessage === "") {
            return;
        }

        addMessage(userMessage, "ai-user-message");
        chatbotInput.value = "";

        fetch("<?php echo $chatbotBaseUrl; ?>chatbot/chatbot-api.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "message=" + encodeURIComponent(userMessage)
        })
        .then(response => response.json())
        .then(data => {
            addMessage(data.reply, "ai-bot-message");
        })
        .catch(() => {
            addMessage("Sorry, I cannot respond right now. Please try again later.", "ai-bot-message");
        });
    });

    function addMessage(message, className) {
        const messageDiv = document.createElement("div");
        messageDiv.className = className;
        messageDiv.textContent = message;

        chatbotMessages.appendChild(messageDiv);
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }
})();
</script>