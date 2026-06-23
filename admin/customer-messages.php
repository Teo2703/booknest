<?php
include '../app.php';
requireAdmin();

date_default_timezone_set('Asia/Kuala_Lumpur');

function formatChatTime($datetime) {
    if (empty($datetime)) {
        return '';
    }

    return date("d M Y, h:i A", strtotime($datetime));
}

$sql = "
    SELECT 
        users.user_id,
        users.name,
        users.email,
        MAX(customer_messages.created_at) AS latest_message_time,
        SUM(CASE WHEN customer_messages.sender_role = 'customer' AND customer_messages.is_read = 0 THEN 1 ELSE 0 END) AS unread_count
    FROM customer_messages
    INNER JOIN users ON customer_messages.user_id = users.user_id
    GROUP BY users.user_id, users.name, users.email
    ORDER BY latest_message_time DESC
";

$result = $conn->query($sql);

if (!$result) {
    die("Database query failed: " . $conn->error);
}

$selectedUserId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$selectedUser = null;
$messagesList = [];

if ($selectedUserId > 0) {
    $userStmt = $conn->prepare("
        SELECT user_id, name, email
        FROM users
        WHERE user_id = ?
        AND role = 'customer'
    ");

    if (!$userStmt) {
        die("Database prepare failed: " . $conn->error);
    }

    $userStmt->bind_param("i", $selectedUserId);
    $userStmt->execute();
    $selectedUser = $userStmt->get_result()->fetch_assoc();

    if ($selectedUser) {
        $msgStmt = $conn->prepare("
            SELECT 
                message_id,
                sender_role,
                message,
                created_at
            FROM customer_messages
            WHERE user_id = ?
            ORDER BY created_at ASC, message_id ASC
        ");

        if (!$msgStmt) {
            die("Database prepare failed: " . $conn->error);
        }

        $msgStmt->bind_param("i", $selectedUserId);
        $msgStmt->execute();
        $messages = $msgStmt->get_result();

        while ($msg = $messages->fetch_assoc()) {
            $messagesList[] = $msg;
        }

        $readStmt = $conn->prepare("
            UPDATE customer_messages
            SET is_read = 1
            WHERE user_id = ?
            AND sender_role = 'customer'
        ");

        if ($readStmt) {
            $readStmt->bind_param("i", $selectedUserId);
            $readStmt->execute();
        }
    }
}

$success = isset($_GET['replied']);
$error = isset($_GET['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Messages | BookNest</title>
    <link rel="stylesheet" href="../css/style.css?v=170">

    <style>
        .message-layout {
            display: grid;
            grid-template-columns: 300px minmax(0, 1fr);
            gap: 1.5rem;
            align-items: flex-start;
        }

        .customer-list,
        .admin-chat-card {
            background: #fffdf8;
            border: 1px solid #e7d8c8;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .customer-list h2,
        .admin-chat-header {
            margin: 0;
            padding: 1rem 1.2rem;
            background: #fff8ef;
            border-bottom: 1px solid #e7d8c8;
            color: #5b321c;
        }

        .customer-link {
            display: block;
            padding: 1rem 1.2rem;
            border-bottom: 1px solid #e7d8c8;
            text-decoration: none;
            color: #332116;
        }

        .customer-link:hover,
        .customer-link.active {
            background: #fff8ef;
        }

        .customer-link strong {
            display: block;
            margin-bottom: 0.25rem;
        }

        .badge {
            display: inline-block;
            background: #8b1e1e;
            color: white;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 999px;
            margin-top: 0.4rem;
        }

        .admin-chat-messages {
            padding: 1.5rem;
            min-height: 420px;
            max-height: 560px;
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
            margin-right: auto;
            background: #f0e4d7;
            color: #332116;
            border-bottom-left-radius: 4px;
        }

        .chat-bubble.admin {
            margin-left: auto;
            background: #7a3e1d;
            color: white;
            border-bottom-right-radius: 4px;
        }

        .chat-meta {
            display: block;
            font-size: 0.78rem;
            opacity: 0.8;
            margin-top: 0.45rem;
        }

        .reply-form {
            padding: 1.2rem;
            border-top: 1px solid #e7d8c8;
            background: white;
        }

        .reply-form textarea {
            width: 100%;
            min-height: 100px;
            resize: vertical;
        }

        .empty-panel {
            padding: 3rem 1rem;
            text-align: center;
            color: #6d5f55;
        }

        @media (max-width: 1000px) {
            .message-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<section class="page-title">
    <div class="container">
        <p class="eyebrow">Admin Support</p>
        <h1>Customer Messages</h1>
        <p>View customer feedback and reply to customer questions.</p>
    </div>
</section>

<main class="section">
    <div class="container">

        <div class="admin-layout">

            <?php include __DIR__ . '/../includes/admin-sidebar.php'; ?>

            <div>

                <?php if ($success): ?>
                    <div class="notice" style="margin-bottom:1rem;">
                        Reply sent successfully.
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="notice" style="margin-bottom:1rem;">
                        Reply cannot be empty.
                    </div>
                <?php endif; ?>

                <div class="message-layout">

                    <aside class="customer-list">
                        <h2>Customers</h2>

                        <?php if ($result->num_rows === 0): ?>
                            <div class="empty-panel">
                                No customer messages yet.
                            </div>
                        <?php else: ?>

                            <?php while ($customer = $result->fetch_assoc()): ?>
                                <?php
                                    $customerId = (int)$customer['user_id'];
                                    $activeClass = $customerId === $selectedUserId ? 'active' : '';
                                    $unread = (int)$customer['unread_count'];
                                ?>

                                <a 
                                    class="customer-link <?php echo $activeClass; ?>" 
                                    href="customer-messages.php?user_id=<?php echo $customerId; ?>"
                                >
                                    <strong><?php echo htmlspecialchars($customer['name']); ?></strong>

                                    <span class="small">
                                        <?php echo htmlspecialchars($customer['email']); ?>
                                    </span>
                                    <br>

                                    <span class="small">
                                        Latest: 
                                        <?php echo formatChatTime($customer['latest_message_time']); ?>
                                    </span>

                                    <?php if ($unread > 0): ?>
                                        <br>
                                        <span class="badge">
                                            <?php echo $unread; ?> new
                                        </span>
                                    <?php endif; ?>
                                </a>
                            <?php endwhile; ?>

                        <?php endif; ?>
                    </aside>

                    <section class="admin-chat-card">

                        <?php if (!$selectedUser): ?>

                            <div class="empty-panel">
                                Select a customer from the left to view messages.
                            </div>

                        <?php else: ?>

                            <div class="admin-chat-header">
                                <h2 style="margin:0;">
                                    Chat with <?php echo htmlspecialchars($selectedUser['name']); ?>
                                </h2>

                                <p style="margin:0.3rem 0 0;">
                                    <?php echo htmlspecialchars($selectedUser['email']); ?>
                                </p>
                            </div>

                            <div class="admin-chat-messages" id="adminChatMessages">
                                <?php foreach ($messagesList as $msg): ?>
                                    <?php
                                        $role = $msg['sender_role'];
                                        $bubbleClass = $role === 'admin' ? 'admin' : 'customer';
                                        $senderLabel = $role === 'admin' ? 'Admin' : 'Customer';
                                    ?>

                                    <div class="chat-bubble <?php echo $bubbleClass; ?>">
                                        <?php echo nl2br(htmlspecialchars($msg['message'])); ?>

                                        <span class="chat-meta">
                                            <?php echo htmlspecialchars($senderLabel); ?> ·
                                            <?php echo formatChatTime($msg['created_at']); ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <form class="reply-form" method="POST" action="reply-message.php">
                                <input 
                                    type="hidden" 
                                    name="user_id" 
                                    value="<?php echo (int)$selectedUser['user_id']; ?>"
                                >

                                <div class="field">
                                    <label>Admin Reply</label>
                                    <textarea 
                                        name="message" 
                                        placeholder="Type your reply here..."
                                        required
                                    ></textarea>
                                </div>

                                <button class="btn" type="submit">
                                    Send Reply
                                </button>
                            </form>

                        <?php endif; ?>

                    </section>

                </div>

            </div>

        </div>

    </div>
</main>

<footer class="footer">
    <div class="container footer-grid">
        <div>
            <h3>BookNest Admin</h3>
            <p>Manage customer feedback and support messages.</p>
        </div>

        <div>
            <h4>Admin</h4>
            <a href="admin-dashboard.php">Dashboard</a>
            <a href="manage-books.php">Manage Books</a>
            <a href="manage-orders.php">Manage Orders</a>
            <a href="manage-refunds.php">Manage Refunds</a>
            <a href="customer-messages.php">Customer Messages</a>
        </div>
    </div>
</footer>

<script>
const adminChatMessages = document.getElementById("adminChatMessages");

if (adminChatMessages) {
    adminChatMessages.scrollTop = adminChatMessages.scrollHeight;
}
</script>

</body>
</html>