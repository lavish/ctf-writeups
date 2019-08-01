<?php
require_once __DIR__ . "/vendor/autoload.php";

function auth($username, $password) {
    $collection = (new MongoDB\Client('mongodb://localhost:27017/'))->test->users;
    $raw_query = '{"username": "'.$username.'", "password": "'.$password.'"}';
    $document = $collection->findOne(json_decode($raw_query));
    if (isset($document) && isset($document->password)) {
        return true;
    }
    return false;
}

$user = false;
if (isset($_COOKIE['username']) && isset($_COOKIE['password'])) {
    $user = auth($_COOKIE['username'], $_COOKIE['password']);
}

if (isset($_POST['username']) && isset($_POST['password'])) {
    $user = auth($_POST['username'], $_POST['password']);
    if ($user) {
        setcookie('username', $_POST['username']);
        setcookie('password', $_POST['password']);
    }
}

?>

<?php if ($user == true): ?>

    Welcome!
    <div>
        Group most common news by
        <a href="?filter=$category">category</a> | 
        <a href="?filter=$public">publicity</a><br>
    </div>

    <?php
        $filter = $_GET['filter'];

        $collection = (new MongoDB\Client('mongodb://localhost:27017/'))->test->news;

        $pipeline = [
            ['$group' => ['_id' => '$category', 'count' => ['$sum' => 1]]],
            ['$sort' => ['count' => -1]],
            ['$limit' => 5],
        ];

        $filters = [
            ['$project' => ['category' => $filter]]
        ];

        print("<pre>".print_r($filter, true)."</pre>");

        $cursor = $collection->aggregate(array_merge($filters, $pipeline));

    ?>

    <?php if (isset($filter)): ?>

        <?php
            foreach ($cursor as $category) {
                    printf("%s has %d news<br>", $category['_id'], $category['count']);
            }
        ?>

    <?php endif; ?>

<?php else: ?>

    <?php if (isset($_POST['username']) && isset($_POST['password'])): ?>
        Invalid username or password
    <?php endif; ?>

    <form action='/' method="POST">
        <input type="text" name="username">
        <input type="password" name="password">
        <input type="submit">
    </form>

    <h2>News</h2>
    <?php
        $collection = (new MongoDB\Client('mongodb://localhost:27017/'))->test->news;
        $cursor = $collection->find(['public' => 1]);
        foreach ($cursor as $news) {
            printf("%s<br>", $news['title']);
        }
    ?>

<?php endif; ?>
