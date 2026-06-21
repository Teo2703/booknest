<?php
include '../app.php';

header('Content-Type: application/json');

$userMessage = strtolower(trim($_POST['message'] ?? ''));

function hasKeyword($text, $keywords) {
    foreach ($keywords as $keyword) {
        if (strpos($text, $keyword) !== false) {
            return true;
        }
    }
    return false;
}

if ($userMessage === '') {
    echo json_encode([
        'reply' => 'Please type a question.'
    ]);
    exit();
}

$reply = "Sorry, I am not sure about that. You can ask me about books, cart, checkout, payment, delivery, orders, refund, or feedback.";

if (hasKeyword($userMessage, ['book', 'books', 'search', 'category', 'author'])) {
    $reply = "You can browse books from the Books page. You can search by title, author, or category to find the book you want.";
}

if (hasKeyword($userMessage, ['cart', 'add to cart', 'remove', 'quantity'])) {
    $reply = "To manage your cart, go to the Cart page. You can update quantity, remove books, clear the cart, or proceed to checkout.";
}

if (hasKeyword($userMessage, ['checkout', 'place order', 'buy', 'purchase'])) {
    $reply = "To checkout, add books to your cart, click Proceed to Checkout, enter your delivery details, choose a payment method, then continue to place your order.";
}

if (hasKeyword($userMessage, ['payment', 'pay', 'online transfer', 'credit', 'debit', 'card', 'cash'])) {
    $reply = "BookNest supports Cash on Delivery, Online Transfer, and Credit/Debit Card simulation. Online Transfer requires a payment reference number, while card payment saves only the last 4 digits.";
}

if (hasKeyword($userMessage, ['receipt', 'invoice', 'print'])) {
    $reply = "After placing an order, the system generates a receipt. You can also open your receipt again from Order History by clicking View Receipt.";
}

if (hasKeyword($userMessage, ['delivery', 'shipping', 'fee'])) {
    $reply = "Delivery is free for orders above RM80. For orders below RM80, the delivery fee is RM5.";
}

if (hasKeyword($userMessage, ['order history', 'my order', 'order status', 'track', 'tracking'])) {
    $reply = "You can view your previous orders from the Order History page. You can also track order status and view the receipt there.";
}

if (hasKeyword($userMessage, ['refund', 'return', 'cancel'])) {
    $reply = "You can cancel an order if it is still Pending or Processing. Refund requests are available for completed orders.";
}

if (hasKeyword($userMessage, ['feedback', 'admin', 'message', 'support', 'chat'])) {
    $reply = "You can use the Feedback page to send questions or messages to the admin. The admin can reply through the Messages page.";
}

if (hasKeyword($userMessage, ['login', 'register', 'account', 'profile'])) {
    $reply = "You need to register and log in as a customer before adding books to cart, checking out, viewing order history, or sending feedback.";
}

if (hasKeyword($userMessage, ['recommend', 'suggest', 'suggestion'])) {
    $reply = "You may try Fiction for novels, Academic for study books, Self-Improvement for personal growth, Children books for young readers, or Comics for light reading.";
}

echo json_encode([
    'reply' => $reply
]);
?>