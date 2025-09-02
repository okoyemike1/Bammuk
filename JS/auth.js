// auth.js
$(document).ready(function () {
    $("#registerForm").submit(function (e) {
        e.preventDefault();

        // Create FormData object
        let formData = new FormData(this);

        $.ajax({
            url: "http://localhost/bammuk/backend/index.php/auth/register", // adjust route if needed
            method: "POST",
            data: formData,
            contentType: false,  // important
            processData: false,  // important
            success: function (response) {
                console.log("Server response:", response);
                alert("Registration successful!");
            },
            error: function (xhr, status, error) {
                console.error("Error:", error);
                alert("Registration failed!");
            }
        });
    });
});


// auth.js (login snippet)
$(document).ready(function () {
    $("#loginForm").submit(function (e) {
        e.preventDefault();

        // Create FormData object
        let formData = new FormData(this);

        $.ajax({
            url: "http://localhost/bammuk/backend/index.php/auth/login", // login endpoint
            method: "POST",
            data: formData,
            contentType: false,  // important
            processData: false,  // important
            success: function (response) {
                console.log("Server response:", response);
                alert("Login successful!");
            },
            error: function (xhr, status, error) {
                console.error("Error:", error);
                alert("Login failed!");
            }
        });
    });
});
