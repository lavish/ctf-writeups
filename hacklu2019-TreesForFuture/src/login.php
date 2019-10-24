<?php
/* TODO: Randomize variable names where possible */
/*login.php*/
require_once "../config.php";
require_once "utils.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
        header("Location: /internal/admin", true, 302);
        die();
    } else {
        // We had some issues with double encoded values. This fixed it.
        $parmas = parse_str(urldecode(file_get_contents("php://input")));
        $pdo = new_database_connection();
        if (isset($params["username"]) && is_string($params["username"])) {
            if (isset($params["password"]) && is_string($params["password"])) {
                $params["password"] = hash("sha512", $params["password"]);
                $stmt = $pdo->prepare("select * from members where username=:username and password='" . $params["password"] . "'");
                $stmt->bindParam(":username", $params["username"], PDO::PARAM_STR);
                $stmt->execute();
                if ($stmt->rowCount() === 1) {
                    // Successfully logged in. Populate Session.
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $_SESSION["username"] = $result["username"];
                    $_SESSION["id"] = $result["id"];
                    $_SESSION["logged_in"] = true;
                    $_SESSION["is_admin"] = $result["admin"] === "1" ? true : false;
                    header("Location: /internal/admin", true, 302);
                    die();
                } else {
                    $error = "Username/Password invalid.";
                }
            } else {
                $error = "Username/Password invalid.";
            }
        } else {
            $error = "Username/Password invalid.";
        }
        header("Location: /internal/login?error=" . urlencode($error));
        die();
    }
} else if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
        echo '<p>Already logged in.</p>';
        die();
    } else {
        if (isset($_GET["error"]) && is_string($_GET["error"])) {
            echo '<div class="error"><p>' . htmlentities($_GET["error"]) . '</p></div>';
        }
        echo '<div id="Login" class="">';
        echo '<br>';
        echo '<p>';
        echo '<h1><b> Admin Login</b></br> </h1>';
        echo '</p>';
        echo '<img src="/internal/img/logo_white.png" alt="Avatar" width="150" height="150">';
        echo '<form action="/internal' . $_SERVER["SCRIPT_NAME"] . '" method="POST">';
        echo '<label for="params[username]"><b>Username</b></label>';
        echo '<br>';
        echo '<input type="text" placeholder="Username" name="params[username]" required>';
        echo '<br>';
        echo '<label for="params[password]"><b>Password</b></label>';
        echo '<br>';
        echo '<input type="password" placeholder="Password" name="params[password]" required>';
        echo '<br>';
        echo '<button type="submit" class="button"><b>Login</b></button>';
        echo '<br>';
        echo '</form>';
        echo '</div>';
        die();
    }
}