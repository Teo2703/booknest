document.addEventListener("DOMContentLoaded", function () {
    setupRegisterValidation();
    setupLoginValidation();
    setupCheckoutValidation();
    setupBookFormValidation();
});

function showError(input, message) {
    clearError(input);

    const error = document.createElement("small");
    error.className = "js-error";
    error.textContent = message;

    input.classList.add("input-error");
    input.parentElement.appendChild(error);
}

function clearError(input) {
    input.classList.remove("input-error");

    const existingError = input.parentElement.querySelector(".js-error");
    if (existingError) {
        existingError.remove();
    }
}

function isEmailValid(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function isContactValid(contact) {
    return /^[0-9]{10,12}$/.test(contact);
}

/* Register validation */
function setupRegisterValidation() {
    const form = document.querySelector("#registerForm");
    if (!form) return;

    const name = form.querySelector("[name='name']");
    const email = form.querySelector("[name='email']");
    const contact = form.querySelector("[name='contact']");
    const password = form.querySelector("[name='password']");
    const confirmPassword = form.querySelector("[name='confirm_password']");

    function validateName() {
        if (name.value.trim() === "") {
            showError(name, "Full name is required.");
            return false;
        }
        clearError(name);
        return true;
    }

    function validateEmail() {
        if (email.value.trim() === "") {
            showError(email, "Email is required.");
            return false;
        }

        if (!isEmailValid(email.value.trim())) {
            showError(email, "Please enter a valid email address.");
            return false;
        }

        clearError(email);
        return true;
    }

    function validateContact() {
        if (contact.value.trim() === "") {
            showError(contact, "Contact number is required.");
            return false;
        }

        if (!isContactValid(contact.value.trim())) {
            showError(contact, "Contact number must be 10 to 12 digits.");
            return false;
        }

        clearError(contact);
        return true;
    }

    function validatePassword() {
        if (password.value === "") {
            showError(password, "Password is required.");
            return false;
        }

        if (password.value.length < 6) {
            showError(password, "Password must be at least 6 characters.");
            return false;
        }

        clearError(password);
        return true;
    }

    function validateConfirmPassword() {
        if (confirmPassword.value === "") {
            showError(confirmPassword, "Confirm password is required.");
            return false;
        }

        if (confirmPassword.value !== password.value) {
            showError(confirmPassword, "Password and confirm password do not match.");
            return false;
        }

        clearError(confirmPassword);
        return true;
    }

    name.addEventListener("input", validateName);
    email.addEventListener("input", validateEmail);
    contact.addEventListener("input", validateContact);
    password.addEventListener("input", validatePassword);
    confirmPassword.addEventListener("input", validateConfirmPassword);

    form.addEventListener("submit", function (event) {
        const valid =
            validateName() &&
            validateEmail() &&
            validateContact() &&
            validatePassword() &&
            validateConfirmPassword();

        if (!valid) {
            event.preventDefault();
        }
    });
}

/* Login validation */
function setupLoginValidation() {
    const form = document.querySelector("#loginForm");
    if (!form) return;

    const email = form.querySelector("[name='email']");
    const password = form.querySelector("[name='password']");
    const role = form.querySelector("[name='role']");

    function validateEmail() {
        if (email.value.trim() === "") {
            showError(email, "Email is required.");
            return false;
        }

        if (!isEmailValid(email.value.trim())) {
            showError(email, "Please enter a valid email address.");
            return false;
        }

        clearError(email);
        return true;
    }

    function validatePassword() {
        if (password.value === "") {
            showError(password, "Password is required.");
            return false;
        }

        clearError(password);
        return true;
    }

    function validateRole() {
        if (role.value !== "customer" && role.value !== "admin") {
            showError(role, "Please select a valid role.");
            return false;
        }

        clearError(role);
        return true;
    }

    email.addEventListener("input", validateEmail);
    password.addEventListener("input", validatePassword);
    role.addEventListener("change", validateRole);

    form.addEventListener("submit", function (event) {
        const valid =
            validateEmail() &&
            validatePassword() &&
            validateRole();

        if (!valid) {
            event.preventDefault();
        }
    });
}

/* Checkout validation */
function setupCheckoutValidation() {
    const form = document.querySelector("#checkoutForm");
    if (!form) return;

    const deliveryName = form.querySelector("[name='delivery_name']");
    const deliveryContact = form.querySelector("[name='delivery_contact']");
    const deliveryEmail = form.querySelector("[name='delivery_email']");
    const deliveryAddress = form.querySelector("[name='delivery_address']");
    const paymentMethod = form.querySelector("[name='payment_method']");

    function validateDeliveryName() {
        if (deliveryName.value.trim() === "") {
            showError(deliveryName, "Delivery name is required.");
            return false;
        }

        clearError(deliveryName);
        return true;
    }

    function validateDeliveryContact() {
        if (deliveryContact.value.trim() === "") {
            showError(deliveryContact, "Contact number is required.");
            return false;
        }

        if (!isContactValid(deliveryContact.value.trim())) {
            showError(deliveryContact, "Contact number must be 10 to 12 digits.");
            return false;
        }

        clearError(deliveryContact);
        return true;
    }

    function validateDeliveryEmail() {
        if (deliveryEmail.value.trim() === "") {
            showError(deliveryEmail, "Email is required.");
            return false;
        }

        if (!isEmailValid(deliveryEmail.value.trim())) {
            showError(deliveryEmail, "Please enter a valid email address.");
            return false;
        }

        clearError(deliveryEmail);
        return true;
    }

    function validateDeliveryAddress() {
        if (deliveryAddress.value.trim() === "") {
            showError(deliveryAddress, "Delivery address is required.");
            return false;
        }

        if (deliveryAddress.value.trim().length < 10) {
            showError(deliveryAddress, "Please enter a complete delivery address.");
            return false;
        }

        clearError(deliveryAddress);
        return true;
    }

    function validatePaymentMethod() {
        if (paymentMethod.value.trim() === "") {
            showError(paymentMethod, "Please select a payment method.");
            return false;
        }

        clearError(paymentMethod);
        return true;
    }

    deliveryName.addEventListener("input", validateDeliveryName);
    deliveryContact.addEventListener("input", validateDeliveryContact);
    deliveryEmail.addEventListener("input", validateDeliveryEmail);
    deliveryAddress.addEventListener("input", validateDeliveryAddress);
    paymentMethod.addEventListener("change", validatePaymentMethod);

    form.addEventListener("submit", function (event) {
        const valid =
            validateDeliveryName() &&
            validateDeliveryContact() &&
            validateDeliveryEmail() &&
            validateDeliveryAddress() &&
            validatePaymentMethod();

        if (!valid) {
            event.preventDefault();
        }
    });
}

/* Add/Edit book validation */
function setupBookFormValidation() {
    const form = document.querySelector("#bookForm");
    if (!form) return;

    const title = form.querySelector("[name='title']");
    const author = form.querySelector("[name='author']");
    const category = form.querySelector("[name='category']");
    const price = form.querySelector("[name='price']");
    const stock = form.querySelector("[name='stock']");

    function validateTitle() {
        if (title.value.trim() === "") {
            showError(title, "Book title is required.");
            return false;
        }

        clearError(title);
        return true;
    }

    function validateAuthor() {
        if (author.value.trim() === "") {
            showError(author, "Author is required.");
            return false;
        }

        clearError(author);
        return true;
    }

    function validateCategory() {
        if (category.value.trim() === "") {
            showError(category, "Category is required.");
            return false;
        }

        clearError(category);
        return true;
    }

    function validatePrice() {
        if (price.value === "") {
            showError(price, "Price is required.");
            return false;
        }

        if (Number(price.value) <= 0) {
            showError(price, "Price must be greater than 0.");
            return false;
        }

        clearError(price);
        return true;
    }

    function validateStock() {
        if (stock.value === "") {
            showError(stock, "Stock quantity is required.");
            return false;
        }

        if (Number(stock.value) < 0) {
            showError(stock, "Stock cannot be negative.");
            return false;
        }

        clearError(stock);
        return true;
    }

    title.addEventListener("input", validateTitle);
    author.addEventListener("input", validateAuthor);
    category.addEventListener("change", validateCategory);
    price.addEventListener("input", validatePrice);
    stock.addEventListener("input", validateStock);

    form.addEventListener("submit", function (event) {
        const valid =
            validateTitle() &&
            validateAuthor() &&
            validateCategory() &&
            validatePrice() &&
            validateStock();

        if (!valid) {
            event.preventDefault();
        }
    });
}