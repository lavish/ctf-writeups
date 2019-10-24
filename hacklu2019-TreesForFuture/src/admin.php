<?php
require_once "../config.php";
require_once "utils.php";


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
        header("Location: /internal/login", true, 302);
        die();
    } else {
        /* disabled for security reasons */
        die("Disabled for security reasons");
        $parmas = parse_str(urldecode(file_get_contents("php://input")));
        $pdo = new_database_connection();
        $stmt = $pdo->prepare("select * from members where username like '%" . $params["username"] . "%'");
        $stmt->execute();
        if ($stmt->rowCount() >= 1) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($result as $row) {
                echo "<tr>";
                echo "<td>{$row['id']}</td>";
                echo "<td>{$row['username']}</td>";
                echo "<td>{$row['password']}</td>";
                echo "<td>{$row['admin']}</td>";
                echo "</tr>";
            }
        }
    }
} else if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
        echo '<p>Not logged in.</p>';
        die();
    } else {
        if ($_SESSION["is_admin"] === true) {
            // Joe: Implemented additional check. Bob told me that his friend recently bypassed this and that I should implement additional checks.
            $pdo = new_database_connection();
            $stmt = $pdo->prepare("SELECT admin FROM members WHERE id=" . $_SESSION["id"]);
            $stmt->execute();
            if ($stmt->rowCount() === 1) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($result["admin"] === "1") {
                    echo '<div id="Login" class="">';
                    echo '<br>';
                    echo '<p>';
                    echo '<h1><b> Member Search</b></br> </h1>';
                    echo '</p>';
                    echo '<form action="/internal' . $_SERVER["SCRIPT_NAME"] . '" method="POST">';
                    echo '<label for="params[username]"><b>Username</b></label>';
                    echo '<br>';
                    echo '<button type="submit" class="button"><b>Search</b></button>';
                    echo '<br>';
                    echo '</form>';
                    echo '</div>';
                }
            } else {
                die("You ain't admin. You must be a bad hacker.");
            }
        } else {
            echo '<div class="custom_tooltip">';
            echo "You're not admin.";
            echo '<span class="custom_tooltiptext">';
            echo getClientIP() . " isn't admins IP.";
            echo '</span>';
            echo '</div>';
            die();
        }
    }
}