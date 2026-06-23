<?php
include '../app.php';
requireCustomer();

$user_id = (int)$_SESSION['user_id'];

date_default_timezone_set('Asia/Kuala_Lumpur');

function formatChatTime($datetime) {
    if (empty($datetime)) {
        return '';
    }

    return $date->format("d M Y, h:i A");
}

$stmt = $conn->prepare("
    SELECT 
        customer_messages.message_id,
        customer_messages.sender_role,
        customer_messages.message,
        customer_messages.created_at,
        users.name
    FROM customer_messages
    LEFT JOIN users ON customer_messages.user_id = users.user_id
    WHERE customer_messages.user_id = ?
    ORDER BY customer_messages.created_at ASC, customer_messages.message_id ASC
");

if (!$stmt) {
    die("Database prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$messages = $stmt->get_result();

$messagesList = [];

while ($row = $messages->fetch_assoc()) {
    $messagesList[] = $row;
}

$success = isset($_GET['sent']);
$error = isset($_GET['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Feedback | BookNest</title>
    <link rel="stylesheet" href="../css/style.css?v=160">

    <style>
        .chat-page {
            max-width: 900px;
            margin: 0 auto;
        }

        .chat-card {
            background: #fffdf8;
            border: 1px solid #e7d8c8;
            border-radius: 24px;
            overflow: hidden;
        }

        .chat-header {
            background: #fff8ef;
            border-bottom: 1px solid #e7d8c8;
            padding: 1.2rem 1.5rem;
        }

        .chat-header h2 {
            margin: 0;
            color: #5b321c;
        }

        .chat-header p {
            margin: 0.4rem 0 0;
            color: #6d5f55;
        }

        .chat-messages {
            padding: 1.5rem;
            min-height: 360px;
            max-height: 520px;
            overflow-y: auto;
            background: #fffaf3;
        }

        .chat-bubble {
            max-width: 75%;
            padding: 0.9rem 1rem;
            border-radius: 18px;
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .chat-bubble.customer {
            margin-left: auto;
            background: #7a3e1d;
            color: white;
            border-bottom-right-radius: 4px;
        }

        .chat-bubble.admin {
            margin-right: auto;
            background: #f0e4d7;
            color: #332116;
            border-bottom-left-radius: 4px;
        }

        .chat-meta {
            display: block;
            font-size: 0.78rem;
            opacity: 0.8;
            margin-top: 0.45rem;
        }

        .chat-form {
            padding: 1.2rem 1.5rem;
            border-top: 1px solid #e7d8c8;
            background: white;
        }

        .chat-form textarea {
            width: 100%;
            min-height: 110px;
            resize: vertical;
        }

        .chat-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 1rem;
        }

        .empty-chat {
            text-align: center;
            color: #6d5f55;
            padding: 4rem 1rem;
        }

        @media (max-width: 700px) {
            .chat-bubble {
                max-width: 90%;
            }
        }
    </style>
</head>

<body>

<div class="topbar">
    <div class="container">
        <span>Mini Online Bookstore</span>
        <span>Customer feedback and support chat</span>
    </div>
</div>

<?php include __DIR__ . '/../includes/navigation.php'; ?>

<section class="page-title">
    <div class="container">
        <p class="eyebrow">Customer Support</p>
        <h1>Feedback Chat</h1>
        <p>Send questions or feedback to the admin and view replies here.</p>
    </div>
</section>

<main class="section">
    <div class="container chat-page">

        <?php if ($success): ?>
            <div class="notice" style="margin-bottom:1rem;">
                Your message has been sent successfully.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="notice" style="margin-bottom:1rem;">
                Message cannot be empty. Please type your feedback.
            </div>
        <?php endif; ?>

        <div class="chat-card">

            <div class="chat-header">
                <h2>Chat with Admin</h2>
                <p>You can ask about orders, payment, delivery, refund, or book availability.</p>
            </div>

            <div class="chat-messages" id="chatMessages">
                <?php if (empty($messagesList)): ?>

                    <div class="empty-chat">
                        No messages yet. Start by sending your feedback or question below.
                    </div>

                <?php else: ?>

                    <?php foreach ($messagesList as $msg): ?>
                        <?php
                            $role = $msg['sender_role'];
                            $bubbleClass = $role === 'admin' ? 'admin' : 'customer';
                            $senderLabel = $role === 'admin' ? 'Admin' : 'You';
                        ?>

                        <div class="chat-bubble <?php echo $bubbleClass; ?>">
                            <?php echo nl2br(htmlspecialchars($msg['message'])); ?>

                            <span class="chat-meta">
                                <?php echo htmlspecialchars($senderLabel); ?> ·
                                <?php echo formatChatTime($msg['created_at']); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>

                <?php endif; ?>
            </div>

            <form class="chat-form" method="POST" action="send-message.php">
                <div class="field">
                    <label>Your Message</label>
                    <textarea 
                        name="message" 
                        placeholder="Type your question or feedback here..."
                        required
                    ></textarea>
                </div>

                <div class="chat-actions">
                    <button class="btn" type="submit">
                        Send Message
                    </button>
                </div>
            </form>

        </div>

    </div>
</main>

<footer class="footer">
    <div class="container footer-grid">
        <div>
            <h3>BookNest</h3>
            <p>Mini Online Bookstore e-commerce system.</p>
        </div>

        <div>
            <h4>Customer</h4>
            <a href="../books/books.php">Browse Books</a>
            <a href="../orders/cart.php">Shopping Cart</a>
            <a href="../orders/order-history.php">Order History</a>
            <a href="customer-chat.php">Feedback Chat</a>
        </div>

        <div>
            <h4>Support</h4>
            <a href="../orders/order-history.php">Track Orders</a>
            <a href="../books/books.php">Book Availability</a>
        </div>
    </div>
</footer>

<script>
const chatMessages = document.getElementById("chatMessages");
if (chatMessages) {
    chatMessages.scrollTop = chatMessages.scrollHeight;
}
</script>

</body>
</html>